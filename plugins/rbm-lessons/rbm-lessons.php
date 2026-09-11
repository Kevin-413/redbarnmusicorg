<?php
/**
 * Plugin Name: Red Barn Music School Lessons
 * Description: Lessons/Instruments data model (msch_lesson CPT, shared msch_instrument taxonomy). Stage 2 of the Lessons module staged reuse plan.
 * Version: 1.0.0
 * Author: Red Barn Music School
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RBM_LESSONS_DIR', __DIR__);
define('RBM_LESSONS_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/includes/rbm-lessons.php';
require_once __DIR__ . '/includes/rbm-lesson-form.php';
require_once __DIR__ . '/includes/rbm-lessons-avada-element.php';
