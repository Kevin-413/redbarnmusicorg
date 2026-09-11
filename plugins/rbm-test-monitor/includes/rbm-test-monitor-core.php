<?php
/**
 * Core: enable/disable gate, test-session cookie, shared logger, and DB table creation.
 * See docs/0910-0344-PLAN-RBM-Testing-Feedback-Activity-Monitor.txt for design rationale.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enabled by default on local/development/staging; disabled on production unless a wp-config.php
// constant explicitly overrides it in either direction (per PLAN's "explicit override required").
function rbm_test_monitor_is_enabled() {
    if (defined('RBM_TEST_MONITOR_ENABLED')) {
        return (bool) RBM_TEST_MONITOR_ENABLED;
    }
    $env = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    return in_array($env, ['local', 'development', 'staging'], true);
}

function rbm_test_monitor_activity_table() {
    global $wpdb;
    return $wpdb->prefix . 'rbm_test_activity';
}

function rbm_test_monitor_feedback_table() {
    global $wpdb;
    return $wpdb->prefix . 'rbm_test_feedback';
}

function rbm_test_monitor_create_tables() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    $activity_table = rbm_test_monitor_activity_table();
    $feedback_table = rbm_test_monitor_feedback_table();

    dbDelta("CREATE TABLE {$activity_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_time DATETIME NOT NULL,
        session_id VARCHAR(40) NOT NULL DEFAULT '',
        user_id BIGINT UNSIGNED NULL,
        user_display_name VARCHAR(191) NOT NULL DEFAULT '',
        module VARCHAR(60) NOT NULL DEFAULT '',
        view VARCHAR(120) NOT NULL DEFAULT '',
        action VARCHAR(60) NOT NULL DEFAULT '',
        object_type VARCHAR(60) NOT NULL DEFAULT '',
        object_id VARCHAR(60) NOT NULL DEFAULT '',
        result VARCHAR(30) NOT NULL DEFAULT '',
        detail TEXT NULL,
        page_url VARCHAR(500) NOT NULL DEFAULT '',
        page_title VARCHAR(255) NOT NULL DEFAULT '',
        browser VARCHAR(120) NOT NULL DEFAULT '',
        os VARCHAR(60) NOT NULL DEFAULT '',
        device_type VARCHAR(30) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY session_id (session_id),
        KEY event_time (event_time),
        KEY module (module)
    ) {$charset_collate};");

    dbDelta("CREATE TABLE {$feedback_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        session_id VARCHAR(40) NOT NULL DEFAULT '',
        user_id BIGINT UNSIGNED NULL,
        user_display_name VARCHAR(191) NOT NULL DEFAULT '',
        page_url VARCHAR(500) NOT NULL DEFAULT '',
        page_title VARCHAR(255) NOT NULL DEFAULT '',
        module VARCHAR(60) NOT NULL DEFAULT '',
        view VARCHAR(120) NOT NULL DEFAULT '',
        last_action VARCHAR(60) NOT NULL DEFAULT '',
        feedback_type VARCHAR(30) NOT NULL DEFAULT 'Other',
        severity VARCHAR(20) NOT NULL DEFAULT 'Minor',
        comment TEXT NOT NULL,
        screenshot_path VARCHAR(500) NOT NULL DEFAULT '',
        browser VARCHAR(120) NOT NULL DEFAULT '',
        os VARCHAR(60) NOT NULL DEFAULT '',
        device_type VARCHAR(30) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'New',
        admin_notes TEXT NULL,
        PRIMARY KEY  (id),
        KEY session_id (session_id),
        KEY created_at (created_at),
        KEY status (status)
    ) {$charset_collate};");
}

// Belt-and-suspenders: create tables on load too, in case the plugin was ever activated before
// this file existed or WP silently skipped the activation hook. dbDelta is idempotent.
add_action('plugins_loaded', function () {
    if (!rbm_test_monitor_is_enabled()) {
        return;
    }
    if (get_option('rbm_test_monitor_tables_verified') !== 'yes') {
        rbm_test_monitor_create_tables();
        update_option('rbm_test_monitor_tables_verified', 'yes', false);
    }
});

// One stable session ID per browser test session, for logged-in testers only, front-end only.
function rbm_test_monitor_generate_session_id() {
    return 'TEST-' . gmdate('Ymd-Hi') . '-' . strtoupper(wp_generate_password(4, false, false));
}

function rbm_test_monitor_current_session_id() {
    return isset($_COOKIE['rbm_test_session']) ? sanitize_text_field(wp_unslash($_COOKIE['rbm_test_session'])) : '';
}

add_action('init', 'rbm_test_monitor_ensure_session', 5);
function rbm_test_monitor_ensure_session() {
    if (!rbm_test_monitor_is_enabled() || is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (!is_user_logged_in()) {
        return;
    }
    if (rbm_test_monitor_current_session_id() !== '') {
        return;
    }
    $sid = rbm_test_monitor_generate_session_id();
    if (!headers_sent()) {
        setcookie('rbm_test_session', $sid, [
            'expires'  => time() + DAY_IN_SECONDS,
            'path'     => '/',
            'secure'   => is_ssl(),
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    $_COOKIE['rbm_test_session'] = $sid;
}

// Shared logger. Must never let a logging failure affect the feature being tested.
function rbm_test_log_event($module, $action, $context = []) {
    if (!rbm_test_monitor_is_enabled()) {
        return;
    }
    try {
        global $wpdb;
        $user = wp_get_current_user();
        $wpdb->insert(rbm_test_monitor_activity_table(), [
            'event_time'        => current_time('mysql'),
            'session_id'        => sanitize_text_field($context['session_id'] ?? rbm_test_monitor_current_session_id()),
            'user_id'           => ($user && $user->ID) ? $user->ID : null,
            'user_display_name' => ($user && $user->ID) ? sanitize_text_field($user->display_name) : '',
            'module'            => sanitize_key((string) $module),
            'view'              => sanitize_text_field((string) ($context['view'] ?? '')),
            'action'            => sanitize_key((string) $action),
            'object_type'       => sanitize_key((string) ($context['object_type'] ?? '')),
            'object_id'         => sanitize_text_field((string) ($context['object_id'] ?? '')),
            'result'            => sanitize_text_field((string) ($context['result'] ?? '')),
            'detail'            => sanitize_textarea_field((string) ($context['detail'] ?? '')),
            'page_url'          => esc_url_raw((string) ($context['page_url'] ?? '')),
            'page_title'        => sanitize_text_field((string) ($context['page_title'] ?? '')),
            'browser'           => sanitize_text_field((string) ($context['browser'] ?? '')),
            'os'                => sanitize_text_field((string) ($context['os'] ?? '')),
            'device_type'       => sanitize_text_field((string) ($context['device_type'] ?? '')),
        ]);
    } catch (\Throwable $e) {
        // Fail silently — monitoring must never break the feature under test.
        return;
    }
}

// --- Retention ---

function rbm_test_monitor_clear_activity_older_than($days) {
    global $wpdb;
    $table = rbm_test_monitor_activity_table();
    $days = max(0, (int) $days);
    return $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE event_time < %s", gmdate('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS))));
}

function rbm_test_monitor_clear_all_activity() {
    global $wpdb;
    return $wpdb->query('TRUNCATE TABLE ' . rbm_test_monitor_activity_table());
}

function rbm_test_monitor_clear_resolved_feedback() {
    global $wpdb;
    $table = rbm_test_monitor_feedback_table();
    return $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE status IN (%s, %s)", 'Resolved', 'Dismissed'));
}
