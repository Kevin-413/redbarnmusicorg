<?php

function thesis_classic_r_defaults() {
	return array (
  'boxes' => 
  array (
    'thesis_html_container' => 
    array (
      'thesis_html_container_1348009564' => 
      array (
        'id' => 'header',
        'class' => 'header',
        '_id' => 'header',
        '_name' => 'Header',
     ),
      'thesis_html_container_1348009571' => 
      array (
        'class' => 'columns',
        '_admin' => 
        array (
          'open' => true,
       ),
        '_id' => 'columns',
        '_name' => 'Columns',
     ),
      'thesis_html_container_1348009575' => 
      array (
        'class' => 'footer',
        '_id' => 'footer',
        '_name' => 'Footer',
     ),
      'thesis_html_container_1348010954' => 
      array (
        'class' => 'content',
        '_admin' => 
        array (
          'open' => true,
       ),
        '_id' => 'content',
        '_name' => 'Content Column',
     ),
      'thesis_html_container_1348010964' => 
      array (
        'class' => 'sidebar',
        '_id' => 'sidebar',
        '_name' => 'Sidebar',
     ),
      'thesis_html_container_1348093642' => 
      array (
        'class' => 'container',
        '_admin' => 
        array (
          'open' => true,
       ),
        '_id' => 'container',
        '_name' => 'Container',
     ),
      'thesis_html_container_1348165494' => 
      array (
        'class' => 'byline small',
        '_id' => 'byline',
        '_name' => 'Byline',
     ),
      'thesis_html_container_1348608649' => 
      array (
        'class' => 'archive_intro post_box grt top',
        '_id' => 'archive_intro',
        '_name' => 'Archive Intro',
     ),
      'thesis_html_container_1348701154' => 
      array (
        'class' => 'prev_next',
        '_id' => 'prev_next',
        '_name' => 'Prev/Next',
     ),
      'thesis_html_container_1348841704' => 
      array (
        'class' => 'comment_head',
        '_id' => 'comment_head',
        '_name' => 'Comment Head',
     ),
      'thesis_html_container_1348886177' => 
      array (
        'class' => 'headline_area',
        '_id' => 'headline_area',
        '_name' => 'Headline Area',
     ),
      'thesis_html_container_1365640887' => 
      array (
        'id' => 'comments',
        '_id' => 'post_comments',
        '_name' => 'Post Comments',
     ),
      'thesis_html_container_1365640949' => 
      array (
        'id' => 'comments',
        '_id' => 'page_comments',
        '_name' => 'Page Comments',
     ),
      'thesis_html_container_1366209424' => 
      array (
        'class' => 'comment_footer',
        '_id' => 'comment_footer',
        '_name' => 'Comment Footer',
     ),
   ),
    'thesis_wp_nav_menu' => 
    array (
      'thesis_wp_nav_menu_1348009742' => 
      array (
        'control' => 
        array (
          'yes' => true,
       ),
        '_name' => 'Nav Menu',
     ),
   ),
    'thesis_post_box' => 
    array (
      'thesis_post_box_1348010947' => 
      array (
        'html' => 'article',
        'class' => 'grt',
        'schema' => 'blogposting',
        '_admin' => 
        array (
          'open' => true,
       ),
        '_id' => 'post_box_archive',
        '_name' => 'Post Box (Archive)',
     ),
      'thesis_post_box_1348607689' => 
      array (
        'html' => 'article',
        'class' => 'grt',
        'schema' => 'blogposting',
        '_admin' => 
        array (
          'open' => true,
       ),
        '_id' => 'post_box_post_page',
        '_name' => 'Post Box (Post/Page)',
     ),
   ),
    'thesis_post_headline' => 
    array (
      'thesis_post_box_1348010947_thesis_post_headline' => 
      array (
        'html' => 'h2',
        'link' => 
        array (
          'on' => true,
       ),
        '_parent' => 'thesis_post_box_1348010947',
     ),
   ),
    'thesis_wp_widgets' => 
    array (
      'thesis_wp_widgets_1348079687' => 
      array (
        'div' => 'div',
        '_id' => 'sidebar',
        '_name' => 'Sidebar Widgets',
     ),
   ),
    'thesis_post_author' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_author' => 
      array (
        'intro' => 'by',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_post_date' => 
    array (
      'thesis_post_box_1348010947_thesis_post_date' => 
      array (
        'intro' => 'on',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_date' => 
      array (
        'intro' => 'on',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_comments' => 
    array (
      'thesis_comments_1348716667' => 
      array (
        '_id' => 'comments',
        '_name' => 'Comments',
     ),
   ),
    'thesis_comment_form' => 
    array (
      'thesis_comment_form_1348843091' => 
      array (
        '_id' => 'comment_form',
        '_name' => 'Comment Form',
     ),
   ),
    'thesis_post_categories' => 
    array (
      'thesis_post_box_1348010947_thesis_post_categories' => 
      array (
        'html' => 'div',
        'intro' => 'in',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_categories' => 
      array (
        'html' => 'div',
        'intro' => 'in',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_post_tags' => 
    array (
      'thesis_post_box_1348010947_thesis_post_tags' => 
      array (
        'intro' => 'Tagged as:',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_tags' => 
      array (
        'intro' => 'Tagged as:',
        'separator' => ',',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_previous_post_link' => 
    array (
      'thesis_previous_post_link' => 
      array (
        'html' => 'p',
        'intro' => 'Previous post:',
     ),
   ),
    'thesis_next_post_link' => 
    array (
      'thesis_next_post_link' => 
      array (
        'html' => 'p',
        'intro' => 'Next post:',
     ),
   ),
    'thesis_text_box' => 
    array (
      'thesis_text_box_1350230891' => 
      array (
        '_id' => 'sidebar',
        '_name' => 'Sidebar Text Box',
     ),
   ),
    'thesis_comment_text' => 
    array (
      'thesis_comments_1348716667_thesis_comment_text' => 
      array (
        'class' => 'grt',
        '_parent' => 'thesis_comments_1348716667',
     ),
   ),
    'thesis_comments_nav' => 
    array (
      'thesis_comments_nav_1366218280' => 
      array (
        'class' => 'comment_nav_bottom',
        '_name' => 'Comment Nav Bottom',
     ),
   ),
    'thesis_wp_featured_image' => 
    array (
      'thesis_post_box_1348607689_thesis_wp_featured_image' => 
      array (
        'link' => 
        array (
          'link' => false,
       ),
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
      'thesis_post_box_1348010947_thesis_wp_featured_image' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
   ),
    'thesis_post_thumbnail' => 
    array (
      'thesis_post_box_1348010947_thesis_post_thumbnail' => 
      array (
        'alignment' => 'left',
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_thumbnail' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_post_image' => 
    array (
      'thesis_post_box_1348607689_thesis_post_image' => 
      array (
        'link' => 
        array (
          'link' => false,
       ),
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
      'thesis_post_box_1348010947_thesis_post_image' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
   ),
    'thesis_post_author_avatar' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author_avatar' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_author_avatar' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_post_author_description' => 
    array (
      'thesis_post_box_1348010947_thesis_post_author_description' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_author_description' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_post_num_comments' => 
    array (
      'thesis_post_box_1348010947_thesis_post_num_comments' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348010947',
     ),
      'thesis_post_box_1348607689_thesis_post_num_comments' => 
      array (
        '_id' => 'loop',
        '_parent' => 'thesis_post_box_1348607689',
     ),
   ),
    'thesis_comment_avatar' => 
    array (
      'thesis_comments_1348716667_thesis_comment_avatar' => 
      array (
        '_id' => 'comments',
        '_parent' => 'thesis_comments_1348716667',
     ),
   ),
    'thesis_comment_date' => 
    array (
      'thesis_comments_1348716667_thesis_comment_date' => 
      array (
        '_id' => 'comments',
        '_parent' => 'thesis_comments_1348716667',
     ),
   ),
 ),
  'templates' => 
  array (
    'home' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
          1 => 'thesis_html_container_1348701154',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348010947',
       ),
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348010947_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348010947_thesis_post_thumbnail',
          3 => 'thesis_post_box_1348010947_thesis_post_content',
          4 => 'thesis_post_box_1348010947_thesis_post_tags',
          5 => 'thesis_post_box_1348010947_thesis_post_num_comments',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348010947_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_edit',
          3 => 'thesis_post_box_1348010947_thesis_post_categories',
       ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_posts_link',
          1 => 'thesis_previous_posts_link',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'archive' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_html_container_1348608649',
          1 => 'thesis_wp_loop',
          2 => 'thesis_html_container_1348701154',
       ),
        'thesis_html_container_1348608649' => 
        array (
          0 => 'thesis_archive_title',
          1 => 'thesis_archive_content',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348010947',
       ),
        'thesis_post_box_1348010947' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348010947_thesis_post_num_comments',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348010947_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348010947_thesis_post_author',
          1 => 'thesis_post_box_1348010947_thesis_post_date',
          2 => 'thesis_post_box_1348010947_thesis_post_edit',
       ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_posts_link',
          1 => 'thesis_previous_posts_link',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'custom_1348591137' => 
    array (
      'title' => 'Landing Page',
      'options' => 
      array (
        'thesis_html_body' => 
        array (
          'class' => 'landing',
       ),
     ),
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_html_container_1348009564',
          1 => 'thesis_html_container_1348010954',
          2 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_headline',
          1 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_edit',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
       ),
     ),
   ),
    'single' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
          1 => 'thesis_html_container_1348701154',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
          1 => 'thesis_html_container_1365640887',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
          4 => 'thesis_post_box_1348607689_thesis_post_tags',
          5 => 'thesis_post_box_1348607689_thesis_post_author_description',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
          3 => 'thesis_post_box_1348607689_thesis_post_categories',
       ),
        'thesis_html_container_1365640887' => 
        array (
          0 => 'thesis_comments_intro',
          1 => 'thesis_comments_1348716667',
          2 => 'thesis_comments_nav_1366218280',
          3 => 'thesis_comment_form_1348843091',
       ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_html_container_1348841704',
          1 => 'thesis_comments_1348716667_thesis_comment_text',
          2 => 'thesis_html_container_1366209424',
       ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_comments_1348716667_thesis_comment_author',
          2 => 'thesis_comments_1348716667_thesis_comment_date',
       ),
        'thesis_html_container_1366209424' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_reply',
          1 => 'thesis_comments_1348716667_thesis_comment_permalink',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
       ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
       ),
        'thesis_html_container_1348701154' => 
        array (
          0 => 'thesis_next_post_link',
          1 => 'thesis_previous_post_link',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'page' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
          1 => 'thesis_html_container_1365640949',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
       ),
        'thesis_html_container_1365640949' => 
        array (
          0 => 'thesis_comments_intro',
          2 => 'thesis_comments_1348716667',
          3 => 'thesis_comments_nav_1366218280',
          4 => 'thesis_comment_form_1348843091',
       ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_html_container_1348841704',
          1 => 'thesis_comments_1348716667_thesis_comment_text',
          2 => 'thesis_html_container_1366209424',
       ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_comments_1348716667_thesis_comment_author',
          2 => 'thesis_comments_1348716667_thesis_comment_date',
       ),
        'thesis_html_container_1366209424' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_reply',
          1 => 'thesis_comments_1348716667_thesis_comment_permalink',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
       ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'custom_1399394331' => 
    array (
      'title' => 'Full Page',
      'options' => 
      array (
        'thesis_html_body' => 
        array (
          'class' => 'full_page',
       ),
     ),
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
          1 => 'thesis_html_container_1365640949',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
       ),
        'thesis_html_container_1365640949' => 
        array (
          0 => 'thesis_comments_intro',
          2 => 'thesis_comments_1348716667',
          3 => 'thesis_comments_nav_1366218280',
          4 => 'thesis_comment_form_1348843091',
       ),
        'thesis_comments_1348716667' => 
        array (
          0 => 'thesis_html_container_1348841704',
          1 => 'thesis_comments_1348716667_thesis_comment_text',
          2 => 'thesis_html_container_1366209424',
       ),
        'thesis_html_container_1348841704' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_avatar',
          1 => 'thesis_comments_1348716667_thesis_comment_author',
          2 => 'thesis_comments_1348716667_thesis_comment_date',
       ),
        'thesis_html_container_1366209424' => 
        array (
          0 => 'thesis_comments_1348716667_thesis_comment_reply',
          1 => 'thesis_comments_1348716667_thesis_comment_permalink',
          2 => 'thesis_comments_1348716667_thesis_comment_edit',
       ),
        'thesis_comment_form_1348843091' => 
        array (
          0 => 'thesis_comment_form_1348843091_thesis_comment_form_cancel',
          1 => 'thesis_comment_form_1348843091_thesis_comment_form_title',
          2 => 'thesis_comment_form_1348843091_thesis_comment_form_name',
          3 => 'thesis_comment_form_1348843091_thesis_comment_form_email',
          4 => 'thesis_comment_form_1348843091_thesis_comment_form_url',
          5 => 'thesis_comment_form_1348843091_thesis_comment_form_comment',
          6 => 'thesis_comment_form_1348843091_thesis_comment_form_submit',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'front' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
    'fourohfour' => 
    array (
      'boxes' => 
      array (
        'thesis_html_body' => 
        array (
          0 => 'thesis_html_container_1348093642',
       ),
        'thesis_html_container_1348093642' => 
        array (
          0 => 'thesis_wp_nav_menu_1348009742',
          1 => 'thesis_html_container_1348009564',
          2 => 'thesis_html_container_1348009571',
          3 => 'thesis_html_container_1348009575',
       ),
        'thesis_html_container_1348009564' => 
        array (
          0 => 'thesis_site_title',
          1 => 'thesis_site_tagline',
       ),
        'thesis_html_container_1348009571' => 
        array (
          0 => 'thesis_html_container_1348010954',
          1 => 'thesis_html_container_1348010964',
       ),
        'thesis_html_container_1348010954' => 
        array (
          0 => 'thesis_wp_loop',
       ),
        'thesis_wp_loop' => 
        array (
          0 => 'thesis_post_box_1348607689',
       ),
        'thesis_post_box_1348607689' => 
        array (
          0 => 'thesis_html_container_1348886177',
          1 => 'thesis_post_box_1348607689_thesis_wp_featured_image',
          2 => 'thesis_post_box_1348607689_thesis_post_image',
          3 => 'thesis_post_box_1348607689_thesis_post_content',
       ),
        'thesis_html_container_1348886177' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author_avatar',
          1 => 'thesis_post_box_1348607689_thesis_post_headline',
          2 => 'thesis_html_container_1348165494',
       ),
        'thesis_html_container_1348165494' => 
        array (
          0 => 'thesis_post_box_1348607689_thesis_post_author',
          1 => 'thesis_post_box_1348607689_thesis_post_date',
          2 => 'thesis_post_box_1348607689_thesis_post_edit',
       ),
        'thesis_html_container_1348010964' => 
        array (
          0 => 'thesis_text_box_1350230891',
          1 => 'thesis_wp_widgets_1348079687',
       ),
        'thesis_html_container_1348009575' => 
        array (
          0 => 'thesis_attribution',
          1 => 'thesis_wp_admin',
       ),
     ),
   ),
 ),
  'css' => '// spatial variables to go with golden ratio typography
// (you can reference these in your Custom CSS, too!)
	$phi = 1.6180339887;

// primary spacing
	$x1 = $h5;
	$x02 = round($x1 / $phi);
	$x03 = round($x02 / $phi);
	$x04 = round($x03 / $phi);

// secondary (sidebar) spacing
	$x1s = $sh5;
	$x02s = round($x1s / $phi);
	$x03s = round($x02s / $phi);
	$x04s = round($x03s / $phi);

// backwards compatibility variables for customizations
	$f_aux = $f6;
	$f_subhead = $f3;
	$f_text = $f5;
	$h_aux = $h6;
	$h_text = $h5;
	$x_half = $x02;
	$x_single = $x1;
	$x_3over2 = $x1 + $x02;
	$x_double = 2 * $x1;
	$s_x_half = $x02s;
	$s_x_single = $x1s;
	$s_x_3over2 = $x1s + $x02s;
	$s_x_double = 2 * $x1s;

/*---:[ layout structure ]:---*/
body {
	font-family: $font;
	font-size: $f5;
	line-height: $h5;
	color: $text1;
	background-color: $color3;
	padding-top: $x1;
}
.container {
	width: $w_total;
	margin: 0 auto;
}
.columns {
	box-sizing: border-box;
	> .content {
		box-sizing: border-box;
		width: $w_content;
		$column1
		border-style: solid;
		border-color: $color1;
	}
	> .sidebar {
		box-sizing: border-box;
		$column2
		padding: $x1 $x1 0 $x1;
	}
}
/*---:[ links ]:---*/
a {
	color: $links;
	text-decoration: none;
	p & {
		text-decoration: underline;
		&:hover {
			text-decoration: none;
		}
	}
}
/*---:[ nav menu ]:---*/
$menu_phi = $menu_f * $phi;
$menu_x = round($menu_phi / $phi);
$menu_y = round($menu_x / $phi);
@mixin menu_links {
	display: block;
	$menu
	text-transform: uppercase;
	letter-spacing: 1px;
	color: $text1;
	background-color: $color2;
	padding: $menu_y $menu_x;
	border-width: 1px 1px 1px 0;
	border-style: solid;
	border-color: $color1;
	&:hover {
		background-color: $color1;
	}
}
.menu {
	position: relative;
	z-index: 50;
	list-style: none;
	border-width: 0 0 1px 1px;
	border-style: solid;
	border-color: $color1;
	a {
		@include menu_links;
	}
	li {
		position: relative;
		float: left;
		margin-bottom: -1px;
	}
	.sub-menu {
		display: none;
		position: absolute;
		z-index: 110;
		left: -1px;
		list-style: none;
		border-color: $color1;
		margin-top: -1px;
		.sub-menu {
			top: 0;
			left: $submenu;
			margin: 0 0 0 -1px;
		}
		li {
			width: $submenu;
			clear: both;
		}
		a {
			border-left-width: 1px;
		}
		.current-menu-item > a {
			border-bottom-color: $color1;
		}
	}
	li:hover > .sub-menu {
		display: block;
	}
	.current-menu-item > a {
		border-bottom-color: $color3;
		background-color: $color3;
		cursor: text;
	}
}
.menu_control {
	@include menu_links;
	display: none;
	background-color: $color3;
}
/*---:[ header ]:---*/
.header {
	border-bottom: 3px double $color1;
	padding: $x1;
}
#site_title {
	$title
	line-height: 1.31em;
	font-weight: bold;
	color: $title_color;
	a {
		color: $title_color;
		&:hover {
			color: $links;
		}
	}
	& + #site_tagline {
		margin-top: $x04;
	}
}
#site_tagline {
	$tagline
	line-height: 1.5em;
}
/*---:[ golden ratio typography with spaced paragraphs ]:---*/
.grt {
	font-size: $f5;
	line-height: $h5;
	h1, .headline {
		$headline
	}
	h1 {
		margin-bottom: $x1;
	}
	.headline {
		color: $headline_color;
		margin: 0;
		a {
			color: $headline_color;
			&:hover {
				color: $links;
			}
		}
	}
	h2, h3, h4 {
		$subhead
		color: $subhead_color;
	}
	h2 {
		font-size: $f3;
		line-height: $h3;
		margin-top: ($x1 + $x02);
		margin-bottom: $x02;
	}
	h3 {
		font-size: $f4;
		line-height: $h4;
		margin-top: ($x1 + $x03);
		margin-bottom: $x03;
	}
	h4 {
		font-size: $f5;
		line-height: $h5;
		font-weight: bold;
		margin-bottom: $x04;
	}
	h1 + h2, h2 + h3 {
		margin-top: 0
	}
	.post_content {
		h2, h3 {
			&:first-child {
				margin-top: 0;
			}
		}
	}
	ul {
		list-style-type: square;
		li a {
			text-decoration: underline;
			&:hover {
				text-decoration: none;
			}
		}
	}
	blockquote {
		$blockquote
		padding-left: $x02;
		border-left: 1px solid $color1;
		&.right, &.left {
			width: 45%;
			$pullquote
			padding-left: 0;
			border: 0;
			margin-bottom: $x02;
		}
	}
	code, pre, kbd {
		font-size: $f5 - 2;
	}
	code {
		$code
		background-color: rgba(0,0,0,0.08);
		padding: round($x04 / 2) ($x04 - 2);
		border-radius: $x04;
		margin: 0 1px;
	}
	pre {
		$pre
		background-color: $color2;
		padding: $x02 $x02 $x02 $x03;
		border-left: $x04 solid rgba(0,0,0,0.15);
	}
	kbd {
		font-family: Consolas, Menlo, Monaco, Courier, Verdana, sans-serif;
		color: #111;
		background-color: #fff;
		padding: round($x04 / 2) ($x04 - 2);
		border-radius: $x04;
		box-shadow: 0 0 $x04 0 rgba(0,0,0,0.45);
		margin: 0 1px;
	}
	.alert, .note, .box {
		padding: $x02;
	}
	.alert {
		background-color: #ff9;
		border: 1px solid #e6e68a;
	}
	.note {
		background-color: $color2;
		border: 1px solid $color1;
	}
	$_links = $links;
	.box {
		background-color: scale-color($_links, $lightness: 90%);
		border: 1px solid scale-color($_links, $lightness: -10%);
	}
	.footnotes {
		font-size: $f6;
		line-height: $h6;
		padding-top: $x1;
		border-top: 1px dotted $color1;
	}
	.footnotes, sub, sup, .post_cats, .post_tags {
		color: $text2;
	}
	fieldset {
		margin-bottom: $x1;
		legend {
			font-size: $f4;
			line-height: $h4;
			font-weight: bold;
			margin-bottom: $x04;
		}
	}
	.avatar {
		$avatar
		float: right;
		clear: both;
		margin-left: $x02;
	}
	.small, .caption {
		font-size: $f6;
		line-height: $h6;
	}
	.caption {
		margin-top: -$x02;
		color: $text2;
	}
	.frame, .post_image_box, .wp-caption {
		box-sizing: border-box;
		background-color: $color2;
		padding: $x02;
		border: 1px solid $color1;
	}
	.wp-caption p {
		font-size: $f6;
		line-height: $h6;
	}
	.wp-caption img, .post_image_box .post_image, .thumb, .footnotes p {
		margin-bottom: $x02;
	}
	.drop_cap {
		font-size: (2 * $x1);
		line-height: 1em;
		margin-right: $x03;
		float: left;
	}
	.author_description {
		padding-top: $x1;
		border-top: 1px dotted $color1;
		.avatar {
			$bio_avatar
			float: left;
			margin-right: $x02;
			margin-left: 0;
		}
	}
	.author_description_intro {
		font-weight: bold;
	}
	p, ul, ol, blockquote, pre, dl, dd, .center, .aligncenter, .block,  .alignnone, .post_image, .post_image_box, .wp-post-image, .caption, .wp-caption, .alert, .note, .box, .footnotes, .headline_area {
		margin-bottom: $x1;
	}
	.right, .alignright, .ad {
		margin-bottom: $x1;
		margin-left: $x1;
	}
	.left, .alignleft, .ad_left {
		margin-bottom: $x1;
		margin-right: $x1;
	}
	ul, ol, .stack {
		margin-left: $x1;
	}
	ul ul, ul ol, ol ul, ol ol, .wp-caption p, blockquote.right p, blockquote.left p {
		margin-bottom: 0;
	}
	.alert, .note, .box, .right, .left .footnotes {
		:last-child {
			margin-bottom: 0;
		}
	}
}
/*---:[ other content styles ]:---*/
.post_box {
	padding: $x1 $x1 0 $x1;
	border-top: 1px dotted $color1;
	&.top {
		border-top: 0;
	}
}
.byline {
	color: $text2;
	a {
		color: $text2;
		border-bottom: 1px solid $color1;
		&:hover {
			color: $text1;
		}
	}
	a, .post_author, .post_date {
		text-transform: uppercase;
		letter-spacing: 1px;
	}
	.post_author_intro, .post_date_intro, .post_cats_intro {
		font-style: italic;
	}
	.post_edit {
		margin-left: $x03;
		&:first-child {
			margin-left: 0;
		}
	}
}
.wp-caption {
	&.aligncenter img {
		margin-right: auto;
		margin-left: auto;
	}
	.wp-caption-text .wp-smiley {
		display: inline;
		margin-bottom: 0;
	}
}
.num_comments_link {
	display: inline-block;
	color: $text2;
	text-decoration: none;
	margin-bottom: $x1;
	$_color1 = $color1;
	$_c1l = lightness($_color1);
	&:hover {
		background-color: change-color($_color1, $lightness: $_c1l + ((100 - $_c1l) / 2));
	}
}
.num_comments {
	font-size: $x1;
	color: $text1;
}
.bracket {
	font-size: $x1;
	color: $color1;
}
.archive_intro {
	border-width: 0 0 1px 0;
	border-style: solid;
	border-color: $color1;
	.headline {
		margin-bottom: $x1;
	}
}
.prev_next {
	clear: both;
	color: $text2;
	border-top: 1px solid $color1;
	padding: $x02 $x1;
	.next_posts {
		float: right;
	}
}
.previous_posts, .next_posts {
	display: block;
	font-size: $f6;
	line-height: $h6;
	text-transform: uppercase;
	letter-spacing: 2px;
	a:hover {
		text-decoration: underline;
	}
}
/*---:[ comments ]:---*/
#comments {
	margin-top: (2 * $x1);
}
.comments_intro {
	color: $text2;
	padding: 0 $x1;
	margin-bottom: $x02;
	a:hover {
		text-decoration: underline;
	}
}
.comments_closed {
	font-size: $f6;
	line-height: $h6;
	color: $text2;
	margin: 0 $x1 $x1 $x1;
}
.comment_list {
	list-style-type: none;
	border-top: 1px dotted $color1;
	margin-bottom: (2 * $x1);
}
.comment {
	padding: $x1;
	border-bottom: 1px dotted $color1;
	.comment_head {
		margin-bottom: $x02;
	}
	.comment_author {
		font-weight: bold;
	}
	.avatar {
		float: right;
		$comment_avatar
		margin-left: $x02;
	}
	.comment_date {
		display: block;
		font-size: $f6;
		line-height: $h6;
		color: $text2;
		a {
			color: $text2;
		}
	}
	.comment_text > :last-child {
		margin-bottom: 0;
	}
	.comment_footer {
		margin-top: $x02;
		a {
			font-size: $f6;
			line-height: $h6;
			color: $text2;
			text-transform: uppercase;
			letter-spacing: 1px;
			margin-right: $x02;
		}
	}
	.comment_footer a {
		font-size: $f6;
		line-height: $h6;
		color: $text2;
		text-transform: uppercase;
		letter-spacing: 1px;
		margin-right: $x02;
	}
}
.children {
	.comment {
		list-style-type: none;
		padding: 0 0 0 $x1;
		border-bottom: 0;
		border-left: 1px solid $color1;
		margin-top: $x1;
	}
	.bypostauthor {
		background-color: transparent;
		border-color: $links;
	}
}
.comment_head, .comment_footer, comment_nav {
	a:hover {
		text-decoration: underline;
	}
}
.comment_nav {
	font-size: $f6;
	line-height: $h6;
	text-transform: uppercase;
	letter-spacing: 1px;
	padding: $x02 $x1;
	border-style: dotted;
	border-color: $color1;
	a:hover {
		text-decoration: underline;
	}
}
.comment_nav_top { // this is a legacy declaration
	border-width: 1px 0 0 0;
}
.comment_nav_bottom {
	border-width: 0 0 1px 0;
	margin: -(2 * $x1) 0 (2 * $x1) 0;
}
.next_comments {
	float: right;
}
.comment_moderated {
	font-weight: bold;
}
/*---:[ inputs ]:---*/
@mixin inputs {
	font-family: inherit;
	font-size: inherit;
	line-height: 1em;
	font-weight: inherit;
	color: $text1;
	background-color: $color2;
	padding: $x04;
	border: 1px solid $color1;
	box-sizing: border-box;
}
input {
	&[type="text"], &[type="number"], &[type="url"], &[type="tel"], &[type="email"], &[type="password"] {
		@include inputs;
		&:focus {
			background-color: $color3;
			border-color: $color2;
		}
	}
}
select, textarea {
	@include inputs;
	line-height: inherit;
	&:focus {
		background-color: $color3;
		border-color: $color2;
	}
}
/*---:[ buttons ]:---*/
@mixin buttons {
	font-family: inherit;
	font-size: inherit;
	line-height: 1em;
	font-weight: bold;
	background-color: $color3;
	padding: $x03;
	border: 3px double $color1;
	&:hover, &:active {
		background-color: $color2;
		transition: background-color 0.3s;
	}
}
button, input[type="submit"] {
	@include buttons;
}
/*---:[ comment form ]:---*/
#commentform {
	padding: 0 $x1;
	margin: (2 * $x1) 0;
	.comment & {
		padding-right: 0;
		padding-left: 0;
		margin-top: $x02;
	}
	.comment_form_title {
		font-size: $f4;
		line-height: $h4;
		color: $subhead_color;
		padding: 0 $x1 $x02 $x1;
		border-bottom: 1px dotted $color1;
		margin-right: -$x1;
		margin-left: -$x1;
	}
	p {
		margin-bottom: $x02;
		.required {
			color: #d00;
		}
	}
	label {
		display: block;
	}
	input[type="checkbox"] + label {
		display: inline;
		margin-left: $x04;
	}
	#wp-comment-cookies-consent + label {
		font-size: $f6;
		line-height: $h6;
		color: $text2;
	}
	input[type="text"] {
		width: 50%;
	}
	input[type="submit"] {
		font-size: $f4;
	}
	textarea {
		display: block;
		width: 100%;
	}
}
#cancel-comment-reply-link {
	float: right;
	font-size: $f6;
	line-height: inherit;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: $links;
	margin-top: $x04;
	&:hover {
		text-decoration: underline;
	}
}
.login_alert {
	font-weight: bold;
	background-color: $color2;
	border: 1px solid $color1;
}
/*---:[ sidebar ]:---*/
.sidebar {
	$sidebar
	.widget_title, .sidebar_heading, .headline {
		$sidebar_heading
		margin-bottom: $x02s;
	}
	.widget_title, .sidebar_heading {
		font-variant: small-caps;
		letter-spacing: 1px;
	}
	p, ul, ol, blockquote, pre, dl, dd, .left, .alignleft, .ad_left, .right, .alignright, .ad, .center, .aligncenter, .block, .alignnone {
		margin-bottom: $x1s;
	}
	.left, .alignleft, .ad_left {
		margin-right: $x1s;
	}
	ul ul, ul ol, ol ul, ol ol, .right, .alignright, .ad, .stack {
		margin-left: $x1s;
	}
	ul ul, ul ol, ol ul, ol ol, .wp-caption p, .post_excerpt p {
		margin-bottom: 0;
	}
	.text_box, .thesis_email_form, .query_box {
		margin-bottom: (2 * $x1s);
	}
	.search-form, .thesis_email_form {
		input[type="text"] {
			width: 100%;
			margin-bottom: $x02s;
		}
	}
	button, input[type="submit"] {
		padding: $x03s;
	}
	.query_box .post_author, .query_box .post_date {
		color: $text2;
	}
	.widget {
		margin-bottom: (2 * $x1s);
		ul {
			list-style-type: none;
			li {
				margin-bottom: $x02s;
				ul, ol {
					margin-top: $x02s;
				}
				a:hover {
					text-decoration: underline;
				}
			}
		}
	}
}
/*---:[ footer ]:---*/
.footer {
	font-size: $f6;
	line-height: $h6;
	color: $text2;
	text-align: right;
	padding: $x02 $x1;
	border-top: 3px double $color1;
	a {
		color: $text2;
		&:hover {
			color: $text1;
		}
	}
}
/*---:[ custom template styles ]:---*/
.landing {
	body& {
		padding-top: 0;
	}
	.container {
		width: $w_content;
	}
	.header, .headline_area, .footer {
		text-align: center;
	}
}
.full_page .columns > .content {
	width: $w_total;
	float: none;
	border-right: 0;
}
@if ($woocommerce) {
	/*---:[ woocommerce compatibility ]:---*/
	.woocommerce {
		&.woocommerce-shop .content {
			padding: $x1;
		}
		&.woocommerce-archive {
			.content {
				padding: 0 $x1;
			}
			.post_box {
				margin-top: 0;
				margin-right: -$x1;
				margin-left: -$x1;
				margin-bottom: $x1;
			}
			.term-description {
				margin-bottom: $x1;
			}
		}
		.content > .product {
			margin: $x1 $x1 0 $x1;
		}
		.page-title {
			$headline
			padding: 0 $x1 $x1;
			border-bottom: 1px solid $color1;
			margin-right: -$x1;
			margin-left: -$x1;
			margin-bottom: $x1;
		}
		.product_title {
			$headline
			margin-bottom: $x02;
		}
		.content .woocommerce-result-count {
			font-size: $f6;
			line-height: $h6;
			color: $text2;
			margin-bottom: $x02;
		}
		nav.woocommerce-pagination ul.page-numbers {
			margin: -$x04 0 $x02 0;
		}
		ul.products li.product.grt {
			.woocommerce-loop-product__title {
				line-height: $h5;
				padding: 0;
				margin-bottom: $x03;
			}
			.star-rating {
				font-size: $f6;
				margin-bottom: $x03;
			}
			.price {
				font-size: $f5;
				margin-bottom: 0;
			}
			.button {
				margin-top: $x02;
			}
		}
		div.product.grt {
			.woocommerce-product-rating {
				margin-bottom: $x02;
			}
			form.cart {
				margin-bottom: $x1;
				.button {
					background-color: $links;
					margin-top: -($x03 - $x04);
				}
			}
			.product_meta {
				font-size: $f6;
				line-height: $h6;
				color: $text2;
				a:hover {
					text-decoration: underline;
				}
			}
			#reviews {
				h2 {
					margin-top: 0;
				}
				#comments {
					margin-top: $x1;
				}
				.comment-reply-title {
					font-size: $f4;
					line-height: $h4;
					font-weight: bold;
				}
				#commentform {
					padding: 0;
					margin-top: $x1;
				}
				#comment {
					height: auto;
				}
			}
		}
		.panel > h2:first-child {
			margin-top: 0;
		}
		.grt {
			.button, #respond input#submit {
				@include buttons;
			}
		}
		.grt & {
			fieldset {
				margin-top: $x1;
				margin-bottom: $x03;
			}
			.button {
				@include buttons;
				&.alt {
					font-size: $f4;
					background-color: $links;
					padding: $x1;
				}
			}
		}
		.woocommerce-info, .woocommerce-message {
			.button {
				margin-left: $x03;
			}
		}
		.woocommerce-cart & {
			#coupon_code {
				padding: $x03;
				margin-right: $x03;
			}
		}
		&.woocommerce-page a.button {
			@include buttons;
		}
	}
}
/*---:[ clearfix ]:---*/
.columns, .menu, .post_box, .post_content, .author_description, .sidebar, .query_box, .prev_next, .comment_text, .comment_nav {
	&:after {
		$z_clearfix
	}
}
/*---:[ media queries ]:---*/
$_b1 = $w_total - 1;
@media all and (max-width: $_b1) {
	body {
		padding-top: 0;
	}
	.container, .landing .container {
		width: auto;
		max-width: $w_content;
	}
	.header {
		border-top: 1px solid $color1;
		.landing & {
			border-top: 0;
		}
	}
	.columns {
		> .content {
			float: none;
			width: 100%;
			border: 0;
			.full_page & {
				width: 100%;
			}
		}
		> .sidebar {
			float: none;
			width: 100%;
			border-top: 3px double $color1;
		}
	}
	.menu_control {
		display: block;
		width: 100%;
		background-color: $color3;
		padding: 1em $x1;
		border-width: 0;
		cursor: pointer;
		box-sizing: border-box;
	}
	.menu {
		display: none;
		clear: both;
		width: 100%;
		border-width: 1px 0 0 0;
		.sub-menu {
			position: static;
			display: block;
			padding-left: $x1;
			border-top: 1px solid $color1;
			margin: 0;
			li {
				width: 100%;
			}
		}
		li {
			float: none;
			width: 100%;
			margin-bottom: 0;
			&:first-child > a:first-child {
				border-top-width: 0;
			}
		}
		a {
			background-color: $color3;
			padding: 1em $x1;
			border-width: 1px 1px 0 0;
		}
		.current-menu-item > a {
			background-color: $color2;
		}
		> li > a {
			border-left-width: 1px;
		}
	}
	.show_menu {
		display: block;
	}
	.sidebar {
		.search-form, .thesis_email_form {
			input[type="text"] {
				width: 50%;
			}
		}
	}
}
$_b2 = $w_content - 1;
@media all and (max-width: $_b2) {
	.menu {
		a {
			border-right-width: 0;
		}
		> li > a {
			border-left-width: 0;
		}
	}
}
$_b3 = 450 + (2 * ($x1 - $x02)) - 1;
@media all and (max-width: $_b3) {
	.menu a, .menu_control {
		padding: 1em $x02;
	}
	.header, .columns > .sidebar, .post_box, .prev_next, .comments_intro, .comment, .comment_nav, #commentform, #commentform .comment_form_title, .footer {
		padding-right: $x02;
		padding-left: $x02;
	}
	.menu .sub-menu, .children .comment {
		padding-left: $x02;
	}
	.right, .alignright, img[align="right"], .left, .alignleft, img[align="left"], .ad, .ad_left {
		float: none;
	}
	.grt {
		.right, .alignright, .left, .alignleft {
			margin-right: 0;
			margin-left: 0;
		}
		blockquote {
			&.right, &.left {
				width: 100%;
				margin-bottom: $x1;
			}
		}
	}
	.post_author:after {
		display: block;
		height: 0;
		content: \'\\a\';
		white-space: pre;
	}
	#commentform, .sidebar .search-form, .sidebar .thesis_email_form {
		input[type="text"] {
			width: 100%;
		}
	}
	.comments_closed, .login_alert {
		margin-right: $x02;
		margin-left: $x02;
	}
	#commentform .comment_form_title {
		margin-left: -$x02;
		margin-right: -$x02;
	}
	.comment_date {
		display: none;
	}
}',
  'css_editor' => '// spatial variables
	$phi = 1.6180339887;

// primary spacing
	$x1 = $h5;
	$x02 = round($x1 / $phi);
	$x03 = round($x02 / $phi);
	$x04 = round($x03 / $phi);

/*---:[ golden ratio typography ]:---*/
.grt {
	width: $w_content - (2 * $x1) - 1;
	font-family: $font;
	font-size: $f5;
	line-height: $h5;
	margin: 0 auto;
	-webkit-font-smoothing: subpixel-antialiased !important;
	> :first-child {
		margin-top: $x02;
	}
	a {
		color: $links;
		text-decoration: underline;
		&:hover {
			text-decoration: none;
		}
	}
	h1, .headline {
		$headline
	}
	h1 {
		margin-bottom: $x1;
	}
	.headline {
		color: $headline_color;
		margin: 0;
		a {
			color: $headline_color;
			&:hover {
				color: $links;
			}
		}
		& + .byline {
			margin-top: $x04;
		}
	}
	h2, h3, h4 {
		$subhead
		color: $subhead_color;
	}
	h2 {
		font-size: $f3;
		line-height: $h3;
		margin-top: ($x1 + $x02);
		margin-bottom: $x02;
	}
	h3 {
		font-size: $f4;
		line-height: $h4;
		margin-top: ($x1 + $x03);
		margin-bottom: $x03;
	}
	h4 {
		font-size: $f5;
		line-height: $h5;
		font-weight: bold;
		margin-bottom: $x04;
	}
	h1 + h2, h2 + h3 {
		margin-top: 0
	}
	.post_content {
		h2, h3 {
			&:first-child {
				margin-top: 0;
			}
		}
	}
	ul {
		list-style-type: square;
		li a {
			text-decoration: underline;
			&:hover {
				text-decoration: none;
			}
		}
	}
	blockquote {
		$blockquote
		padding-left: $x02;
		border-left: 1px solid $color1;
		&.right, &.left {
			width: 45%;
			$pullquote
			padding-left: 0;
			border: 0;
			margin-bottom: $x02;
		}
	}
	code, pre, kbd {
		font-size: $f5 - 2;
	}
	code {
		$code
		background-color: rgba(0,0,0,0.08);
		padding: round($x04 / 2) ($x04 - 2);
		border-radius: $x04;
		margin: 0 1px;
	}
	pre {
		$pre
		background-color: $color2;
		padding: $x02 $x02 $x02 $x03;
		border-left: $x04 solid rgba(0,0,0,0.15);
	}
	kbd {
		font-family: Consolas, Menlo, Monaco, Courier, Verdana, sans-serif;
		color: #111;
		background-color: #fff;
		padding: round($x04 / 2) ($x04 - 2);
		border-radius: $x04;
		box-shadow: 0 0 $x04 0 rgba(0,0,0,0.45);
		margin: 0 1px;
	}
	.alert, .note, .box {
		padding: $x02;
	}
	.alert {
		background-color: #ff9;
		border: 1px solid #e6e68a;
	}
	.note {
		background-color: $color2;
		border: 1px solid $color1;
	}
	$_links = $links;
	.box {
		background-color: scale-color($_links, $lightness: 90%);
		border: 1px solid scale-color($_links, $lightness: -10%);
	}
	.footnotes {
		font-size: $f6;
		line-height: $h6;
		padding-top: $x1;
		border-top: 1px dotted $color1;
	}
	.footnotes, sub, sup, .post_cats, .post_tags {
		color: $text2;
	}
	fieldset {
		margin-top: $x1;
		margin-bottom: $x1;
		legend {
			font-weight: bold;
			margin-bottom: $x02;
		}
	}
	.avatar {
		$avatar
		float: right;
		clear: both;
		margin-left: $x02;
	}
	.small, .caption {
		font-size: $f6;
		line-height: $h6;
	}
	.caption {
		margin-top: -$x02;
		color: $text2;
	}
	.frame, .post_image_box, .wp-caption {
		box-sizing: border-box;
		background-color: $color2;
		padding: $x02;
		border: 1px solid $color1;
	}
	.wp-caption p {
		font-size: $f6;
		line-height: $h6;
	}
	.wp-caption img, .post_image_box .post_image, .thumb, .footnotes p {
		margin-bottom: $x02;
	}
	.drop_cap {
		font-size: (2 * $x1);
		line-height: 1em;
		margin-right: $x03;
		float: left;
	}
	.author_description {
		padding-top: $x1;
		border-top: 1px dotted $color1;
		.avatar {
			$bio_avatar
			float: left;
			margin-right: $x02;
			margin-left: 0;
		}
	}
	.author_description_intro {
		font-weight: bold;
	}
	p, ul, ol, blockquote, pre, dl, dd, .center, .aligncenter, .block,  .alignnone, .post_image, .post_image_box, .wp-post-image, .caption, .wp-caption, .alert, .note, .box, .footnotes, .headline_area {
		margin-bottom: $x1;
	}
	.right, .alignright, .ad {
		margin-bottom: $x1;
		margin-left: $x1;
	}
	.left, .alignleft, .ad_left {
		margin-bottom: $x1;
		margin-right: $x1;
	}
	ul, ol, .stack {
		margin-left: $x1;
	}
	ul ul, ul ol, ol ul, ol ol, .wp-caption p, blockquote.right p, blockquote.left p {
		margin-bottom: 0;
	}
	.alert, .note, .box, .right, .left .footnotes {
		:last-child {
			margin-bottom: 0;
		}
	}
}
.wp-caption {
	&.aligncenter img {
		margin-right: auto;
		margin-left: auto;
	}
	.wp-caption-text .wp-smiley {
		display: inline;
		margin-bottom: 0;
	}
}',
  'vars' => 
  array (
    'var_1349039761' => 
    array (
      'name' => 'Links',
      'ref' => 'links',
      'css' => '#DD0000',
   ),
    'var_1351010515' => 
    array (
      'name' => 'Clearfix',
      'ref' => 'z_clearfix',
      'css' => 'display: table;
	clear: both;
	content: \'\';',
   ),
    'var_1360768628' => 
    array (
      'name' => 'Primary Text Color',
      'ref' => 'text1',
      'css' => '#111111',
   ),
    'var_1360768650' => 
    array (
      'name' => 'Secondary Text Color',
      'ref' => 'text2',
      'css' => '#888888',
   ),
    'var_1360768659' => 
    array (
      'name' => 'Color 1',
      'ref' => 'color1',
      'css' => '#DDDDDD',
   ),
    'var_1360768669' => 
    array (
      'name' => 'Color 2',
      'ref' => 'color2',
      'css' => '#EEEEEE',
   ),
    'var_1360768678' => 
    array (
      'name' => 'Color 3',
      'ref' => 'color3',
      'css' => '#FFFFFF',
   ),
    'var_1362696253' => 
    array (
      'name' => 'Width: Content',
      'ref' => 'w_content',
      'css' => '617px',
   ),
    'var_1362696268' => 
    array (
      'name' => 'Width: Sidebar',
      'ref' => 'w_sidebar',
      'css' => '280px',
   ),
    'var_1362697011' => 
    array (
      'name' => 'Width: Total',
      'ref' => 'w_total',
      'css' => '897px',
   ),
    'var_1362757553' => 
    array (
      'name' => 'Font: Primary',
      'ref' => 'font',
      'css' => 'Georgia, "Times New Roman", Times, serif',
   ),
    'var_1363019458' => 
    array (
      'name' => 'Site Title Color',
      'ref' => 'title_color',
      'css' => '#111111',
   ),
    'var_1363458877' => 
    array (
      'name' => 'Site Title',
      'ref' => 'title',
      'css' => 'font-size: 42px;',
   ),
    'var_1363459110' => 
    array (
      'name' => 'Tagline',
      'ref' => 'tagline',
      'css' => 'font-size: 16px;
	color: #888888;',
   ),
    'var_1363467168' => 
    array (
      'name' => 'Nav Menu',
      'ref' => 'menu',
      'css' => 'font-size: 13px;
	line-height: 19px;',
   ),
    'var_1363467273' => 
    array (
      'name' => 'Sub-headline',
      'ref' => 'subhead',
      'css' => '',
   ),
    'var_1363467831' => 
    array (
      'name' => 'Headline',
      'ref' => 'headline',
      'css' => 'font-size: 33px;
	line-height: 49px;',
   ),
    'var_1363537291' => 
    array (
      'name' => 'Sidebar',
      'ref' => 'sidebar',
      'css' => 'font-size: 13px;
	line-height: 19px;',
   ),
    'var_1363621601' => 
    array (
      'name' => 'Blockquote',
      'ref' => 'blockquote',
      'css' => 'color: #888888;',
   ),
    'var_1363621659' => 
    array (
      'name' => 'Code',
      'ref' => 'code',
      'css' => 'font-family: Consolas, Menlo, Monaco, Courier, Verdana, sans-serif;',
   ),
    'var_1363621686' => 
    array (
      'name' => 'Pre-formatted Code',
      'ref' => 'pre',
      'css' => 'font-family: Consolas, Menlo, Monaco, Courier, Verdana, sans-serif;',
   ),
    'var_1363621701' => 
    array (
      'name' => 'Sidebar Heading',
      'ref' => 'sidebar_heading',
      'css' => 'font-size: 21px;
	line-height: 30px;',
   ),
    'var_1363633021' => 
    array (
      'name' => 'Headline Color',
      'ref' => 'headline_color',
      'css' => '#111111',
   ),
    'var_1363633037' => 
    array (
      'name' => 'Sub-headline Color',
      'ref' => 'subhead_color',
      'css' => '#111111',
   ),
    'var_1363989059' => 
    array (
      'name' => 'Author Avatar',
      'ref' => 'avatar',
      'css' => 'width: 71px;
	height: 71px;',
   ),
    'var_1364573035' => 
    array (
      'name' => 'Comment Avatar',
      'ref' => 'comment_avatar',
      'css' => 'width: 48px;
	height: 48px;',
   ),
    'var_1364921879' => 
    array (
      'name' => 'Author Description Avatar',
      'ref' => 'bio_avatar',
      'css' => 'width: 78px;
	height: 78px;',
   ),
    'var_1364931901' => 
    array (
      'name' => 'Pullquote',
      'ref' => 'pullquote',
      'css' => 'font-size: 26px;
	line-height: 37px;',
   ),
    'var_1366555361' => 
    array (
      'name' => 'Navigation Submenu',
      'ref' => 'submenu',
      'css' => '166px',
   ),
    'var_1367605257' => 
    array (
      'name' => 'Content Column',
      'ref' => 'column1',
      'css' => 'float: left;
	border-width: 0 1px 0 0;',
   ),
    'var_1367605279' => 
    array (
      'name' => 'Sidebar Column',
      'ref' => 'column2',
      'css' => 'width: $w_sidebar;
	float: right;',
   ),
    'var_1515536162' => 
    array (
      'name' => 'Font Size 1',
      'ref' => 'f1',
      'css' => '42px',
   ),
    'var_1515536178' => 
    array (
      'name' => 'Font Size 2',
      'ref' => 'f2',
      'css' => '33px',
   ),
    'var_1515536186' => 
    array (
      'name' => 'Font Size 3',
      'ref' => 'f3',
      'css' => '26px',
   ),
    'var_1515536193' => 
    array (
      'name' => 'Font Size 4',
      'ref' => 'f4',
      'css' => '20px',
   ),
    'var_1515536200' => 
    array (
      'name' => 'Font Size 5',
      'ref' => 'f5',
      'css' => '16px',
   ),
    'var_1515536208' => 
    array (
      'name' => 'Font Size 6',
      'ref' => 'f6',
      'css' => '13px',
   ),
    'var_1515536227' => 
    array (
      'name' => 'Line Height 1',
      'ref' => 'h1',
      'css' => '60px',
   ),
    'var_1515536236' => 
    array (
      'name' => 'Line Height 2',
      'ref' => 'h2',
      'css' => '49px',
   ),
    'var_1515536242' => 
    array (
      'name' => 'Line Height 3',
      'ref' => 'h3',
      'css' => '39px',
   ),
    'var_1515536248' => 
    array (
      'name' => 'Line Height 4',
      'ref' => 'h4',
      'css' => '32px',
   ),
    'var_1515536253' => 
    array (
      'name' => 'Line Height 5',
      'ref' => 'h5',
      'css' => '26px',
   ),
    'var_1515536258' => 
    array (
      'name' => 'Line Height 6',
      'ref' => 'h6',
      'css' => '22px',
   ),
    'var_1515623468' => 
    array (
      'name' => 'Sidebar Font Size',
      'ref' => 'sf5',
      'css' => '13px',
   ),
    'var_1515623478' => 
    array (
      'name' => 'Sidebar Line Height',
      'ref' => 'sh5',
      'css' => '19px',
   ),
    'var_1515789594' => 
    array (
      'name' => 'Nav Menu Font Size',
      'ref' => 'menu_f',
      'css' => '13px',
   ),
    'var_1515953299' => 
    array (
      'name' => 'Use WooCommerce styles?',
      'ref' => 'woocommerce',
      'css' => true,
   ),
 ),
);
}
