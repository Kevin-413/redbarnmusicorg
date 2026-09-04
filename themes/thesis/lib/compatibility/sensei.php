<?php
/*
Copyright 2017 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_sensei {
	public function __construct() {
		if (!function_exists('is_sensei')) return;
		add_theme_support('sensei');
		add_action('template_redirect', array($this, 'sensei'));
	}

	public function sensei() {
/*		if (!is_sensei()) {
			if (is_singular('course')) echo "Singular course!\n";
			elseif (is_singular('lesson')) echo "Lesson!\n";
			if (apply_filters('thesis_sensei_optimized', true)) {
				add_filter('sensei_disable_styles', '__return_true');
				add_action('wp_enqueue_scripts', array($this, 'strip_scripts'), 11);
			}
			echo "This is NOT a Sensei page.\n";
			return;
		}*/
		global $thesis, $wp_query;
		add_filter('thesis_use_wp_body_classes', '__return_true');
		if (is_object($thesis) && is_object($thesis->skin) && !empty($thesis->skin->functionality)) {
			$this->post_class = !empty($thesis->skin->functionality['formatting_class']) ?
				trim(esc_attr($thesis->skin->functionality['formatting_class'])) : (!empty($thesis->skin->functionality['editor_grt']) ?
				'grt' : false);
			if (!empty($this->post_class))
				add_filter('post_class', array($this, 'post_class'));
		}
//		echo "This is a sensei page!\n";
		// Reject the Thesis comments template in favor of Sensei?
//		add_filter('thesis_comments_template', '__return_false');
		/*
		WooCommerce archive pages will have results, pagination, and sorting in both top and bottom
		locations (and in that order per WooCommerce CSS defaults).
		*/
//		add_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 35);
//		add_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 9);
//		add_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 5);
		// Remove WooCommerce archive title and cede control to the Thesis Skin archive title
//		if (!is_singular('product') && !is_shop())
//			add_filter('woocommerce_show_page_title', '__return_false');
/*		global $wp_query;*/
		$type = is_single() ? (($cpt = get_post_type()) == 'course' ?
			'single_course' : ($cpt == 'lesson' ?
			'single_lesson' : ($cpt == 'quiz' ?
			'single_quiz' : ($cpt == 'sensei_message' ?
			'single_message' : false)))) : (is_post_type_archive('course') ?
			'archive_course' : (is_post_type_archive('sensei_message') ?
			'archive_message' : (is_tax('lesson-tag') ?
			'archive_lesson' : (isset($wp_query->query_vars['learner_profile']) ?
			'learner_profile' : (isset($wp_query->query_vars['course_results']) ?
			'course_results' : (is_author()
				&& Sensei_Teacher::is_a_teacher(get_query_var('author'))
				&& !user_can(get_query_var('author'), 'manage_options') ?
			'teacher_archive' : false))))));
		if (empty($type)) return;
//		echo "Type is $type!\n";
		if ($type == 'archive_course') {
			if (apply_filters('thesis_sensei_archive_title', true))
				add_filter('course_archive_title', '__return_empty_string');
			// Notify Thesis that Sensei will override the WP Loop
			add_filter('thesis_use_custom_loop', '__return_true');
			if ($type == 'archive_course')
				// Replace the WP Loop contents on appropriate pages
				add_action('thesis_custom_loop', array($this, 'archive_course'));
		}
		else {
			add_filter('post_box_rotator_override', '__return_true');
			add_action('post_box_rotator', array($this, $type));
		}
	}

	public function post_class($classes) {
		$classes[] = $this->post_class;
		return $classes;
	}

/*---:[ Sensei template compatibility ]:---*/

/*
TODO: Add sensei_before_main_content, sensei_pagination, sensei_after_main_conten, and sensei_sidebar actions?
*/

	/*
	Output Node Methods
	===================
	The loop_course() and content_course() methods may not be necessary
	if the template parts can be retrieved in this context.
	*/
	public function loop_course() {
		do_action('sensei_loop_course_before');
		echo "\n<ul class=\"course-container columns-";
		sensei_courses_per_row();
		echo "\">\n";
		do_action('sensei_loop_course_inside_before');
		while (have_posts()) {
			the_post();
			sensei_load_template_part('content', 'course');
		}
		do_action('sensei_loop_course_inside_after');
		echo "</ul>\n";
		do_action('sensei_loop_course_after');
	}

	public function content_course() {
/*		echo "<li ". post_class(WooThemes_Sensei_Course::get_course_loop_content_class().">";
		do_action('sensei_course_content_before', get_the_ID());
		echo "</li>\n";*/
	}

	/*
	Template Methods
	================
	
	*/

	public function single_course() {
		do_action('sensei_single_course_content_inside_before', get_the_ID());
//		echo "This is running!\n";
		the_content();
		do_action('sensei_single_course_content_inside_after', get_the_ID());
	}

	public function single_lesson() {
		do_action('sensei_single_lesson_content_inside_before', get_the_ID());
		if (sensei_can_user_view_lesson()) {
			if (apply_filters('sensei_video_position', 'top', $post->ID) == 'top')
				do_action('sensei_lesson_video', $post->ID);
			the_content();
		}
		else
			the_excerpt();
		do_action('sensei_single_lesson_content_inside_after', get_the_ID());
	}

	public function single_quiz() {
		do_action('sensei_single_quiz_content_inside_before', get_the_ID());
		if (sensei_can_user_view_lesson()) {
			if (sensei_quiz_has_questions()) {
				echo "<form method=\"POST\" action=\"". esc_url_raw(get_permalink()). "\" enctype=\"multipart/form-data\">\n";
				do_action('sensei_single_quiz_questions_before', get_the_id());
				echo "\t<ol id=\"sensei-quiz-list\">\n";
				while (sensei_quiz_has_questions()) {
					sensei_setup_the_question();
					echo "\t\t<li class=\"". sensei_the_question_class(). "\">";
					do_action('sensei_quiz_question_inside_before', sensei_get_the_question_id());
					sensei_the_question_content();
					do_action('sensei_quiz_question_inside_after', sensei_get_the_question_id());
					echo "</li>\n";
				}
				echo "\t</ol>\n";
				do_action('sensei_single_quiz_questions_after', get_the_id());
				echo "</form>\n";
			}
			else
				echo "<div class=\"sensei-message alert\">\n". __('There are no questions for this Quiz yet. Check back soon.', 'woothemes-sensei'). "</div>\n";
			$quiz_lesson = Sensei()->quiz->data->quiz_lesson;
			do_action('sensei_quiz_back_link', $quiz_lesson);
		}
		do_action('sensei_single_quiz_content_inside_after', get_the_ID());
	}

	public function single_message() {
		do_action('sensei_single_message_content_inside_before', get_the_ID());
		the_content();
		do_action('sensei_single_message_content_inside_after', get_the_ID());
	}

	public function archive_course() {
		do_action('sensei_archive_before_course_loop');
		$this->loop_course();
		do_action('sensei_archive_after_course_loop');
	}

	public function archive_message() {
		do_action('sensei_archive_before_message_loop');
		echo "<section id=\"main-sensei_message\" class=\"sensei_message-container\">\n";
		if (have_posts())
			sensei_load_template('loop-message.php');
		else
			echo __('You do not have any messages.', 'woothemes-sensei');
		echo "</section>\n";
		do_action('sensei_archive_after_message_loop');
	}

	public function archive_lesson() {
		do_action('sensei_archive_before_lesson_loop');
		if (have_posts())
			sensei_load_template('loop-lesson.php');
		else
			echo __('No lessons found that match your selection.', 'woothemes-sensei');
		do_action('sensei_archive_after_lesson_loop');
	}

	public function learner_profile() {
		do_action('sensei_learner_profile_content_before');
		echo "<section id=\"learner-info\" class=\"learner-info entry fix\">\n";
		do_action('sensei_learner_profile_inside_content_before');
		$learner_user = get_user_by('slug', get_query_var('learner_profile'));  // get requested learner object
		if (is_a($learner_user, 'WP_User')) {
			Sensei_Learner_Profiles::user_info($learner_user); // show the user information
			Sensei()->course->load_user_courses_content($learner_user); // show the user courses
		}
		else
			echo "<p class=\"sensei-message\">". __('The user requested does not exist.', 'woothemes-sensei'). "</p>\n";
		do_action('sensei_learner_profile_inside_content_after');
		echo "</section>\n";
		do_action('sensei_learner_profile_content_after');
	}

	public function course_results() {
		do_action('sensei_course_results_content_before');
		global $course;
		$course = get_page_by_path($wp_query->query_vars['course_results'], OBJECT, 'course');
		do_action('sensei_course_results_content_inside_before', $course->ID);
		if (is_user_logged_in()) {
			do_action('sensei_course_results_content_inside_before_lessons', $course->ID);
			if (Sensei_Utils::user_started_course($course->ID, get_current_user_id())) {
				echo "<section class=\"course-results-lessons\">\n";
				sensei_the_course_results_lessons();
				echo "</section>\n";
			}
		}
		do_action('sensei_course_results_content_inside_after', $course->ID);
		do_action('sensei_course_results_content_after');
	}

	public function teacher_archive() {
		do_action('sensei_teacher_archive_course_loop_before');
		if (have_posts())
			sensei_load_template('loop-course.php');
		else
			echo __('There are no courses for this teacher.', 'woothemes-sensei');
		do_action('sensei_teacher_archive_course_loop_after');
	}
}