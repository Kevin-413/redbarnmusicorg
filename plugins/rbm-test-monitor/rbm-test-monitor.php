<?php
/**
 * Plugin Name: RBM Testing Feedback & Activity Monitor
 * Description: Testing-only activity log + contextual feedback tool. Disabled on production by default (docs/0910-0344-PLAN-RBM-Testing-Feedback-Activity-Monitor.txt, docs/0910-0346-Copilot-REQUEST-Implement-RBM-Testing-Feedback-Activity-Monitor.txt).
 * Version: 1.0.0
 * Author: Red Barn Music School
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RBM_TEST_MONITOR_DIR', __DIR__);
define('RBM_TEST_MONITOR_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/includes/rbm-test-monitor-core.php';
require_once __DIR__ . '/includes/rbm-test-monitor-rest.php';
require_once __DIR__ . '/includes/rbm-test-monitor-frontend.php';
require_once __DIR__ . '/includes/rbm-test-monitor-admin.php';

register_activation_hook(__FILE__, 'rbm_test_monitor_create_tables');
