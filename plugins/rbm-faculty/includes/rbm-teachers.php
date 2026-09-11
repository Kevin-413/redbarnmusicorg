<?php
/**
 * Central Teacher data structure (Phase 1: data model + admin UI only).
 * No front-end output. See docs/0907-2144-Copilot-REQUEST-Implement-Teacher-CPT-And-Instrument-Taxonomy-Phase-1.txt
 */

add_action('init', 'rbm_register_teacher_cpt');
function rbm_register_teacher_cpt() {
    register_post_type('msch_teacher', [
        'labels' => [
            'name'               => 'Teachers',
            'singular_name'      => 'Teacher',
            'add_new_item'       => 'Add Teacher',
            'edit_item'          => 'Edit Teacher',
            'new_item'           => 'Add Teacher',
            'all_items'          => 'All Teachers',
            'view_item'          => 'View Teacher',
            'search_items'       => 'Search Teachers',
            'not_found'          => 'No teachers found',
            'not_found_in_trash' => 'No teachers found in Trash',
            'menu_name'          => 'Faculty',
        ],
        'public'              => true,
        'has_archive'         => false,
        'publicly_queryable'  => true,
        'exclude_from_search' => true,
        'show_in_nav_menus'   => false,
        'rewrite'             => ['slug' => 'teacher', 'with_front' => false],
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-groups',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    ]);
}

add_action('init', 'rbm_register_instrument_taxonomy');
function rbm_register_instrument_taxonomy() {
    register_taxonomy('msch_instrument', ['msch_teacher'], [
        'labels' => [
            'name'          => 'Instruments',
            'singular_name' => 'Instrument',
            'search_items'  => 'Search Instruments',
            'all_items'     => 'All Instruments',
            'edit_item'     => 'Edit Instrument',
            'add_new_item'  => 'Add Instrument',
            'menu_name'     => 'Instruments',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ]);
}

// Seed initial instrument terms once, only if the taxonomy is empty.
add_action('init', 'rbm_seed_instrument_terms', 20);
function rbm_seed_instrument_terms() {
    if (get_option('rbm_msch_instrument_seeded')) {
        return;
    }
    $seed = ['Piano', 'Guitar', 'Voice', 'Drums', 'Woodwinds', 'Strings', 'Brass', 'Composition'];
    foreach ($seed as $term) {
        if (!term_exists($term, 'msch_instrument')) {
            wp_insert_term($term, 'msch_instrument');
        }
    }
    update_option('rbm_msch_instrument_seeded', 1);
}

// One-time flush so the new `teacher` rewrite base takes effect; never runs on every request.
add_action('init', 'rbm_msch_teacher_maybe_flush_rewrite_rules', 20);
function rbm_msch_teacher_maybe_flush_rewrite_rules() {
    if (get_option('rbm_msch_teacher_rewrite_flushed_v1')) {
        return;
    }
    flush_rewrite_rules();
    update_option('rbm_msch_teacher_rewrite_flushed_v1', 1);
}

// --- Public single-Teacher profile page (canonical /teacher/<slug>/) ---
// Reuses Avada's own single.php (its fallback template for post types with no dedicated template)
// by filtering the_content(), so no new template file or template_include hook is needed.
// Renders photo + instruments + long bio + optional website via existing helpers/data only.

// Appends the optional "Teaches" meta to the profile page title only (post_title itself is untouched).
add_filter('the_title', 'rbm_msch_teacher_profile_title', 10, 2);
function rbm_msch_teacher_profile_title($title, $post_id) {
    if (!is_singular('msch_teacher') || !in_the_loop() || !is_main_query()) {
        return $title;
    }
    if ((int) $post_id !== get_queried_object_id()) {
        return $title;
    }
    $teaches = trim((string) get_post_meta($post_id, '_msch_teaches', true));
    return ($teaches !== '') ? $title . ', ' . $teaches : $title;
}

add_filter('previous_post_link', 'rbm_msch_teacher_post_nav_arrow');
add_filter('next_post_link', 'rbm_msch_teacher_post_nav_arrow');
function rbm_msch_teacher_post_nav_arrow($output) {
    if (!is_singular('msch_teacher') || $output === '') {
        return $output;
    }
    if (strpos($output, 'rel="prev"') !== false) {
        return str_replace('>Previous<', '>&lt; Previous<', $output);
    }
    if (strpos($output, 'rel="next"') !== false) {
        return str_replace('>Next<', '>Next &gt;<', $output);
    }
    return $output;
}

add_filter('the_content', 'rbm_msch_teacher_single_content');
function rbm_msch_teacher_single_content($content) {
    if (!is_singular('msch_teacher') || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    $teacher_id = get_the_ID();
    $long_bio = rbm_msch_teacher_long_bio(get_post($teacher_id));
    $bio_html = ($long_bio !== '') ? wpautop(wp_kses_post($long_bio)) : '';
    $website = get_post_meta($teacher_id, '_msch_website', true);

    ob_start();
    ?>
    <style>
    /* Scoped to Teacher Profile Pages only; does not affect Faculty/Lesson pages or other post types. */
    body.single-msch_teacher .fusion-post-slideshow,
    body.single-msch_teacher .post-slideshow { display: none !important; }
    body.single-msch_teacher .fusion-post-title { font-size: 34px !important; line-height: 1.3 !important; margin-bottom: 8px !important; }
    body.single-msch_teacher .thesis-teacher-row { max-width: 900px; }
    body.single-msch_teacher .thesis-teacher-photo { flex: 0 0 320px; }
    body.single-msch_teacher .thesis-teacher-photo img { width: 320px; }
    body.single-msch_teacher .thesis-teacher-photo-secondary { margin-top: 16px; }
    /* Avada core .single-navigation adds margin-bottom:60px; tighten it on Teacher Profile only. */
    body.single-msch_teacher .single-navigation { margin-bottom: 20px; display: flex; justify-content: space-between; text-align: left; }
    body.single-msch_teacher .single-navigation a[rel="prev"] { margin-left: 0; }
    body.single-msch_teacher .single-navigation a[rel="next"] { margin-left: 0; margin-right: 0; }
    /* Arrow characters are hard-coded into the link text (see rbm_msch_teacher_post_nav_arrow); suppress Avada's icon-font pseudo-elements here. */
    body.single-msch_teacher .single-navigation a[rel="prev"]::before,
    body.single-msch_teacher .single-navigation a[rel="next"]::after {
        content: none !important;
    }
    @media (max-width: 480px) {
        body.single-msch_teacher .fusion-post-title { font-size: 28px !important; }
    }
    </style>
    <div class="thesis-teacher-row">
        <div class="thesis-teacher-photo">
            <?php echo rbm_msch_render_portrait_photo($teacher_id, 'medium'); ?>
            <?php $second_photo_html = rbm_msch_render_second_profile_photo($teacher_id, 'medium'); ?>
            <?php if ($second_photo_html !== '') : ?>
                <div class="thesis-teacher-photo-secondary"><?php echo $second_photo_html; ?></div>
            <?php endif; ?>
        </div>
        <div class="thesis-teacher-bio">
            <?php if ($bio_html !== '') : ?>
                <?php echo $bio_html; ?>
            <?php endif; ?>
            <?php if (!empty($website)) : ?>
                <p><a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer">Visit Website</a></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// --- Simplified Teacher Editor meta boxes: Status, Website, Advanced (admin-only) ---
// See docs/0908-1248-PLAN-Simplified-Teacher-Editor.txt

add_action('add_meta_boxes', 'rbm_add_teacher_meta_box');
function rbm_add_teacher_meta_box() {
    add_meta_box('rbm_teacher_status', 'Status', 'rbm_render_teacher_status_box', 'msch_teacher', 'side', 'high');
    add_meta_box('rbm_teacher_card_photo', 'Card Photo (Compact/Faculty Card)', 'rbm_render_teacher_card_photo_box', 'msch_teacher', 'side', 'default');
    add_meta_box('rbm_teacher_website', 'Website / Professional Link', 'rbm_render_teacher_website_box', 'msch_teacher', 'normal', 'high');
    add_meta_box('rbm_teacher_advanced', 'Advanced (Admin Only)', 'rbm_render_teacher_advanced_box', 'msch_teacher', 'normal', 'low');
}

function rbm_render_teacher_status_box($post) {
    wp_nonce_field('rbm_save_teacher_meta_' . $post->ID, 'rbm_teacher_meta_nonce');
    $is_active = ($post->post_status === 'publish');
    ?>
    <p>
        <label>
            <input type="radio" name="rbm_teacher_status" value="active" <?php checked($is_active); ?>>
            Active
        </label>
        <br>
        <label>
            <input type="radio" name="rbm_teacher_status" value="inactive" <?php checked(!$is_active); ?>>
            Inactive
        </label>
    </p>
    <?php
}

function rbm_render_teacher_website_box($post) {
    $website = get_post_meta($post->ID, '_msch_website', true);
    ?>
    <p>
        <label for="rbm_msch_website">Website / Professional Link</label><br>
        <input type="url" id="rbm_msch_website" name="rbm_msch_website" class="widefat" value="<?php echo esc_attr($website); ?>" placeholder="https://">
    </p>
    <?php
}

// Card Photo: separate manually-cropped square image for compact/Faculty cards.
// The Featured Image field remains the Portrait Photo (Full/Profile displays); the two are never
// substituted for each other (see docs/0908-1306-PLAN-Simplified-Teacher-Editor-Image-Fields-And-Placeholder.txt).
function rbm_render_teacher_card_photo_box($post) {
    $card_photo_id = (int) get_post_meta($post->ID, '_msch_card_photo_id', true);
    $image_html = $card_photo_id ? wp_get_attachment_image($card_photo_id, 'thumbnail') : '';
    ?>
    <p><em>Separate square image for compact Faculty/teacher cards. Not auto-cropped from Portrait Photo.</em></p>
    <div id="rbm_card_photo_preview"><?php echo $image_html; ?></div>
    <input type="hidden" id="rbm_msch_card_photo_id" name="rbm_msch_card_photo_id" value="<?php echo esc_attr($card_photo_id); ?>">
    <p>
        <button type="button" class="button rbm-media-picker" data-target="rbm_msch_card_photo_id" data-preview="rbm_card_photo_preview">
            <?php echo $card_photo_id ? 'Replace Image' : 'Select Image'; ?>
        </button>
        <button type="button" class="button rbm-media-remove" data-target="rbm_msch_card_photo_id" data-preview="rbm_card_photo_preview" <?php echo $card_photo_id ? '' : 'style="display:none;"'; ?>>
            Remove Image
        </button>
    </p>
    <?php
}

// Private admin-only fields: never register these with register_post_meta(show_in_rest => true).
function rbm_render_teacher_advanced_box($post) {
    $email       = get_post_meta($post->ID, '_msch_email', true);
    $phone       = get_post_meta($post->ID, '_msch_phone', true);
    $external_id = get_post_meta($post->ID, '_msch_external_id', true);
    ?>
    <p><em>Not displayed publicly.</em></p>
    <p>
        <label for="rbm_msch_email">Email</label><br>
        <input type="email" id="rbm_msch_email" name="rbm_msch_email" class="widefat" value="<?php echo esc_attr($email); ?>">
    </p>
    <p>
        <label for="rbm_msch_phone">Phone</label><br>
        <input type="text" id="rbm_msch_phone" name="rbm_msch_phone" class="widefat" value="<?php echo esc_attr($phone); ?>">
    </p>
    <p>
        <label for="rbm_msch_external_id">External / RBAdmin ID</label><br>
        <input type="text" id="rbm_msch_external_id" name="rbm_msch_external_id" class="widefat" value="<?php echo esc_attr($external_id); ?>">
    </p>
    <?php
}

add_action('save_post_msch_teacher', 'rbm_save_teacher_meta');
function rbm_save_teacher_meta($post_id) {
    $nonce = $_POST['rbm_teacher_meta_nonce'] ?? '';
    // The standalone form (rbm-teacher-form.php) scopes its nonce to teacher_id=0 when
    // creating a new record, since the real ID doesn't exist until wp_insert_post() runs;
    // accept that alongside the real $post_id so a new teacher's meta still saves on create.
    if (!wp_verify_nonce($nonce, 'rbm_save_teacher_meta_' . $post_id) && !wp_verify_nonce($nonce, 'rbm_save_teacher_meta_0')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Active/Inactive maps to native publish/draft status (no separate meta field).
    if (isset($_POST['rbm_teacher_status'])) {
        $desired = ($_POST['rbm_teacher_status'] === 'active') ? 'publish' : 'draft';
        $current = get_post_status($post_id);
        if ($current !== $desired && in_array($current, ['publish', 'draft', 'pending', 'private'], true)) {
            remove_action('save_post_msch_teacher', 'rbm_save_teacher_meta');
            wp_update_post(['ID' => $post_id, 'post_status' => $desired]);
            add_action('save_post_msch_teacher', 'rbm_save_teacher_meta');
        }
    }

    if (isset($_POST['rbm_msch_website'])) {
        update_post_meta($post_id, '_msch_website', esc_url_raw(wp_unslash($_POST['rbm_msch_website'])));
    }
    if (isset($_POST['rbm_msch_card_photo_id'])) {
        $card_photo_id = (int) $_POST['rbm_msch_card_photo_id'];
        if ($card_photo_id > 0 && wp_attachment_is_image($card_photo_id)) {
            update_post_meta($post_id, '_msch_card_photo_id', $card_photo_id);
        } else {
            delete_post_meta($post_id, '_msch_card_photo_id');
        }
    }
    if (isset($_POST['rbm_msch_second_profile_photo_id'])) {
        $second_photo_id = (int) $_POST['rbm_msch_second_profile_photo_id'];
        if ($second_photo_id > 0 && wp_attachment_is_image($second_photo_id)) {
            update_post_meta($post_id, '_msch_second_profile_photo_id', $second_photo_id);
        } else {
            delete_post_meta($post_id, '_msch_second_profile_photo_id');
        }
    }
    if (isset($_POST['rbm_msch_email'])) {
        update_post_meta($post_id, '_msch_email', sanitize_email(wp_unslash($_POST['rbm_msch_email'])));
    }
    if (isset($_POST['rbm_msch_phone'])) {
        update_post_meta($post_id, '_msch_phone', sanitize_text_field(wp_unslash($_POST['rbm_msch_phone'])));
    }
    if (isset($_POST['rbm_msch_external_id'])) {
        update_post_meta($post_id, '_msch_external_id', sanitize_text_field(wp_unslash($_POST['rbm_msch_external_id'])));
    }
    if (isset($_POST['rbm_msch_admin_memo'])) {
        update_post_meta($post_id, '_msch_admin_memo', sanitize_textarea_field(wp_unslash($_POST['rbm_msch_admin_memo'])));
    }
}

// --- Media picker JS (Card Photo box + Teacher Placeholder settings page) ---

add_action('admin_enqueue_scripts', 'rbm_teacher_media_picker_assets');
function rbm_teacher_media_picker_assets($hook) {
    $screen = get_current_screen();
    $is_teacher_edit = $screen && $screen->post_type === 'msch_teacher' && in_array($hook, ['post.php', 'post-new.php'], true);
    // CPT submenu pages don't get a predictable "{menu}_page_{slug}" hook suffix (no add_menu_page() call
    // backs edit.php?post_type=X), so match on the actual admin-page $_GET['page'] instead of guessing $hook.
    $page_param = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $is_placeholder_page = $page_param === 'rbm-teacher-placeholder';
    $is_teacher_form_page = $page_param === 'rbm-teacher-form';
    if (!$is_teacher_edit && !$is_placeholder_page && !$is_teacher_form_page) {
        return;
    }
    wp_enqueue_media();
    wp_add_inline_script('media-editor', <<<'JS'
        jQuery(function($){
            var frame;
            $(document).on('click', '.rbm-media-picker', function(e){
                e.preventDefault();
                var button = $(this);
                var targetId = '#' + button.data('target');
                var previewId = '#' + button.data('preview');
                frame = wp.media({ title: 'Select Image', multiple: false, library: { type: 'image' } });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $(targetId).val(attachment.id);
                    var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
                    $(previewId).html('<img src="' + url + '" style="max-width:150px;height:auto;display:block;">');
                    button.text('Replace Image');
                    button.siblings('.rbm-media-remove').show();
                });
                frame.open();
            });
            $(document).on('click', '.rbm-media-remove', function(e){
                e.preventDefault();
                var button = $(this);
                var targetId = '#' + button.data('target');
                var previewId = '#' + button.data('preview');
                $(targetId).val('');
                $(previewId).html('');
                button.hide();
                button.siblings('.rbm-media-picker').text('Select Image');
            });
        });
        JS
    );
}

// --- Shared Teacher Placeholder (site-level fallback, not copied into teacher records) ---

add_action('admin_menu', 'rbm_add_teacher_placeholder_menu');
function rbm_add_teacher_placeholder_menu() {
    add_submenu_page(
        'edit.php?post_type=msch_teacher',
        'Teacher Placeholder',
        'Placeholder',
        'manage_options',
        'rbm-teacher-placeholder',
        'rbm_render_teacher_placeholder_page'
    );
}

function rbm_render_teacher_placeholder_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_POST['rbm_placeholder_nonce']) && wp_verify_nonce($_POST['rbm_placeholder_nonce'], 'rbm_save_teacher_placeholder')) {
        $id = (int) ($_POST['rbm_msch_placeholder_id'] ?? 0);
        if ($id > 0) {
            update_option('rbm_teacher_placeholder_id', $id);
        } else {
            delete_option('rbm_teacher_placeholder_id');
        }
        echo '<div class="updated"><p>Saved.</p></div>';
    }
    $placeholder_id = (int) get_option('rbm_teacher_placeholder_id');
    $image_html = $placeholder_id ? wp_get_attachment_image($placeholder_id, 'thumbnail') : '';
    ?>
    <div class="wrap">
        <h1>Teacher Placeholder</h1>
        <p>Shared fallback image used site-wide whenever a teacher's Card Photo (compact) or Portrait Photo (full) is empty. Not stored on individual teacher records.</p>
        <form method="post">
            <?php wp_nonce_field('rbm_save_teacher_placeholder', 'rbm_placeholder_nonce'); ?>
            <div id="rbm_placeholder_preview"><?php echo $image_html; ?></div>
            <input type="hidden" id="rbm_msch_placeholder_id" name="rbm_msch_placeholder_id" value="<?php echo esc_attr($placeholder_id); ?>">
            <p>
                <button type="button" class="button rbm-media-picker" data-target="rbm_msch_placeholder_id" data-preview="rbm_placeholder_preview">
                    <?php echo $placeholder_id ? 'Replace Image' : 'Select Image'; ?>
                </button>
                <button type="button" class="button rbm-media-remove" data-target="rbm_msch_placeholder_id" data-preview="rbm_placeholder_preview" <?php echo $placeholder_id ? '' : 'style="display:none;"'; ?>>
                    Remove Image
                </button>
            </p>
            <?php submit_button('Save Placeholder'); ?>
        </form>
        <?php if (!$placeholder_id) : ?>
            <p><em>No image selected. A built-in neutral silhouette is used automatically until one is set here.</em></p>
        <?php endif; ?>
    </div>
    <?php
}

// Built-in neutral square silhouette, used only when no shared placeholder attachment is configured.
function rbm_msch_default_placeholder_svg($class = '') {
    $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';
    return '<svg' . $class_attr . ' viewBox="0 0 100 100" width="100%" height="100%" role="img" aria-label="Teacher photo placeholder" xmlns="http://www.w3.org/2000/svg" style="background:#e4e4e4;display:block;">'
        . '<circle cx="50" cy="38" r="18" fill="#b7b7b7"/>'
        . '<path d="M18 90c2-22 16-34 32-34s30 12 32 34" fill="#b7b7b7"/>'
        . '</svg>';
}

// Renders Card Photo (compact/Faculty cards); falls back to the shared placeholder, never to Portrait Photo.
function rbm_msch_render_card_photo($teacher_id, $class = '') {
    $card_photo_id = (int) get_post_meta($teacher_id, '_msch_card_photo_id', true);
    if ($card_photo_id) {
        return wp_get_attachment_image($card_photo_id, 'medium', false, ['class' => $class, 'loading' => 'lazy']);
    }
    return rbm_msch_render_placeholder($class);
}

// Renders Portrait Photo (Full/Profile displays) via the native Featured Image; falls back to the
// shared placeholder, never to Card Photo.
function rbm_msch_render_portrait_photo($teacher_id, $size = 'medium', $class = '') {
    if (has_post_thumbnail($teacher_id)) {
        return get_the_post_thumbnail($teacher_id, $size, ['class' => $class]);
    }
    return rbm_msch_render_placeholder($class);
}

function rbm_msch_render_placeholder($class = '') {
    $placeholder_id = (int) get_option('rbm_teacher_placeholder_id');
    if ($placeholder_id) {
        return wp_get_attachment_image($placeholder_id, 'medium', false, ['class' => $class, 'loading' => 'lazy']);
    }
    return rbm_msch_default_placeholder_svg($class);
}

// Renders the optional Second Profile Image (Teacher Profile page only); no fallback to Portrait
// Photo, Card Photo, or the shared placeholder — returns '' when no image is assigned.
function rbm_msch_render_second_profile_photo($teacher_id, $size = 'medium', $class = '') {
    $photo_id = (int) get_post_meta($teacher_id, '_msch_second_profile_photo_id', true);
    if (!$photo_id) {
        return '';
    }
    $alt = trim((string) get_post_meta($photo_id, '_wp_attachment_image_alt', true));
    if ($alt === '') {
        $alt = get_the_title($teacher_id) . ' at Red Barn Music School';
    }
    return wp_get_attachment_image($photo_id, $size, false, ['class' => $class, 'loading' => 'lazy', 'alt' => $alt]);
}

// --- Admin list columns: Photo, Name (native), Instruments (native tax column), Status (native), Order ---

add_filter('manage_msch_teacher_posts_columns', 'rbm_teacher_admin_columns');
function rbm_teacher_admin_columns($columns) {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'cb') {
            $new['rbm_photo'] = 'Photo';
        }
        if ($key === 'title') {
            $new['rbm_order'] = 'Order';
        }
    }
    return $new;
}

add_action('manage_msch_teacher_posts_custom_column', 'rbm_teacher_admin_column_content', 10, 2);
function rbm_teacher_admin_column_content($column, $post_id) {
    if ($column === 'rbm_photo') {
        echo has_post_thumbnail($post_id) ? get_the_post_thumbnail($post_id, [40, 40]) : '&#8212;';
    }
    if ($column === 'rbm_order') {
        $post = get_post($post_id);
        echo esc_html($post->menu_order);
    }
}

// --- [msch_teachers] shortcode (Phase 3: compact mode, Phase 4: full mode) ---

add_shortcode('msch_teachers', 'rbm_msch_teachers_shortcode');
function rbm_msch_teachers_shortcode($atts) {
    $atts = shortcode_atts([
        'mode'       => 'compact',
        'instrument' => '',
        'anchors'    => '', // full mode only: "teacherID:legacy-anchor-id,teacherID:legacy-anchor-id"
        'order'      => 'name', // "name" = alphabetical by displayed teacher title; "manual" = numeric Order field
        'bio'        => '', // '' = mode default; 'none'|'short'|'long' (short = stored Excerpt, long = stored post content; no auto-fallback)
        'website'    => 'yes', // 'yes'|'no'
        'columns'    => 'auto', // compact mode only: 'auto'|'1'|'2'|'3'|'4'
    ], $atts, 'msch_teachers');

    $order_mode = ($atts['order'] === 'manual') ? 'manual' : 'name';

    $bio_mode = strtolower(trim((string) $atts['bio']));
    if (!in_array($bio_mode, ['none', 'short', 'long'], true)) {
        $bio_mode = ($atts['mode'] === 'full') ? 'long' : 'short';
    }
    $website_enabled = (strtolower(trim((string) $atts['website'])) !== 'no');

    $query_args = [
        'post_type'      => 'msch_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => ($order_mode === 'manual')
            ? ['menu_order' => 'ASC', 'title' => 'ASC']
            : ['title' => 'ASC'],
    ];

    if (!empty($atts['instrument'])) {
        $query_args['tax_query'] = [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'slug',
            'terms'    => sanitize_title($atts['instrument']),
        ]];
    }

    $teachers = get_posts($query_args);
    if (empty($teachers)) {
        return '';
    }

    if ($atts['mode'] === 'full') {
        return rbm_msch_teachers_full_mode($teachers, $atts, $bio_mode, $website_enabled);
    }

    // Distinct instrument terms among the teachers actually being displayed, alphabetical by name.
    $filter_terms = [];
    foreach ($teachers as $t) {
        $t_terms = get_the_terms($t->ID, 'msch_instrument');
        if (is_array($t_terms) && !is_wp_error($t_terms)) {
            foreach ($t_terms as $term) {
                $filter_terms[$term->slug] = $term->name;
            }
        }
    }
    asort($filter_terms);

    static $rbm_teachers_instance = 0;
    $rbm_teachers_instance++;
    $filter_id = 'msch-teacher-filter-' . $rbm_teachers_instance;

    static $rbm_teachers_css_printed = false;
    ob_start();
    if (!$rbm_teachers_css_printed) {
        $rbm_teachers_css_printed = true;
        ?>
        <style>
        .msch-teacher-filter{margin:0 0 1.5em;}
        .msch-teacher-filter-select{
            font-size:18px;
            font-weight:600;
            font-family:inherit;
            line-height:1.2;
            padding:12px 24px;
            border:none;
            border-radius:999px;
            background:#6cbf3f;
            color:#ffffff;
            cursor:pointer;
            box-shadow:none;
            transition:background-color 0.15s ease;
        }
        .msch-teacher-filter-select:hover,
        .msch-teacher-filter-select:focus{
            background:#5aa932;
            outline:none;
        }
        </style>
        <script>
        document.addEventListener('change', function (e) {
            if (!e.target || !e.target.classList || !e.target.classList.contains('msch-teacher-filter-select')) {
                return;
            }
            var wrap = e.target.closest('.msch-teacher-filter');
            var grid = wrap ? wrap.nextElementSibling : null;
            if (!grid || !grid.classList.contains('thesis-lesson-card-grid')) {
                return;
            }
            var value = e.target.value;
            var cards = grid.querySelectorAll('.thesis-teacher-card');
            cards.forEach(function (card) {
                if (!value || value === 'all') {
                    card.style.display = '';
                    return;
                }
                var terms = (card.getAttribute('data-msch-instruments') || '').split(',');
                card.style.display = (terms.indexOf(value) !== -1) ? '' : 'none';
            });
        }, false);
        </script>
        <?php
    }
    if (!empty($filter_terms)) : ?>
        <div class="msch-teacher-filter">
            <select id="<?php echo esc_attr($filter_id); ?>" class="msch-teacher-filter-select" aria-label="Filter Faculty by instrument">
                <option value="" disabled selected>Choose Instrument</option>
                <option value="all">All Faculty</option>
                <?php foreach ($filter_terms as $slug => $name) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="thesis-lesson-card-grid">
        <?php foreach ($teachers as $teacher) :
            $instruments = get_the_terms($teacher->ID, 'msch_instrument');
            $instrument_slugs = (is_array($instruments) && !is_wp_error($instruments))
                ? wp_list_pluck($instruments, 'slug')
                : [];
            $teaches = trim((string) get_post_meta($teacher->ID, '_msch_teaches', true));
            $profile_url = get_permalink($teacher->ID);
        ?>
        <a class="thesis-lesson-card thesis-teacher-card" href="<?php echo esc_url($profile_url); ?>" data-msch-instruments="<?php echo esc_attr(implode(',', $instrument_slugs)); ?>">
            <span class="thesis-lesson-card-image"><?php echo rbm_msch_render_card_photo($teacher->ID); ?></span>
            <span class="thesis-lesson-card-title"><?php echo esc_html(get_the_title($teacher)); ?></span>
            <?php if ($teaches !== '') : ?>
                <span class="thesis-lesson-card-line thesis-teacher-card-instrument"><?php echo esc_html($teaches); ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Reads the stored Excerpt field directly; never auto-generates from post content.
function rbm_msch_teacher_short_bio($teacher) {
    return isset($teacher->post_excerpt) ? trim($teacher->post_excerpt) : '';
}

// Reads the stored main content directly.
function rbm_msch_teacher_long_bio($teacher) {
    return isset($teacher->post_content) ? trim($teacher->post_content) : '';
}

// Full mode (Phase 4): replaces per-page duplicated Teacher/Bio HTML on lesson pages.
function rbm_msch_teachers_full_mode($teachers, $atts, $bio_mode = 'long', $website_enabled = true) {
    $anchor_map = [];
    if (!empty($atts['anchors'])) {
        foreach (explode(',', $atts['anchors']) as $pair) {
            $parts = explode(':', trim($pair), 2);
            if (count($parts) === 2 && (int) $parts[0] > 0 && $parts[1] !== '') {
                $anchor_map[(int) $parts[0]] = $parts[1];
            }
        }
    }

    $instrument_label = '';
    if (!empty($atts['instrument'])) {
        $term = get_term_by('slug', sanitize_title($atts['instrument']), 'msch_instrument');
        $instrument_label = $term ? strtoupper($term->name) : strtoupper($atts['instrument']);
    }

    ob_start();
    foreach ($teachers as $index => $teacher) :
        if ($index > 0) {
            echo '<hr />';
        }
        $anchor_id = isset($anchor_map[$teacher->ID]) ? $anchor_map[$teacher->ID] : '';
        $title = get_the_title($teacher);
        if ($instrument_label !== '') {
            $title .= ' - ' . $instrument_label;
        }
        $bio_html = '';
        if ($bio_mode === 'long') {
            $long_bio = rbm_msch_teacher_long_bio($teacher);
            $bio_html = ($long_bio !== '') ? wpautop(wp_kses_post($long_bio)) : '';
        } elseif ($bio_mode === 'short') {
            $short_bio = rbm_msch_teacher_short_bio($teacher);
            $bio_html = ($short_bio !== '') ? wpautop(wp_kses_post($short_bio)) : '';
        }
        $website  = get_post_meta($teacher->ID, '_msch_website', true);
        $show_website_link = false;
        if ($website_enabled && !empty($website)) {
            $host = parse_url($website, PHP_URL_HOST);
            // Preserve legacy behavior: suppress the link if the host is already named in the teacher's main content.
            $show_website_link = !$host || stripos($teacher->post_content, $host) === false;
        }
    ?>
    <div<?php echo $anchor_id ? ' id="' . esc_attr($anchor_id) . '"' : ''; ?>>
        <h2 class="thesis-teacher-title"><?php echo esc_html($title); ?></h2>
        <div class="thesis-teacher-row">
            <div class="thesis-teacher-photo">
                <?php if (has_post_thumbnail($teacher->ID)) : ?>
                <a href="<?php echo esc_url(get_the_post_thumbnail_url($teacher->ID, 'full')); ?>">
                    <?php echo rbm_msch_render_portrait_photo($teacher->ID, 'medium'); ?>
                </a>
                <?php else : ?>
                    <?php echo rbm_msch_render_portrait_photo($teacher->ID, 'medium'); ?>
                <?php endif; ?>
            </div>
            <div class="thesis-teacher-bio">
                <?php if ($bio_html !== '') : ?>
                <?php echo $bio_html; ?>
                <?php endif; ?>
                <?php if ($show_website_link) : ?>
                <p><a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer">Visit Website</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    endforeach;
    return ob_get_clean();
}
