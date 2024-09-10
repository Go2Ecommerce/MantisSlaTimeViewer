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
			<td class="align-right">20</td>
			<td class="align-right">24%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_at_sla_time' ) ?></td>
			<td class="align-right">47</td>
			<td class="align-right">57%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_passed_sla_time' ) ?></td>
			<td class="align-right">16</td>
			<td class="align-right">19%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_sum' ) ?></td>
			<td class="align-right"><?php echo $statistics ?></td>
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
			<td class="align-right">131</td>
			<td class="align-right">52%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_at_sla_time' ) ?></td>
			<td class="align-right">47</td>
			<td class="align-right">19%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'closed_passed_sla_time' ) ?></td>
			<td class="align-right">75</td>
			<td class="align-right">30%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'opened_sum' ) ?></td>
			<td class="align-right">253</td>
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
			<td class="align-right">26</td>
			<td class="align-right">35%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '24_48' ) ?></td>
			<td class="align-right">8</td>
			<td class="align-right">11%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '48_72' ) ?></td>
			<td class="align-right">5</td>
			<td class="align-right">7%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( '72_120' ) ?></td>
			<td class="align-right">6</td>
			<td class="align-right">8%</td>
		</tr>
		<tr>
			<td><?php echo plugin_lang_get( 'over_120' ) ?></td>
			<td class="align-right">30</td>
			<td class="align-right">40%</td>
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
