<?php
/**
 * Admin UI: Testing Monitor menu, Activity list, Feedback list/detail, CSV export, retention actions.
 * Simple filtered tables (not WP_List_Table) to keep this testing-only feature lightweight.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'rbm_test_monitor_admin_menu');
function rbm_test_monitor_admin_menu() {
    if (!rbm_test_monitor_is_enabled()) {
        return;
    }
    add_menu_page(
        'Testing Monitor',
        'Testing Monitor',
        'manage_options',
        'rbm-test-monitor-activity',
        'rbm_test_monitor_render_activity_page',
        'dashicons-clipboard',
        76
    );
    add_submenu_page('rbm-test-monitor-activity', 'Activity', 'Activity', 'manage_options', 'rbm-test-monitor-activity', 'rbm_test_monitor_render_activity_page');
    add_submenu_page('rbm-test-monitor-activity', 'Feedback', 'Feedback', 'manage_options', 'rbm-test-monitor-feedback', 'rbm_test_monitor_render_feedback_page');
}

function rbm_test_monitor_admin_notice_disabled_check() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
}

// --- Activity page ---

function rbm_test_monitor_render_activity_page() {
    rbm_test_monitor_admin_notice_disabled_check();
    global $wpdb;
    $table = rbm_test_monitor_activity_table();

    $module = isset($_GET['module']) ? sanitize_key(wp_unslash($_GET['module'])) : '';
    $session = isset($_GET['session']) ? sanitize_text_field(wp_unslash($_GET['session'])) : '';
    $date = isset($_GET['date']) ? sanitize_text_field(wp_unslash($_GET['date'])) : '';
    $action_filter = isset($_GET['action_filter']) ? sanitize_key(wp_unslash($_GET['action_filter'])) : '';

    $where = ['1=1'];
    $params = [];
    if ($module !== '') { $where[] = 'module = %s'; $params[] = $module; }
    if ($session !== '') { $where[] = 'session_id = %s'; $params[] = $session; }
    if ($action_filter !== '') { $where[] = 'action = %s'; $params[] = $action_filter; }
    if ($date !== '') { $where[] = 'DATE(event_time) = %s'; $params[] = $date; }
    $where_sql = implode(' AND ', $where);

    $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY event_time DESC LIMIT 100";
    $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);

    echo '<div class="wrap"><h1>Testing Activity</h1>';
    $public_feedback_on = rbm_test_monitor_public_feedback_enabled();
    echo '<p>';
    echo 'Suggestions button: <strong>' . ($public_feedback_on ? 'Open to everyone' : 'Logged-in testers only') . '</strong> &mdash; ';
    echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_test_monitor_toggle_public_feedback'), 'rbm_test_monitor_toggle_public_feedback')) . '">';
    echo $public_feedback_on ? 'Turn Off (logged-in testers only)' : 'Turn On (open to everyone)';
    echo '</a>';
    echo '</p>';
    echo '<form method="get" style="margin:12px 0;">';
    echo '<input type="hidden" name="page" value="rbm-test-monitor-activity">';
    echo '<input type="text" name="module" placeholder="Module" value="' . esc_attr($module) . '"> ';
    echo '<input type="text" name="session" placeholder="Session ID" value="' . esc_attr($session) . '"> ';
    echo '<input type="text" name="action_filter" placeholder="Action" value="' . esc_attr($action_filter) . '"> ';
    echo '<input type="date" name="date" value="' . esc_attr($date) . '"> ';
    echo '<button type="submit" class="button">Filter</button> ';
    echo '<a href="' . esc_url(admin_url('admin.php?page=rbm-test-monitor-activity')) . '" class="button">Reset</a>';
    echo '</form>';

    echo '<p>';
    echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_test_monitor_export_activity'), 'rbm_test_monitor_export_activity')) . '">Export CSV</a> ';
    echo '<a class="button" style="color:#a00;" onclick="return confirm(\'Clear all test activity? This cannot be undone.\');" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_test_monitor_clear_activity'), 'rbm_test_monitor_clear_activity')) . '">Clear Test Logs</a>';
    echo '</p>';

    echo '<table class="widefat striped"><thead><tr>';
    foreach (['Time', 'Session', 'User', 'Module', 'View', 'Action', 'Record', 'Result', 'Detail'] as $col) {
        echo '<th>' . esc_html($col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    if (empty($rows)) {
        echo '<tr><td colspan="9">No activity recorded yet.</td></tr>';
    }
    foreach ((array) $rows as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row->event_time) . '</td>';
        echo '<td><a href="' . esc_url(admin_url('admin.php?page=rbm-test-monitor-activity&session=' . rawurlencode($row->session_id))) . '">' . esc_html($row->session_id) . '</a></td>';
        echo '<td>' . esc_html($row->user_display_name) . '</td>';
        echo '<td>' . esc_html($row->module) . '</td>';
        echo '<td>' . esc_html($row->view) . '</td>';
        echo '<td>' . esc_html($row->action) . '</td>';
        echo '<td>' . esc_html(trim($row->object_type . ' ' . $row->object_id)) . '</td>';
        echo '<td>' . esc_html($row->result) . '</td>';
        echo '<td>' . esc_html($row->detail) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p style="color:#666;">Showing latest 100 matching events. Routine activity auto-clears after 30 days via "Clear Test Logs" or manual retention cleanup.</p>';
    echo '</div>';
}

// --- Feedback page (list + detail) ---

function rbm_test_monitor_render_feedback_page() {
    rbm_test_monitor_admin_notice_disabled_check();
    $feedback_id = isset($_GET['feedback_id']) ? (int) $_GET['feedback_id'] : 0;
    if ($feedback_id > 0) {
        rbm_test_monitor_render_feedback_detail($feedback_id);
        return;
    }

    global $wpdb;
    $table = rbm_test_monitor_feedback_table();

    $status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
    $severity = isset($_GET['severity']) ? sanitize_text_field(wp_unslash($_GET['severity'])) : '';
    $module = isset($_GET['module']) ? sanitize_key(wp_unslash($_GET['module'])) : '';

    $where = ['1=1'];
    $params = [];
    if ($status !== '') { $where[] = 'status = %s'; $params[] = $status; }
    if ($severity !== '') { $where[] = 'severity = %s'; $params[] = $severity; }
    if ($module !== '') { $where[] = 'module = %s'; $params[] = $module; }
    $where_sql = implode(' AND ', $where);

    $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT 100";
    $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);

    echo '<div class="wrap"><h1>Testing Feedback</h1>';
    echo '<form method="get" style="margin:12px 0;">';
    echo '<input type="hidden" name="page" value="rbm-test-monitor-feedback">';
    echo '<select name="status"><option value="">Any Status</option>';
    foreach (['New', 'Reviewed', 'Resolved', 'Dismissed'] as $s) {
        echo '<option value="' . esc_attr($s) . '"' . selected($status, $s, false) . '>' . esc_html($s) . '</option>';
    }
    echo '</select> ';
    echo '<select name="severity"><option value="">Any Severity</option>';
    foreach (['Minor', 'Problem', 'Blocker'] as $s) {
        echo '<option value="' . esc_attr($s) . '"' . selected($severity, $s, false) . '>' . esc_html($s) . '</option>';
    }
    echo '</select> ';
    echo '<input type="text" name="module" placeholder="Module" value="' . esc_attr($module) . '"> ';
    echo '<button type="submit" class="button">Filter</button> ';
    echo '<a href="' . esc_url(admin_url('admin.php?page=rbm-test-monitor-feedback')) . '" class="button">Reset</a>';
    echo '</form>';

    echo '<p>';
    echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_test_monitor_export_feedback'), 'rbm_test_monitor_export_feedback')) . '">Export CSV</a> ';
    echo '<a class="button" style="color:#a00;" onclick="return confirm(\'Clear all Resolved/Dismissed feedback? This cannot be undone.\');" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_test_monitor_clear_resolved_feedback'), 'rbm_test_monitor_clear_resolved_feedback')) . '">Clear Resolved Feedback</a>';
    echo '</p>';

    if (isset($_GET['bulk_deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . (int) $_GET['bulk_deleted'] . ' feedback item(s) deleted.</p></div>';
    }

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" id="rbm-tm-bulk-form">';
    wp_nonce_field('rbm_test_monitor_bulk_feedback');
    echo '<input type="hidden" name="action" value="rbm_test_monitor_bulk_feedback">';
    echo '<div style="margin:8px 0;">';
    echo '<select name="bulk_action"><option value="">Bulk Actions</option><option value="delete">Delete</option></select> ';
    echo '<button type="submit" class="button" onclick="return confirm(\'Delete the selected feedback item(s)? This cannot be undone.\');">Apply</button>';
    echo '</div>';

    echo '<table class="widefat striped"><thead><tr>';
    echo '<th style="width:24px;"><input type="checkbox" onclick="document.querySelectorAll(\'.rbm-tm-row-check\').forEach(function(c){c.checked=this.checked;}.bind(this));"></th>';
    foreach (['Time', 'Severity', 'Type', 'User', 'Module', 'Page/View', 'Comment', 'Status', ''] as $col) {
        echo '<th>' . esc_html($col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    if (empty($rows)) {
        echo '<tr><td colspan="10">No feedback submitted yet.</td></tr>';
    }
    foreach ((array) $rows as $row) {
        $detail_url = admin_url('admin.php?page=rbm-test-monitor-feedback&feedback_id=' . (int) $row->id);
        echo '<tr>';
        echo '<td><input type="checkbox" class="rbm-tm-row-check" name="feedback_ids[]" value="' . (int) $row->id . '"></td>';
        echo '<td>' . esc_html($row->created_at) . '</td>';
        echo '<td>' . esc_html($row->severity) . '</td>';
        echo '<td>' . esc_html($row->feedback_type) . '</td>';
        echo '<td>' . esc_html($row->user_display_name) . '</td>';
        echo '<td>' . esc_html($row->module) . '</td>';
        echo '<td>' . esc_html(trim($row->page_title . ' / ' . $row->view)) . '</td>';
        echo '<td>' . esc_html(wp_trim_words($row->comment, 10)) . '</td>';
        echo '<td>' . esc_html($row->status) . '</td>';
        echo '<td><a href="' . esc_url($detail_url) . '">View</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</form></div>';
}

function rbm_test_monitor_render_feedback_detail($feedback_id) {
    global $wpdb;
    $feedback = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . rbm_test_monitor_feedback_table() . ' WHERE id = %d', $feedback_id));
    if (!$feedback) {
        echo '<div class="wrap"><h1>Testing Feedback</h1><p>Not found.</p></div>';
        return;
    }

    $recent = $wpdb->get_results($wpdb->prepare(
        'SELECT * FROM ' . rbm_test_monitor_activity_table() . ' WHERE session_id = %s AND event_time <= %s ORDER BY event_time DESC LIMIT 20',
        $feedback->session_id,
        $feedback->created_at
    ));
    $recent = array_reverse((array) $recent);

    echo '<div class="wrap"><h1>Feedback #' . (int) $feedback->id . '</h1>';
    echo '<p><a href="' . esc_url(admin_url('admin.php?page=rbm-test-monitor-feedback')) . '">&larr; Back to list</a></p>';

    echo '<p>';
    echo '<button type="submit" form="rbm-tm-status-form" class="button button-primary">Save</button> ';
    echo '<button type="submit" form="rbm-tm-delete-form" class="button" style="color:#a00;">Delete This Feedback</button>';
    echo '</p>';

    echo '<table class="form-table"><tbody>';
    $fields = [
        'Time' => $feedback->created_at,
        'Severity' => $feedback->severity,
        'Type' => $feedback->feedback_type,
        'User' => $feedback->user_display_name,
        'Page URL' => $feedback->page_url,
        'Module' => $feedback->module,
        'View' => $feedback->view,
        'Last Action' => $feedback->last_action,
        'Session ID' => $feedback->session_id,
        'Browser' => $feedback->browser,
        'OS' => $feedback->os,
        'Device' => $feedback->device_type,
    ];
    foreach ($fields as $label => $value) {
        echo '<tr><th>' . esc_html($label) . '</th><td>' . esc_html((string) $value) . '</td></tr>';
    }
    echo '<tr><th>Comment</th><td>' . nl2br(esc_html($feedback->comment)) . '</td></tr>';
    if ($feedback->screenshot_path) {
        echo '<tr><th>Screenshot</th><td><img src="' . esc_url(rbm_test_monitor_screenshot_url($feedback->screenshot_path)) . '" style="max-width:400px;height:auto;border:1px solid #ccc;"></td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>Recent Activity Leading Up to This Report</h2>';
    if (empty($recent)) {
        echo '<p>No preceding activity recorded for this session.</p>';
    } else {
        echo '<table class="widefat striped"><thead><tr><th>Time</th><th>Module</th><th>Action</th><th>View</th><th>Detail</th></tr></thead><tbody>';
        foreach ($recent as $row) {
            echo '<tr><td>' . esc_html($row->event_time) . '</td><td>' . esc_html($row->module) . '</td><td>' . esc_html($row->action) . '</td><td>' . esc_html($row->view) . '</td><td>' . esc_html($row->detail) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    echo '<h2>Status</h2>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" id="rbm-tm-status-form">';
    wp_nonce_field('rbm_test_monitor_save_feedback_status');
    echo '<input type="hidden" name="action" value="rbm_test_monitor_save_feedback_status">';
    echo '<input type="hidden" name="feedback_id" value="' . (int) $feedback->id . '">';
    echo '<select name="status">';
    foreach (['New', 'Reviewed', 'Resolved', 'Dismissed'] as $s) {
        echo '<option value="' . esc_attr($s) . '"' . selected($feedback->status, $s, false) . '>' . esc_html($s) . '</option>';
    }
    echo '</select><br><br>';
    echo '<textarea name="admin_notes" rows="4" style="width:100%;max-width:500px;" placeholder="Admin notes">' . esc_textarea($feedback->admin_notes) . '</textarea><br><br>';
    echo '<button type="submit" class="button button-primary">Save</button>';
    echo '</form>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" id="rbm-tm-delete-form" style="margin-top:12px;" onsubmit="return confirm(\'Delete this feedback item? This cannot be undone.\');">';
    wp_nonce_field('rbm_test_monitor_bulk_feedback');
    echo '<input type="hidden" name="action" value="rbm_test_monitor_bulk_feedback">';
    echo '<input type="hidden" name="bulk_action" value="delete">';
    echo '<input type="hidden" name="feedback_ids[]" value="' . (int) $feedback->id . '">';
    echo '<button type="submit" class="button" style="color:#a00;">Delete This Feedback</button>';
    echo '</form>';
    echo '</div>';
}

add_action('admin_post_rbm_test_monitor_save_feedback_status', 'rbm_test_monitor_handle_save_feedback_status');
function rbm_test_monitor_handle_save_feedback_status() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_save_feedback_status');
    global $wpdb;
    $id = isset($_POST['feedback_id']) ? (int) $_POST['feedback_id'] : 0;
    $allowed_status = ['New', 'Reviewed', 'Resolved', 'Dismissed'];
    $status = isset($_POST['status']) && in_array($_POST['status'], $allowed_status, true) ? $_POST['status'] : 'New';
    $notes = isset($_POST['admin_notes']) ? sanitize_textarea_field(wp_unslash($_POST['admin_notes'])) : '';
    if ($id > 0) {
        $wpdb->update(rbm_test_monitor_feedback_table(), ['status' => $status, 'admin_notes' => $notes], ['id' => $id]);
    }
    wp_safe_redirect(admin_url('admin.php?page=rbm-test-monitor-feedback&feedback_id=' . $id));
    exit;
}

// --- CSV export ---

add_action('admin_post_rbm_test_monitor_export_activity', 'rbm_test_monitor_handle_export_activity');
function rbm_test_monitor_handle_export_activity() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_export_activity');
    global $wpdb;
    $rows = $wpdb->get_results('SELECT * FROM ' . rbm_test_monitor_activity_table() . ' ORDER BY event_time DESC', ARRAY_A);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rbm-test-activity.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

add_action('admin_post_rbm_test_monitor_export_feedback', 'rbm_test_monitor_handle_export_feedback');
function rbm_test_monitor_handle_export_feedback() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_export_feedback');
    global $wpdb;
    $rows = $wpdb->get_results('SELECT * FROM ' . rbm_test_monitor_feedback_table() . ' ORDER BY created_at DESC', ARRAY_A);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rbm-test-feedback.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

// --- Retention actions ---

add_action('admin_post_rbm_test_monitor_toggle_public_feedback', 'rbm_test_monitor_handle_toggle_public_feedback');
function rbm_test_monitor_handle_toggle_public_feedback() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_toggle_public_feedback');
    update_option('rbm_test_monitor_public_feedback', rbm_test_monitor_public_feedback_enabled() ? '0' : '1', false);
    wp_safe_redirect(admin_url('admin.php?page=rbm-test-monitor-activity'));
    exit;
}

add_action('admin_post_rbm_test_monitor_clear_activity', 'rbm_test_monitor_handle_clear_activity');
function rbm_test_monitor_handle_clear_activity() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_clear_activity');
    rbm_test_monitor_clear_all_activity();
    wp_safe_redirect(admin_url('admin.php?page=rbm-test-monitor-activity&cleared=1'));
    exit;
}

add_action('admin_post_rbm_test_monitor_clear_resolved_feedback', 'rbm_test_monitor_handle_clear_resolved_feedback');
function rbm_test_monitor_handle_clear_resolved_feedback() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_clear_resolved_feedback');
    rbm_test_monitor_clear_resolved_feedback();
    wp_safe_redirect(admin_url('admin.php?page=rbm-test-monitor-feedback&cleared=1'));
    exit;
}

add_action('admin_post_rbm_test_monitor_bulk_feedback', 'rbm_test_monitor_handle_bulk_feedback');
function rbm_test_monitor_handle_bulk_feedback() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_test_monitor_bulk_feedback');
    global $wpdb;

    $bulk_action = isset($_POST['bulk_action']) ? sanitize_key(wp_unslash($_POST['bulk_action'])) : '';
    $ids = isset($_POST['feedback_ids']) ? array_map('intval', (array) $_POST['feedback_ids']) : [];
    $ids = array_filter($ids);

    $deleted = 0;
    if ($bulk_action === 'delete' && !empty($ids)) {
        $table = rbm_test_monitor_feedback_table();
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $screenshots = $wpdb->get_col($wpdb->prepare("SELECT screenshot_path FROM {$table} WHERE id IN ({$placeholders})", $ids));
        foreach ($screenshots as $relative_path) {
            if ($relative_path) {
                $upload_dir = wp_upload_dir();
                $full_path = trailingslashit($upload_dir['basedir']) . $relative_path;
                if (is_file($full_path)) {
                    @unlink($full_path);
                }
            }
        }
        $deleted = (int) $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids));
    }

    wp_safe_redirect(admin_url('admin.php?page=rbm-test-monitor-feedback&bulk_deleted=' . $deleted));
    exit;
}
