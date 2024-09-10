<?php
namespace SlaTimeViewer;

class SlaTimeViewerApi {

    function get_statistics() {
    	$table = plugin_table('time_tracking', 'SlaTimeTracking');
        $t_query = "SELECT * FROM {$table}";

        return db_num_rows(db_query($t_query));
    }
}