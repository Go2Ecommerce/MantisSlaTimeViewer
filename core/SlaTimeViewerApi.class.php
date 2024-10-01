<?php
namespace SlaTimeViewer;

class SlaTimeViewerApi {

    function get_statistics() {
        $g_status_enum_string = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,60:wstrzymany,80:resolved,90:closed';

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
        while($t_row = db_fetch_array($closedBugsPreviousDayQuery)) {
            $closedBugsPreviousDayResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value(21, $t_row['id']);
            $categoryName = category_get_field($t_row['category_id'], 'name');

            if (
                in_array($reasonFieldValue, ['Niezasadne', 'Konserwacja', 'Przegląd', 'Kradzież', 'Dewastacja'])
                || in_array($categoryName, ['SDIP 65', 'RTF – P/K', 'Bezumowne', 'Zamówienie', 'Wypowiedzenie'])
            ) {
                $closedWithoutSla++;
            }
        }

        //otwarte zgloszenia stan na koniec poprzedniego dnia
        $openBugsQuery = db_query($queryOpen . '(10,50,60)');

        $openBugsResults = array();
        $openWithoutSla = 0;
        while($t_row = db_fetch_array($openBugsQuery)) {
            $openBugsResults[] = $t_row;
            $reasonFieldValue = custom_field_get_value(21, $t_row['id']);
            $categoryName = category_get_field($t_row['category_id'], 'name');

            if (
                in_array($reasonFieldValue, ['Niezasadne', 'Konserwacja', 'Przegląd', 'Kradzież', 'Dewastacja'])
                || in_array($categoryName, ['SDIP 65', 'RTF – P/K', 'Bezumowne', 'Zamówienie', 'Wypowiedzenie'])
            ) {
                $openWithoutSla++;
            }
        }


        $dataArray = [
            'closedBugsPreviousDay' => [
                'all' => count($closedBugsPreviousDayResults),
                'withoutSla' => $closedWithoutSla,
                'withoutSlaPercentage' => round(($closedWithoutSla/count($closedBugsPreviousDayResults))*100,2),
                'atSla' => 0,
                'atSlaPercentage' => 0,
                'crossedSla' => 0,
                'crossedSlaPercentage' => 0
            ],
            'openBugs' => [
                'all' => count($openBugsResults),
                'withoutSla' => $openWithoutSla,
                'withoutSlaPercentage' => round(($openWithoutSla/count($openBugsResults))*100, 2),
                'atSla' => 0,
                'atSlaPercentage' => 0,
                'crossedSla' => 0,
                'crossedSlaPercentage' => 0
            ],
            'openedPassedSla' => [
                'till24h' => [
                    'num' => 0,
                    'percentage' => 0
                ],
                '24h-48h' => [
                    'num' => 0,
                    'percentage' => 0
                ],
                '48h-72h' => [
                    'num' => 0,
                    'percentage' => 0
                ],
                '72h-120h' => [
                    'num' => 0,
                    'percentage' => 0
                ],
                'over120h' => [
                    'num' => 0,
                    'percentage' => 0
                ],
            ]
        ];

        return $dataArray;
    }
}