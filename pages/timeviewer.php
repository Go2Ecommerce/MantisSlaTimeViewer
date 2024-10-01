<?php
access_ensure_project_level( config_get( 'view_summary_threshold' ) );

layout_page_header();
layout_page_begin( 'summary_page.php' );

$t_filter = summary_get_filter();
print_summary_menu( 'timeviewer.php', $t_filter );

$timeViewerApi = new \SlaTimeViewer\SlaTimeViewerApi();

$statistics = $timeViewerApi->get_statistics();

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
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['withoutSla'] ?></td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['withoutSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_at_sla_time' ) ?></td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['atSla'] ?></td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['atSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_passed_sla_time' ) ?></td>
			<td class="align-right"><?php echo $statistics['closedBugsPreviousDay']['crossedSla'] ?></td>
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
			<td class="align-right"><?php echo $statistics['openBugs']['withoutSla'] ?></td>
			<td class="align-right"><?php echo $statistics['openBugs']['withoutSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_with_sla' ) ?></td>
			<td class="align-right"><?php echo $statistics['openBugs']['atSla'] ?></td>
			<td class="align-right"><?php echo $statistics['openBugs']['atSlaPercentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_passed_sla' ) ?></td>
			<td class="align-right"><?php echo $statistics['openBugs']['crossedSla'] ?></td>
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
			<td class="align-right"><?php echo $statistics['openedPassedSla']['till24h']['num'] ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['till24h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '24_48' ) ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['24h-48h']['num'] ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['24h-48h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '48_72' ) ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['48h-72h']['num'] ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['48h-72h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '72_120' ) ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['72h-120h']['num'] ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['72h-120h']['percentage'] ?>%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'over_120' ) ?></td>
			<td class="align-right"><?php echo $statistics['openedPassedSla']['over120h']['num'] ?></td>
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
