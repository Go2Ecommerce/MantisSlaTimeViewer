<?php

namespace SlaTimeViewer;

class SlaTimeViewerApi
{

    function get_statistics($regionFilter = null)
    {
        $t_bug_table = db_get_table('bug');
        $t_custom_field_string_table = db_get_table('custom_field_string');

        $close_date_field_id = custom_field_get_id_from_name('Data Zakończenia');

        $beginOfPreviousDay = strtotime("yesterday");

        // Formatowanie dat w stylu SQL: 'YYYY-MM-DD HH:MM:SS'
        $dateFromStr = date('Y-m-d 00:00:00', $beginOfPreviousDay);
        $dateToStr = date('Y-m-d 23:59:59', $beginOfPreviousDay);

        $queryClosed = "SELECT b.*
                        FROM {$t_bug_table} b
                        JOIN {$t_custom_field_string_table} cf ON cf.bug_id = b.id AND cf.field_id = " . db_param() . "
                        WHERE cf.value BETWEEN " . db_param() . " AND " . db_param() . "
                        AND b.status IN (80, 90)";

        // Parametry do zapytania
        $paramsClosed = array($close_date_field_id, $dateFromStr, $dateToStr);

        // Wykonanie zapytania
        $closedBugsPreviousDayQuery = db_query($queryClosed, $paramsClosed);

        //wyciaganie zlecen z dnia poprzedniego
        $endOfPreviousDay = strtotime("today") - 1;

        $t_query = "SELECT * FROM {$t_bug_table}
                    WHERE last_updated between '%dateFrom%' AND {$endOfPreviousDay}
                    AND status IN ";

        $queryOpen = str_replace('%dateFrom%', null, $t_query);

        $closedBugsPreviousDayResults = array();
        $closedWithoutSla = 0;
        $closedPassedSla = 0;
        $closedAtSla = 0;

        $slaPLKId = custom_field_get_id_from_name('SLA_PLK');
        $slaId = custom_field_get_id_from_name('SLA');
        $reasonId = custom_field_get_id_from_name('Przyczyna');

        while ($t_row = db_fetch_array($closedBugsPreviousDayQuery)) {
            if ($regionFilter && !$this->matches_region($t_row, $regionFilter)) {
                continue;
            }

            $closedBugsPreviousDayResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value($reasonId, $t_row['id']);
            $slaValue = custom_field_get_value($slaPLKId, $t_row['id']);

            if ($slaValue === null) {
                $slaValue = custom_field_get_value($slaId, $t_row['id']);
            }

            $categoryName = category_get_field($t_row['category_id'], 'name');

            if (
                in_array($reasonFieldValue, ['Niezasadne', 'Konserwacja', 'Przegląd', 'Kradzież', 'Dewastacja'])
                || in_array($categoryName, ['SDIP 65', 'RTF – P/K', 'Bezumowne', 'Zamówienie', 'Wypowiedzenie']) || !$slaValue
            ) {
                $closedWithoutSla++;
                continue;
            }

            if ($slaValue) {
                $table = plugin_table('time_tracking', 'SlaTimeTracking');
                $query = "SELECT * FROM {$table} WHERE bug_id=" . db_param();
                $result = db_query($query, array($t_row['id']));
                $slaTime = 0;
                if (db_affected_rows() > 0) {
                    $row = db_fetch_array($result);
                    $slaTime = $row['sla_time'];
                }
                if ($slaTime > $this->transformSla($slaValue)) {
                    $closedAtSla++;
                } else {
                    $closedPassedSla++;
                }
            }
        }

        //otwarte zgloszenia stan na koniec poprzedniego dnia
        $openBugsQuery = db_query($queryOpen . '(10,50,60)');

        $openBugsResults = array();
        $openWithoutSla = 0;
        $openAtSla = 0;
        $openPassedSla = 0;
        $till24 = 0;
        $moreThanOneDay = 0;
        $moreThanTwoDays = 0;
        $moreThanThreeDays = 0;
        $over120 = 0;
        while ($t_row = db_fetch_array($openBugsQuery)) {
            if ($regionFilter && !$this->matches_region($t_row, $regionFilter)) {
                continue;
            }

            $openBugsResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value($reasonId, $t_row['id']);

            $categoryName = category_get_field($t_row['category_id'], 'name');
            $slaValue = custom_field_get_value($slaPLKId, $t_row['id']);

            if ($slaValue === null) {
                $slaValue = custom_field_get_value($slaId, $t_row['id']);
            }

            if (
                in_array($reasonFieldValue, ['Niezasadne', 'Konserwacja', 'Przegląd', 'Kradzież', 'Dewastacja'])
                || in_array($categoryName, ['SDIP 65', 'RTF – P/K', 'Bezumowne', 'Zamówienie', 'Wypowiedzenie']) || !$slaValue
            ) {
                $openWithoutSla++;
                continue;
            }


            if ($slaValue) {
                $table = plugin_table('time_tracking', 'SlaTimeTracking');
                $query = "SELECT * FROM {$table} WHERE bug_id=" . db_param();
                $result = db_query($query, array($t_row['id']));
                $slaTime = 0;
                if (db_affected_rows() > 0) {
                    $row = db_fetch_array($result);
                    $slaTime = $row['sla_time'];
                }
                $slaDifference = $slaTime - $this->transformSla($slaValue);

                if ($slaDifference < 0) {
                    $openAtSla++;
                } else {
                    if ($slaDifference < 86400) {
                        $till24++;
                    } elseif ($slaDifference > 86400 && $slaDifference < 172800) {
                        $moreThanOneDay++;
                    } elseif ($slaDifference > 172800 && $slaDifference < 259200) {
                        $moreThanTwoDays++;
                    } elseif ($slaDifference > 259200 && $slaDifference < 432000) {
                        $moreThanThreeDays++;
                    } elseif ($slaDifference > 432000) {
                        $over120++;
                    }

                    $openPassedSla++;
                }
            }
        }

        $dataArray = [
            'closedBugsPreviousDay' => [
                'all' => count($closedBugsPreviousDayResults),
                'withoutSla' => $closedWithoutSla,
                'withoutSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedWithoutSla / count($closedBugsPreviousDayResults)) * 100, 2) : 0,
                'atSla' => $closedAtSla,
                'atSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedAtSla / count($closedBugsPreviousDayResults)) * 100, 2) : 0,
                'crossedSla' => $closedPassedSla,
                'crossedSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedPassedSla / count($closedBugsPreviousDayResults)) * 100, 2) : 0,
            ],
            'openBugs' => [
                'all' => count($openBugsResults),
                'withoutSla' => $openWithoutSla,
                'withoutSlaPercentage' => $openBugsResults ? round(($openWithoutSla / count($openBugsResults)) * 100, 2) : 0,
                'atSla' => $openAtSla,
                'atSlaPercentage' => $openBugsResults ? round(($openAtSla / count($openBugsResults)) * 100, 2) : 0,
                'crossedSla' => $openPassedSla,
                'crossedSlaPercentage' => $openBugsResults ? round(($openPassedSla / count($openBugsResults)) * 100, 2) : 0,
            ],
            'openedPassedSla' => [
                'till24h' => [
                    'num' => $till24,
                    'percentage' => $openPassedSla ? round(($till24 / $openPassedSla) * 100, 2) : 0
                ],
                '24h-48h' => [
                    'num' => $moreThanOneDay,
                    'percentage' => $openPassedSla ? round(($moreThanOneDay / $openPassedSla) * 100, 2) : 0
                ],
                '48h-72h' => [
                    'num' => $moreThanTwoDays,
                    'percentage' => $openPassedSla ? round(($moreThanTwoDays / $openPassedSla) * 100, 2) : 0
                ],
                '72h-120h' => [
                    'num' => $moreThanThreeDays,
                    'percentage' => $openPassedSla ? round(($moreThanThreeDays / $openPassedSla) * 100, 2) : 0
                ],
                'over120h' => [
                    'num' => $over120,
                    'percentage' => $openPassedSla ? round(($over120 / $openPassedSla) * 100, 2) : 0
                ],
            ]
        ];

        return $dataArray;
    }

    private function matches_region($bug, $regionFilter) {
        if (!$regionFilter) {
            return true;
        }

        if ($regionFilter === 'nie przydzielone do żadnego Regionu') {
            $regions = ['R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7'];
            $regionFieldId = 90;
            $regionValue = custom_field_get_value($regionFieldId, $bug['id']);
            $regIn = false;
            $projectName = project_get_field($bug['project_id'], 'name');

            if ($regionValue) {
                foreach ($regions as $reg) {
                    $regIn = stripos($regionValue, $reg) === 0;
                    if ($regIn) {
                        break;
                    }
                }
            } else {
                return preg_match('/^R[1-7]/', $projectName) === 0;
            }
            if ($bug['id'] === '440') {
                var_dump(!$regIn);
                var_dump(preg_match('/^R[1-7]/', $projectName) === 0);
                var_dump(!$regIn || preg_match('/^R[1-7]/', $projectName) === 0);die;
            }

            return !$regIn || !preg_match('/^R[1-7]/', $projectName) === 0;
        } else {
            $regionFieldId = 90;
            $regionValue = custom_field_get_value($regionFieldId, $bug['id']);

            if ($regionValue) {
                return stripos($regionValue, $regionFilter) === 0;
            }

            $projectName = project_get_field($bug['project_id'], 'name');
            if (preg_match('/^R[1-7]/', $projectName, $matches)) {
                $projectRegion = $matches[0]; // e.g. 'R1'
                return $projectRegion === $regionFilter;
            }
        }

        return false;
    }

    function transformSla($slaValue)
    {
        $numberOfHours = (int)$slaValue;

        return $numberOfHours * 60 * 60;
    }
}