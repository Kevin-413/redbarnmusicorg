<?php
/**
 * Plugin Name: Red Barn Music School Faculty
 * Description: Faculty/Teacher directory system (msch_teacher CPT, msch_instrument taxonomy, standalone Add/Edit form, Portrait/Card photos, shared placeholder, [msch_teachers] shortcode, and the Avada Music School Teachers element). Moved from mu-plugins so it can be deployed as a single installable/activatable plugin.
 * Version: 1.0.0
 * Author: Red Barn Music School
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/rbm-teachers.php';
require_once __DIR__ . '/includes/rbm-teacher-form.php';
require_once __DIR__ . '/includes/rbm-teachers-avada-element.php';
