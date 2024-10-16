<?php
namespace SlaTimeViewer;

class SlaTimeViewerApi {

    function get_statistics() {
        $t_bug_table = db_get_table('bug');

        //wyciaganie zlecen z dnia poprzedniego
        $beginOfPreviousDay = strtotime("yesterday");
        $endOfPreviousDay = strtotime("today") - 1;

    	// $table = plugin_table('time_tracking', 'SlaTimeTracking');
        $t_query = "SELECT * FROM {$t_bug_table}
                    WHERE last_updated between '%dateFrom%' AND {$endOfPreviousDay}
                    AND status IN ";

        $queryOpen = str_replace('%dateFrom%', null, $t_query);

        $queryClosed = str_replace('%dateFrom%', $beginOfPreviousDay, $t_query);


        //zdarzenia zamkniete w poprzednim dniu
        $closedBugsPreviousDayQuery = db_query($queryClosed . '(80,90)');

        $closedBugsPreviousDayResults = array();
        $closedWithoutSla = 0;
        $closedPassedSla = 0;
        $closedAtSla = 0;

        while($t_row = db_fetch_array($closedBugsPreviousDayQuery)) {
            $closedBugsPreviousDayResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value(21, $t_row['id']);
            $slaValue = custom_field_get_value(128, $t_row['id']);
            $categoryName = category_get_field($t_row['category_id'], 'name');

            if (
                in_array($reasonFieldValue, ['Niezasadne', 'Konserwacja', 'Przegląd', 'Kradzież', 'Dewastacja'])
                || in_array($categoryName, ['SDIP 65', 'RTF – P/K', 'Bezumowne', 'Zamówienie', 'Wypowiedzenie']) || !$slaValue
            ) {
                $closedWithoutSla++;
                break;
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
        while($t_row = db_fetch_array($openBugsQuery)) {
            $openBugsResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value(21, $t_row['id']);

            $categoryName = category_get_field($t_row['category_id'], 'name');
            $slaValue = custom_field_get_value(128, $t_row['id']);

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
                'withoutSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedWithoutSla/count($closedBugsPreviousDayResults))*100,2) : 0,
                'atSla' => $closedAtSla,
                'atSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedAtSla/count($closedBugsPreviousDayResults))*100,2) : 0,
                'crossedSla' => $closedPassedSla,
                'crossedSlaPercentage' => $closedBugsPreviousDayResults ? round(($closedPassedSla/count($closedBugsPreviousDayResults))*100,2) : 0,
            ],
            'openBugs' => [
                'all' => count($openBugsResults),
                'withoutSla' => $openWithoutSla,
                'withoutSlaPercentage' => $openBugsResults ? round(($openWithoutSla/count($openBugsResults))*100, 2) : 0,
                'atSla' => $openAtSla,
                'atSlaPercentage' => $openBugsResults ? round(($openAtSla/count($openBugsResults))*100, 2) : 0,
                'crossedSla' => $openPassedSla,
                'crossedSlaPercentage' => $openBugsResults ? round(($openPassedSla/count($openBugsResults))*100, 2) : 0,
            ],
            'openedPassedSla' => [
                'till24h' => [
                    'num' => $till24,
                    'percentage' => $openPassedSla ? round(($till24/$openPassedSla)*100, 2) : 0
                ],
                '24h-48h' => [
                    'num' => $moreThanOneDay,
                    'percentage' => $openPassedSla ? round(($moreThanOneDay/$openPassedSla)*100, 2) : 0
                ],
                '48h-72h' => [
                    'num' => $moreThanTwoDays,
                    'percentage' => $openPassedSla ? round(($moreThanTwoDays/$openPassedSla)*100, 2) : 0
                ],
                '72h-120h' => [
                    'num' => $moreThanThreeDays,
                    'percentage' => $openPassedSla ? round(($moreThanThreeDays/$openPassedSla)*100, 2) : 0
                ],
                'over120h' => [
                    'num' => $over120,
                    'percentage' => $openPassedSla ? round(($over120/$openPassedSla)*100, 2) : 0
                ],
            ]
        ];

        return $dataArray;
    }

    function transformSla($slaValue) {
        $numberOfHours = (int)$slaValue;

        return $numberOfHours*60*60;
    }
}