<?php
/**
 * REST endpoints for client-side activity logging and contextual feedback submission.
 * Logged-in testers only for this first version (no anonymous feedback per the Request).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'rbm_test_monitor_register_rest_routes');
function rbm_test_monitor_register_rest_routes() {
    if (!rbm_test_monitor_is_enabled()) {
        return;
    }
    register_rest_route('rbm-test-monitor/v1', '/event', [
        'methods'             => 'POST',
        'callback'            => 'rbm_test_monitor_rest_log_event',
        'permission_callback' => 'rbm_test_monitor_rest_permission',
    ]);
    register_rest_route('rbm-test-monitor/v1', '/feedback', [
        'methods'             => 'POST',
        'callback'            => 'rbm_test_monitor_rest_submit_feedback',
        'permission_callback' => 'rbm_test_monitor_rest_permission',
    ]);
}

function rbm_test_monitor_rest_permission() {
    return rbm_test_monitor_is_enabled() && is_user_logged_in();
}

function rbm_test_monitor_rest_log_event($request) {
    $module = sanitize_key((string) $request->get_param('module')) ?: 'general';
    $action = sanitize_key((string) $request->get_param('action')) ?: 'unknown';
    rbm_test_log_event($module, $action, [
        'view'        => $request->get_param('view'),
        'object_type' => $request->get_param('object_type'),
        'object_id'   => $request->get_param('object_id'),
        'result'      => $request->get_param('result'),
        'detail'      => $request->get_param('detail'),
        'page_url'    => $request->get_param('page_url'),
        'page_title'  => $request->get_param('page_title'),
        'browser'     => $request->get_param('browser'),
        'os'          => $request->get_param('os'),
        'device_type' => $request->get_param('device_type'),
    ]);
    // Always 200: this is fire-and-forget instrumentation, never something the tester should see fail.
    return new WP_REST_Response(['logged' => true], 200);
}

function rbm_test_monitor_rest_submit_feedback($request) {
    global $wpdb;
    $comment = sanitize_textarea_field((string) $request->get_param('comment'));
    if ($comment === '') {
        return new WP_REST_Response(['error' => 'comment_required'], 400);
    }

    $allowed_types = ['Problem', 'Suggestion', 'Question', 'Usability', 'Data Issue', 'Other'];
    $type = $request->get_param('feedback_type');
    $type = in_array($type, $allowed_types, true) ? $type : 'Other';

    $allowed_severity = ['Minor', 'Problem', 'Blocker'];
    $severity = $request->get_param('severity');
    $severity = in_array($severity, $allowed_severity, true) ? $severity : 'Minor';

    $screenshot_path = '';
    $files = $request->get_file_params();
    if (!empty($files['screenshot']['tmp_name'])) {
        $screenshot_path = rbm_test_monitor_handle_screenshot_upload($files['screenshot']);
    }

    $user = wp_get_current_user();
    $data = [
        'created_at'         => current_time('mysql'),
        'session_id'         => sanitize_text_field((string) ($request->get_param('session_id') ?: rbm_test_monitor_current_session_id())),
        'user_id'            => ($user && $user->ID) ? $user->ID : null,
        'user_display_name'  => ($user && $user->ID) ? sanitize_text_field($user->display_name) : '',
        'page_url'           => esc_url_raw((string) $request->get_param('page_url')),
        'page_title'         => sanitize_text_field((string) $request->get_param('page_title')),
        'module'             => sanitize_key((string) $request->get_param('module')) ?: 'general',
        'view'               => sanitize_text_field((string) $request->get_param('view')),
        'last_action'        => sanitize_text_field((string) $request->get_param('last_action')),
        'feedback_type'      => $type,
        'severity'           => $severity,
        'comment'            => $comment,
        'screenshot_path'    => $screenshot_path,
        'browser'            => sanitize_text_field((string) $request->get_param('browser')),
        'os'                 => sanitize_text_field((string) $request->get_param('os')),
        'device_type'        => sanitize_text_field((string) $request->get_param('device_type')),
        'status'             => 'New',
        'admin_notes'        => '',
    ];

    try {
        $wpdb->insert(rbm_test_monitor_feedback_table(), $data);
        $feedback_id = (int) $wpdb->insert_id;
    } catch (\Throwable $e) {
        // Fail-safe: the tester's submit action should not surface a hard error.
        return new WP_REST_Response(['error' => 'store_failed'], 200);
    }

    rbm_test_log_event($data['module'], 'feedback_submitted', [
        'view'       => $data['view'],
        'result'     => $severity,
        'detail'     => wp_trim_words($comment, 12),
        'page_url'   => $data['page_url'],
        'page_title' => $data['page_title'],
    ]);

    if ($feedback_id && in_array($severity, ['Problem', 'Blocker'], true)) {
        rbm_test_monitor_send_feedback_email($feedback_id, $data);
    }

    return new WP_REST_Response(['logged' => true, 'feedback_id' => $feedback_id], 200);
}

// Plain uploads subfolder, not the Media Library — this is transient tester-submitted content,
// not part of the shipped site catalog, and should be trivial to purge via retention cleanup.
function rbm_test_monitor_handle_screenshot_upload($file) {
    $allowed_mimes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp'];

    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return '';
    }
    if (($file['size'] ?? 0) > 3 * MB_IN_BYTES) {
        return '';
    }

    $filetype = wp_check_filetype($file['name'] ?? '');
    $declared_mime = $filetype['type'] ?? '';
    if ($declared_mime === '' || !isset($allowed_mimes[$declared_mime])) {
        return '';
    }

    // Verify actual file content, not just the declared extension/MIME.
    $real_mime = $declared_mime;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $real_mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if (!isset($allowed_mimes[$real_mime])) {
        return '';
    }

    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'rbm-test-screenshots/';
    if (!is_dir($target_dir)) {
        wp_mkdir_p($target_dir);
        @file_put_contents($target_dir . 'index.php', "<?php\n// Silence is golden.\n");
    }

    $filename = 'fb-' . gmdate('Ymd-His') . '-' . strtolower(wp_generate_password(8, false, false)) . '.' . $allowed_mimes[$real_mime];
    $target = $target_dir . $filename;

    if (!@move_uploaded_file($file['tmp_name'], $target)) {
        return '';
    }

    return 'rbm-test-screenshots/' . $filename;
}

function rbm_test_monitor_screenshot_url($relative_path) {
    if ($relative_path === '') {
        return '';
    }
    $upload_dir = wp_upload_dir();
    return trailingslashit($upload_dir['baseurl']) . $relative_path;
}

// Store-only for routine activity/suggestions; email only for Problem (store+notify) and
// Blocker (store+immediate notify) per the PLAN's notification policy.
function rbm_test_monitor_send_feedback_email($feedback_id, $data) {
    try {
        $to = get_option('admin_email');
        if (!$to) {
            return;
        }
        $subject = sprintf(
            '[RBM TEST] %s — %s / %s',
            $data['severity'],
            $data['module'] ?: 'General',
            $data['view'] ?: 'N/A'
        );
        $body  = "Time: " . $data['created_at'] . "\n";
        $body .= "Tester: " . ($data['user_display_name'] ?: 'Unknown') . "\n";
        $body .= "Page: " . $data['page_url'] . "\n";
        $body .= "Module: " . $data['module'] . "\n";
        $body .= "View: " . $data['view'] . "\n";
        $body .= "Session: " . $data['session_id'] . "\n\n";
        $body .= "Comment:\n" . $data['comment'] . "\n\n";
        $body .= "Admin link: " . admin_url('admin.php?page=rbm-test-monitor-feedback&feedback_id=' . (int) $feedback_id) . "\n";
        wp_mail($to, $subject, $body);
    } catch (\Throwable $e) {
        // Fail silently — an email failure must never surface to the tester.
        return;
    }
}
