<?php
/**
 * Front-end enqueue for the "Report a Problem" control and lightweight Lessons instrumentation.
 * Only loads for logged-in testers when the monitor is enabled; otherwise zero front-end footprint.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'rbm_test_monitor_enqueue_frontend');
function rbm_test_monitor_enqueue_frontend() {
    if (!rbm_test_monitor_is_enabled() || is_admin() || !is_user_logged_in()) {
        return;
    }

    wp_enqueue_style(
        'rbm-test-monitor-feedback',
        RBM_TEST_MONITOR_URL . 'assets/feedback.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'rbm-test-monitor-feedback',
        RBM_TEST_MONITOR_URL . 'assets/feedback.js',
        [],
        '1.0.0',
        true
    );
    wp_localize_script('rbm-test-monitor-feedback', 'rbmTestMonitor', [
        'restUrl'   => esc_url_raw(rest_url('rbm-test-monitor/v1')),
        'nonce'     => wp_create_nonce('wp_rest'),
        'sessionId' => rbm_test_monitor_current_session_id(),
    ]);
}
