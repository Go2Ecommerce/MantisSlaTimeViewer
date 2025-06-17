<?php
access_ensure_project_level( config_get( 'view_summary_threshold' ) );

layout_page_header();
layout_page_begin( 'summary_page.php' );

$t_filter = summary_get_filter();
print_summary_menu('timeviewer.php', $t_filter);

// Filters
$selected_region = gpc_get_string( 'region', '');
$start_date = gpc_get_string( 'start_date', '' );
$end_date = gpc_get_string( 'end_date', '' );
// Form with region select
?>
    <form method="get" id="regionFilterForm" style="margin: 15px 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <input type="hidden" name="page" value="SlaTimeViewer/timeviewer.php">
        <label for="region">Region:</label>
        <select name="region" id="region">
            <option value="">-- Wszystkie --</option>
            <?php
            $regions = ['R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'nie przydzielone do żadnego Regionu'];
            foreach ( $regions as $region ) {
                $selected = $selected_region === $region ? 'selected' : '';
                echo "<option value=\"$region\" $selected>$region</option>";
            }
            ?>
        </select>

        <label for="start_date">Od:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo string_attribute($start_date) ?>">

        <label for="end_date">Do:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo string_attribute($end_date) ?>">

        <button type="submit">Filtruj</button>
    </form>
<?php

$timeViewerApi = new \SlaTimeViewer\SlaTimeViewerApi();

$statistics = $timeViewerApi->get_statistics($selected_region, $start_date, $end_date);

function store_ids_in_session(array $ids): string {
    $key = uniqid('sla_', true);
    $_SESSION['sla_ids'][$key] = $ids;
    return $key;
}

function sla_list_link(array $ids, string $label): string {
    if (empty($ids)) {
        return 0;
    }
    $key = store_ids_in_session($ids);
    $url = plugin_page('ids_list.php') . '&key=' . urlencode($key) . '&title=' . urlencode($label);
    return sprintf('<a href="%s" target="_blank">%s</a>', $url, count($ids));
}

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-bar-chart-o"></i>
		<?php echo lang_get('summary_title') ?>
	</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">


<!-- LEFT COLUMN -->
<div class="col-md-12 col-xs-12">
	<!-- TIME STATS -->
	<div class="space-10"></div>
	<div class="widget-box table-responsive">
		<table class="table table-hover table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<th colspan="3"><?php echo plugin_lang_get( 'closed_at_day' ) ?> <?php echo date( 'd.m.Y',strtotime("-1 days") ) ?></th>
			</tr>
		</thead>
		<tr>
			<td><?php echo plugin_lang_get( 'without_sla' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['closedBugsPreviousDay']['withoutSlaIds'], plugin_lang_get('without_sla')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['withoutSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_at_sla_time' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['closedBugsPreviousDay']['atSlaIds'], plugin_lang_get('closed_at_sla_time')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['atSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_passed_sla_time' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['closedBugsPreviousDay']['crossedSlaIds'], plugin_lang_get('closed_passed_sla_time')); ?>
            <td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['crossedSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_sum' ) ?></td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['all'] ?></td>
		</tr>
	</table>
	</div>
		<div class="space-10"></div>
	<div class="widget-box table-responsive">
		<table class="table table-hover table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<th colspan="3"><?php echo plugin_lang_get( 'opened' ) ?> <?php echo date( 'd.m.Y' ) ?></th>
			</tr>
		</thead>
		<tr>
			<td><?php echo plugin_lang_get( 'without_sla' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openBugs']['withoutSlaIds'], plugin_lang_get('without_sla')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openBugs']['withoutSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_with_sla' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openBugs']['atSlaIds'], plugin_lang_get('opened_with_sla')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openBugs']['atSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_passed_sla' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openBugs']['crossedSlaIds'], plugin_lang_get('opened_passed_sla')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openBugs']['crossedSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_sum' ) ?></td>
			<td class="align-right"><?php echo $statistics['openBugs']['all'] ?></td>
		</tr>
	</table>
	</div>
		<div class="space-10"></div>
	<div class="widget-box table-responsive">
		<table class="table table-hover table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<th colspan="3"><?php echo plugin_lang_get( 'opened_passed_sla' ) ?></th>
			</tr>
		</thead>
		<tr>
			<td><?php echo plugin_lang_get( 'till_24' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openedPassedSla']['till24h']['ids'], plugin_lang_get('till_24')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['till24h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '24_48' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openedPassedSla']['24h-48h']['ids'], plugin_lang_get('24_48')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['24h-48h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '48_72' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openedPassedSla']['48h-72h']['ids'], plugin_lang_get('48_72')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['48h-72h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '72_120' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openedPassedSla']['72h-120h']['ids'], plugin_lang_get('72_120')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['72h-120h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'over_120' ) ?></td>
			<td class="align-right">
                <?php echo sla_list_link($statistics['openedPassedSla']['over120h']['ids'], plugin_lang_get('over_120')); ?>
            </td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['over120h']['percentage'] ?>%</td>
		</tr>
	</table>
	</div>
</div>
</div>
</div>

</div>
</div>
<div class="clearfix"></div>
<div class="space-10"></div>
</div>
</div>

<?php
layout_page_end();
