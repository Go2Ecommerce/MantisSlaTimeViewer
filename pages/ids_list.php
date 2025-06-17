<?php
access_ensure_project_level(config_get('view_summary_threshold'));
layout_page_header();
layout_page_begin();

$key = gpc_get_string('key', '');
$title = gpc_get_string('title', 'Lista zgłoszeń');
$page = max(1, gpc_get_int('pagination', 1));
$per_page = gpc_get_int('per_page', 100);

$available_limits = [10, 50, 100, 250, 500];

$bug_ids = [];
if (isset($_SESSION['sla_ids'][$key])) {
    $bug_ids = $_SESSION['sla_ids'][$key];
}

$total = count($bug_ids);
$total_pages = (int)ceil($total / $per_page);

$start_index = ($page - 1) * $per_page;
$bug_ids_page = array_slice($bug_ids, $start_index, $per_page);
$start_display = $start_index + 1;
$end_display = min($start_index + count($bug_ids_page), $total);
?>
    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>
        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-list"></i>
                    <?php echo string_display_line($title); ?>
                </h4>
            </div>
            <div class="widget-body">
                <div class="widget-main no-padding">
                    <?php if (empty($bug_ids_page)): ?>
                        <div class="alert alert-info" style="margin: 15px;">
                            Brak zgłoszeń do wyświetlenia.
                        </div>
                    <?php else: ?>
<!--                        <div style="margin: 15px;">-->
<!--                            <form method="get" style="display: inline-block;">-->
<!--                                <input type="hidden" name="key" value="--><?php //echo string_attribute($key); ?><!--">-->
<!--                                <input type="hidden" name="title" value="--><?php //echo string_attribute($title); ?><!--">-->
<!--                                <input type="hidden" name="pagination" value="1">-->
<!--                                Pokaż-->
<!--                                <select name="per_page" onchange="this.form.submit()">-->
<!--                                    --><?php //foreach ($available_limits as $limit): ?>
<!--                                        <option value="--><?php //echo $limit; ?><!--" --><?php //echo $limit === $per_page ? 'selected' : ''; ?>
<!--                                            --><?php //echo $limit; ?>
<!--                                        </option>-->
<!--                                    --><?php //endforeach; ?>
<!--                                </select>-->
<!--                                wyników na stronę-->
<!--                            </form>-->
<!--                        </div>-->

                        <div style="margin: 15px;">
                            <strong>Wyświetlam:</strong> <?php echo $start_display; ?>–<?php echo $end_display; ?> z <?php echo $total; ?> zgłoszeń
                        </div>

                        <table class="table table-hover table-bordered table-condensed table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tytuł</th>
                                <th>Status</th>
                                <th>Link</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($bug_ids_page as $id):
                                $bug = bug_get((int)$id, true);
                                ?>
                                <tr>
                                    <td><?php echo bug_format_id($id); ?></td>
                                    <td><?php echo string_display_line($bug->summary); ?></td>
                                    <td><?php echo get_enum_element('status', $bug->status); ?></td>
                                    <td><a href="<?php echo string_get_bug_view_url($id); ?>" target="_blank">Zobacz</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin: 15px;">
                            <strong>Strony:</strong>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="plugin.php?page=SlaTimeViewer/ids_list.php&key=<?php echo urlencode($key); ?>&title=<?php echo urlencode($title); ?>&pagination=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php layout_page_end(); ?>