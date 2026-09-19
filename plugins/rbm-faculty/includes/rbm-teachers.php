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

// Canonical Sign Up / Lesson Inquiry destination (docs/0919-0141-Copilot-REQUEST-Implement-
// Prominent-Sign-Up-Placement-And-Flow.txt): reuses the existing _msch_lesson_signup_url meta,
// already set to the same value on every published Instrument, instead of hardcoding another
// '/lessons-inquiry/' string in this plugin. Falls back to the Lessons Inquiry page itself if no
// Instrument/meta value is found. No instrument/teacher context is passed yet (later phase).
function rbm_msch_signup_url() {
    static $url = null;
    if ($url !== null) {
        return $url;
    }
    $lessons = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
    ]);
    foreach ($lessons as $lesson) {
        $signup_url = get_post_meta($lesson->ID, '_msch_lesson_signup_url', true);
        if (!empty($signup_url)) {
            $url = $signup_url;
            return $url;
        }
    }
    $page = get_posts(['post_type' => 'page', 'name' => 'lessons-inquiry', 'posts_per_page' => 1]);
    $url = !empty($page) ? get_permalink($page[0]) : home_url('/lessons-inquiry/');
    return $url;
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
    return ($teaches !== '') ? $title . ' - ' . $teaches : $title;
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
    rbm_faculty_enqueue_frontend_style();

    ob_start();
    ?>
    <p class="msch-teacher-signup-top"><a class="thesis-cta-button" href="<?php echo esc_url(rbm_msch_signup_url()); ?>">Sign Up</a></p>
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
    <p class="msch-teacher-signup-bottom"><a class="thesis-cta-button" href="<?php echo esc_url(rbm_msch_signup_url()); ?>">Sign Up</a></p>
    <?php
    return ob_get_clean();
}

// Front-end CSS loader (docs/0917-1053-PLAN-Extract-Inline-JS-CSS-From-RBM-Plugins.txt) — shared by
// the Teacher Profile page above and the [msch_teachers] filter select below, enqueued rather than
// echoed inline so it's only registered once per page regardless of how many times it's needed.
function rbm_faculty_enqueue_frontend_style() {
    static $enqueued = false;
    if ($enqueued) {
        return;
    }
    $enqueued = true;
    $css_path = RBM_FACULTY_DIR . '/assets/css/rbm-faculty.css';
    wp_enqueue_style('rbm-faculty', RBM_FACULTY_URL . 'assets/css/rbm-faculty.css', [], file_exists($css_path) ? filemtime($css_path) : false);
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

// Centralized admin asset loader for rbm-faculty (docs/0917-1053-PLAN-Extract-Inline-JS-CSS-From-
// RBM-Plugins.txt): the Teacher edit screen, the Teacher Placeholder settings page, and the
// standalone Teacher form. Covers the Media Library picker (Card Photo/Portrait/Second Profile/
// Placeholder fields) and the Copy Shortcode/Copy List clipboard controls. Never enqueued globally
// across wp-admin.
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
    $js_path = RBM_FACULTY_DIR . '/assets/js/rbm-faculty-admin.js';
    wp_enqueue_script('rbm-faculty-admin', RBM_FACULTY_URL . 'assets/js/rbm-faculty-admin.js', ['jquery'], file_exists($js_path) ? filemtime($js_path) : false, true);
}

// --- Faculty Instruments We Teach (docs/0912-1838-Copilot-REQUEST-Add-Faculty-Instruments-We-Teach-List-And-Download.txt) ---
// Self-contained within this plugin: source of truth is this plugin's own msch_teacher posts and
// their own msch_instrument term assignments. No rbm-lessons function, taxonomy, or option is read.
// Active/public rule reused as-is from the existing Status meta box: post_status === 'publish'
// (see rbm_render_teacher_status_box() / rbm_save_teacher_meta() above - Active maps to 'publish').
function rbm_faculty_get_instruments_we_teach_titles() {
    $teacher_ids = get_posts([
        'post_type'      => 'msch_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    $names = [];
    foreach ($teacher_ids as $teacher_id) {
        $terms = get_the_terms($teacher_id, 'msch_instrument');
        if (is_array($terms)) {
            foreach ($terms as $term) {
                $names[$term->name] = true; // keyed by name to de-duplicate
            }
        }
    }
    $names = array_keys($names);
    sort($names, SORT_STRING | SORT_FLAG_CASE);
    return $names;
}

add_action('admin_post_rbm_download_faculty_instruments_we_teach', 'rbm_faculty_download_instruments_we_teach');
function rbm_faculty_download_instruments_we_teach() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_download_faculty_instruments_we_teach');
    $titles = rbm_faculty_get_instruments_we_teach_titles();
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="instruments-we-teach.txt"');
    echo implode("\n", $titles) . (empty($titles) ? '' : "\n");
    exit;
}

// --- Faculty Instruments Taught Plaintext Export (docs/0912-1851-Copilot-REQUEST-Faculty-Instruments-Taught-Plaintext-Export.txt) ---
// Literal, unparsed export: Teacher Name (post_title) plus the exact stored '_msch_teaches' value
// (the free-text "Instruments Taught" field on the Add/Edit Teacher form). No splitting,
// normalizing, or deduplication. Independent from rbm-lessons (no cross-plugin calls).
function rbm_faculty_get_lessons_we_teach_rows() {
    $teacher_ids = get_posts([
        'post_type'      => 'msch_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    $rows = [];
    foreach ($teacher_ids as $teacher_id) {
        $rows[] = [
            'name'    => get_the_title($teacher_id),
            'teaches' => trim((string) get_post_meta($teacher_id, '_msch_teaches', true)),
        ];
    }
    usort($rows, function ($a, $b) {
        $cmp = strcasecmp($a['teaches'], $b['teaches']);
        return $cmp !== 0 ? $cmp : strcasecmp($a['name'], $b['name']);
    });
    return $rows;
}

function rbm_faculty_get_lessons_we_teach_plaintext() {
    $lines = ["Teacher\tInstruments Taught"];
    foreach (rbm_faculty_get_lessons_we_teach_rows() as $row) {
        $lines[] = $row['name'] . "\t" . $row['teaches'];
    }
    return implode("\n", $lines);
}

add_action('admin_post_rbm_download_faculty_lessons_we_teach', 'rbm_faculty_download_lessons_we_teach');
function rbm_faculty_download_lessons_we_teach() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_download_faculty_lessons_we_teach');
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="faculty-lessons-we-teach.txt"');
    echo rbm_faculty_get_lessons_we_teach_plaintext() . "\n";
    exit;
}

// --- Shared Teacher Placeholder (site-level fallback, not copied into teacher records) ---

add_action('admin_menu', 'rbm_add_teacher_placeholder_menu');
function rbm_add_teacher_placeholder_menu() {
    add_submenu_page(
        'edit.php?post_type=msch_teacher',
        'Teacher Placeholder',
        'Settings',
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
        <p><button type="submit" form="rbm-teacher-placeholder-form" class="button button-primary">Save Placeholder</button></p>
        <form method="post" id="rbm-teacher-placeholder-form">
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

        <h2>INSTRUMENTS WE TEACH</h2>
        <?php
        $instrument_titles = rbm_faculty_get_instruments_we_teach_titles();
        if (empty($instrument_titles)) :
        ?>
            <p>No instruments are currently assigned to active Faculty.</p>
        <?php else : ?>
            <p>
                <textarea id="rbm-faculty-iwt-list" readonly rows="<?php echo (int) max(3, count($instrument_titles)); ?>" style="width:320px;max-width:100%;font-family:monospace;"><?php echo esc_textarea(implode("\n", $instrument_titles)); ?></textarea>
            </p>
            <p>
                <button type="button" class="button rbm-copy-list-trigger" data-copy-target="rbm-faculty-iwt-list" data-status-target="rbm-faculty-iwt-copy-status">Copy List</button>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_download_faculty_instruments_we_teach'), 'rbm_download_faculty_instruments_we_teach')); ?>">Download .txt</a>
                <span id="rbm-faculty-iwt-copy-status" style="margin-left:8px;"></span>
            </p>
        <?php endif; ?>

        <h2>FACULTY &mdash; LESSONS WE TEACH</h2>
        <?php
        $lwt_rows = rbm_faculty_get_lessons_we_teach_rows();
        $lwt_text = rbm_faculty_get_lessons_we_teach_plaintext();
        ?>
        <p>
            <textarea id="rbm-faculty-lwt-list" readonly rows="<?php echo (int) max(3, count($lwt_rows) + 1); ?>" style="width:480px;max-width:100%;font-family:monospace;white-space:pre;"><?php echo esc_textarea($lwt_text); ?></textarea>
        </p>
        <p>
            <button type="button" class="button rbm-copy-list-trigger" data-copy-target="rbm-faculty-lwt-list" data-status-target="rbm-faculty-lwt-copy-status">Copy List</button>
            <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_download_faculty_lessons_we_teach'), 'rbm_download_faculty_lessons_we_teach')); ?>">Download .txt</a>
            <span id="rbm-faculty-lwt-copy-status" style="margin-left:8px;"></span>
        </p>
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

// Removes The SEO Framework's per-post SEO score column from this list — Teachers aren't indexed
// content the way blog posts are, so that column is unused here. TSF registers it via the screen-
// based columns hook, not the post-type one this plugin otherwise uses, so both are covered here;
// priority 20 runs after TSF's own priority-10 registration so its column key is always present.
add_filter('manage_msch_teacher_posts_columns', 'rbm_teacher_remove_seo_column', 20);
add_filter('manage_edit-msch_teacher_columns', 'rbm_teacher_remove_seo_column', 20);
function rbm_teacher_remove_seo_column($columns) {
    unset($columns['tsf-seo-bar-wrap']);
    return $columns;
}

// Status filter (docs/0913-1544-Copilot-REQUEST-Add-Faculty-Status-Controls-And-List-State-
// Persistence.txt): read-only list filter reusing the existing publish/draft rule already used by
// the Status meta box (rbm_render_teacher_status_box()) — no new status field. Default is 'all'
// (WP's own current default, showing every status) rather than 'active', since forcing a narrower
// default would silently hide existing Draft teachers from admins used to seeing them by default.
function rbm_msch_teacher_current_status_filter() {
    $status = isset($_GET['rbm_status']) ? sanitize_key(wp_unslash($_GET['rbm_status'])) : 'all';
    return in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'all';
}

add_action('pre_get_posts', 'rbm_msch_teacher_filter_by_status');
function rbm_msch_teacher_filter_by_status($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'msch_teacher') {
        return;
    }
    $status_filter = rbm_msch_teacher_current_status_filter();
    if ($status_filter === 'active') {
        $query->set('post_status', 'publish');
    } elseif ($status_filter === 'inactive') {
        $query->set('post_status', 'draft');
    }
    // 'all' intentionally leaves WP's own default post_status handling untouched.
}

add_action('admin_notices', 'rbm_msch_teacher_status_filter_control');
function rbm_msch_teacher_status_filter_control() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-msch_teacher') {
        return;
    }
    $status_filter = rbm_msch_teacher_current_status_filter();
    $base_url = remove_query_arg(['rbm_status', 'paged']);
    ?>
    <div class="rbm-teacher-status-filter" style="display:flex; align-items:center; gap:6px; margin:10px 0;">
        <strong>Status:</strong>
        <a href="<?php echo esc_url(add_query_arg('rbm_status', 'active', $base_url)); ?>" class="button<?php echo $status_filter === 'active' ? ' button-primary' : ''; ?>">Active</a>
        <a href="<?php echo esc_url(add_query_arg('rbm_status', 'inactive', $base_url)); ?>" class="button<?php echo $status_filter === 'inactive' ? ' button-primary' : ''; ?>">Inactive</a>
        <a href="<?php echo esc_url(add_query_arg('rbm_status', 'all', $base_url)); ?>" class="button<?php echo $status_filter === 'all' ? ' button-primary' : ''; ?>">All</a>
    </div>
    <?php
}

// Persist the selected status through the list's own search form submission (pagination/column
// sort links already carry it via the current URL, since they're generated with add_query_arg()).
add_action('restrict_manage_posts', 'rbm_msch_teacher_status_hidden_field');
function rbm_msch_teacher_status_hidden_field($post_type) {
    if ($post_type !== 'msch_teacher') {
        return;
    }
    echo '<input type="hidden" name="rbm_status" value="' . esc_attr(rbm_msch_teacher_current_status_filter()) . '">';
}

// Per-user list-state persistence (same generic pattern as rbm-instruments, docs/0913-1455-PLAN-
// Generic-Query-Param-List-State-Persistence.txt): whatever's in the URL besides post_type/paged/
// known action-noise params gets remembered, so a bare visit reopens where the user left off.
// First-time use (nothing saved yet) falls through to WP's own current default behavior.
add_action('load-edit.php', 'rbm_msch_teacher_remember_list_state');
function rbm_msch_teacher_remember_list_state() {
    if (($_GET['post_type'] ?? '') !== 'msch_teacher') {
        return;
    }
    $ignore = ['post_type', 'paged', 'action', 'action2', '_wpnonce', '_wp_http_referer', 'ids'];
    $state = array_diff_key($_GET, array_flip($ignore));
    $user_id = get_current_user_id();
    if (!empty($state)) {
        $sanitized = [];
        foreach ($state as $key => $value) {
            if (is_string($value)) {
                $sanitized[sanitize_key($key)] = sanitize_text_field(wp_unslash($value));
            }
        }
        update_user_meta($user_id, '_rbm_msch_teacher_list_state', $sanitized);
        return;
    }
    $saved = get_user_meta($user_id, '_rbm_msch_teacher_list_state', true);
    if (!empty($saved) && is_array($saved)) {
        wp_safe_redirect(add_query_arg($saved));
        exit;
    }
}

// --- Filter Instruments (docs/0913-1644-Copilot-REQUEST-Implement-Teacher-By-Instrument-Filtering-
// And-Tile-Links.txt): structured, separate from the display-only Instruments Taught text field.
// Canonical Instrument identity reused as-is from rbm-instruments: a published msch_lesson post
// (no second taxonomy/catalog). Stored explicitly as an array of msch_lesson post IDs on the
// teacher; empty explicit value falls back to the teacher's Faculty category-derived set.
function rbm_msch_teacher_category_derived_instruments($teacher_id) {
    $terms = get_the_terms($teacher_id, 'msch_instrument');
    if (!is_array($terms) || is_wp_error($terms) || empty($terms) || !post_type_exists('msch_lesson')) {
        return [];
    }
    $lesson_ids = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck($terms, 'term_id'),
        ]],
    ]);
    return array_map('intval', $lesson_ids);
}

function rbm_msch_teacher_get_filter_instruments($teacher_id) {
    $explicit = get_post_meta($teacher_id, '_msch_teacher_filter_instruments', true);
    if (is_array($explicit) && !empty($explicit)) {
        return array_map('intval', $explicit);
    }
    return rbm_msch_teacher_category_derived_instruments($teacher_id);
}

// One-time backfill (existing teachers only; empty Filter Instruments only) so the admin control
// shows real saved values, not just a runtime fallback. Idempotent via the option guard, matching
// this codebase's existing seed-once pattern (e.g. rbm_seed_lesson_category_terms()).
add_action('init', 'rbm_backfill_teacher_filter_instruments', 20);
function rbm_backfill_teacher_filter_instruments() {
    if (get_option('rbm_teacher_filter_instruments_backfilled_v1')) {
        return;
    }
    $teacher_ids = get_posts([
        'post_type'      => 'msch_teacher',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    foreach ($teacher_ids as $teacher_id) {
        $existing = get_post_meta($teacher_id, '_msch_teacher_filter_instruments', true);
        if (is_array($existing) && !empty($existing)) {
            continue; // never overwrite an existing explicit value
        }
        $derived = rbm_msch_teacher_category_derived_instruments($teacher_id);
        if (!empty($derived)) {
            update_post_meta($teacher_id, '_msch_teacher_filter_instruments', $derived);
        }
    }
    update_option('rbm_teacher_filter_instruments_backfilled_v1', 1);
}

// Fallback chain (docs/0913-1731-Copilot-REQUEST-Implement-Missing-Faculty-Fallback-Chain.txt):
// EXACT INSTRUMENT -> CATEGORY FALLBACK -> (caller decides terminal return-to-Lessons when this
// returns 'none'). Single resolver reused by both the template_redirect safety check below and the
// shortcode itself, so there is exactly one place that decides who matches an Instrument.
function rbm_msch_resolve_faculty_by_instrument($lesson_id) {
    $base_args = [
        'post_type'      => 'msch_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => ['title' => 'ASC'],
    ];
    $teachers = get_posts($base_args);
    $exact = array_values(array_filter($teachers, function ($t) use ($lesson_id) {
        return in_array($lesson_id, rbm_msch_teacher_get_filter_instruments($t->ID), true);
    }));
    if (!empty($exact)) {
        return ['teachers' => $exact, 'mode' => 'exact', 'category_name' => ''];
    }

    // Category fallback: the same shared msch_instrument taxonomy is the only bridge used — no new
    // mapping table, no string matching on names.
    $terms = get_the_terms($lesson_id, 'msch_instrument');
    if (is_array($terms) && !is_wp_error($terms) && !empty($terms)) {
        $category_args = $base_args;
        $category_args['tax_query'] = [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck($terms, 'term_id'),
        ]];
        $category_teachers = get_posts($category_args);
        if (!empty($category_teachers)) {
            // Prefer a normal (non-Direct-Display) term name for the optional UI note; a Lesson can
            // also be tagged with the special "Instruments We Teach" term, which isn't a real
            // Faculty category label a visitor would recognize.
            $display_term = $terms[0];
            foreach ($terms as $term) {
                if (!function_exists('rbm_msch_category_is_direct_display') || !rbm_msch_category_is_direct_display($term->term_id)) {
                    $display_term = $term;
                    break;
                }
            }
            return ['teachers' => $category_teachers, 'mode' => 'category', 'category_name' => $display_term->name];
        }
    }

    return ['teachers' => [], 'mode' => 'none', 'category_name' => ''];
}

// Full Faculty group for a whole msch_instrument Category term — single resolver reused by both
// the template_redirect safety check and the shortcode's ?category= handling below (docs/0917-1005-
// Copilot-REQUEST-Implement-Category-Click-Destination-Setting.txt). Unlike the per-Instrument
// resolver above, this is always an exact Category match — no further fallback chain involved.
function rbm_msch_teachers_for_category_term($term_id) {
    return get_posts([
        'post_type'      => 'msch_teacher',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => ['title' => 'ASC'],
        'tax_query'      => [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ]],
    ]);
}

// Same-site-only return target for the terminal "Back to Lessons" fallback. Accepts an optional
// ?return= path/URL (only honored if its host matches this site) so a visitor can be sent back to
// the exact Lessons context they came from; otherwise resolves the canonical Lessons page by slug.
function rbm_msch_faculty_return_url() {
    if (!empty($_GET['return'])) {
        $parsed = wp_parse_url(wp_unslash($_GET['return']));
        $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
        if (empty($parsed['host']) || $parsed['host'] === $home_host) {
            $path = isset($parsed['path']) ? $parsed['path'] : '/';
            $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
            return home_url($path . $query);
        }
    }
    $page = get_posts(['post_type' => 'page', 'name' => 'lessons', 'posts_per_page' => 1]);
    return !empty($page) ? get_permalink($page[0]) : home_url('/lessons/');
}

// Terminal fallback: runs before any output, since a shortcode-time redirect would be too late
// (headers already sent). Only acts on the actual Faculty page (has the Teachers shortcode), and
// only when ?instrument= or ?category= resolves to nothing renderable, per the fallback chain's
// STOP condition.
add_action('template_redirect', 'rbm_faculty_instrument_fallback_redirect');
function rbm_faculty_instrument_fallback_redirect() {
    if ((empty($_GET['instrument']) && empty($_GET['category'])) || !is_singular('page')) {
        return;
    }
    $post = get_queried_object();
    if (!$post || (!has_shortcode($post->post_content, 'rbm_msch_teachers_element') && !has_shortcode($post->post_content, 'msch_teachers'))) {
        return;
    }
    if (!empty($_GET['instrument'])) {
        $slug = sanitize_title(wp_unslash($_GET['instrument']));
        $matched = get_posts(['post_type' => 'msch_lesson', 'name' => $slug, 'post_status' => 'publish', 'posts_per_page' => 1]);
        if (empty($matched)) {
            wp_safe_redirect(rbm_msch_faculty_return_url());
            exit;
        }
        $result = rbm_msch_resolve_faculty_by_instrument($matched[0]->ID);
        if (empty($result['teachers'])) {
            wp_safe_redirect(rbm_msch_faculty_return_url());
            exit;
        }
        return;
    }
    // docs/0917-1005-Copilot-REQUEST-Implement-Category-Click-Destination-Setting.txt: ?category=
    // <msch_instrument term slug>, a direct Category match (no per-Instrument fallback chain).
    $slug = sanitize_title(wp_unslash($_GET['category']));
    $term = get_term_by('slug', $slug, 'msch_instrument');
    if (!$term || is_wp_error($term) || empty(rbm_msch_teachers_for_category_term($term->term_id))) {
        wp_safe_redirect(rbm_msch_faculty_return_url());
        exit;
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

    // Public per-Instrument filtering (docs/0913-1644-..., fallback chain added in
    // docs/0913-1731-...): server-side only, independent of the broad Faculty-category
    // 'instrument' attribute above. EXACT INSTRUMENT -> CATEGORY FALLBACK via the shared resolver;
    // the terminal "Back to Lessons" case is handled earlier by template_redirect (a shortcode-time
    // redirect would be too late), so an empty result here is just a safety-net blank render for
    // any context that reaches this shortcode without going through that check.
    $rbm_filter_instrument_id = 0;
    $rbm_filter_category_slug = '';
    $rbm_filter_category_name = '';
    $rbm_fallback_note = '';
    if (!empty($_GET['instrument'])) {
        $slug = sanitize_title(wp_unslash($_GET['instrument']));
        $matched = get_posts([
            'post_type'      => 'msch_lesson',
            'name'           => $slug,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ]);
        if (!empty($matched)) {
            $rbm_filter_instrument_id = $matched[0]->ID;
            $result = rbm_msch_resolve_faculty_by_instrument($rbm_filter_instrument_id);
            $teachers = $result['teachers'];
            if ($result['mode'] === 'category') {
                $rbm_fallback_note = sprintf(
                    'No exact teachers found for %s. Showing %s faculty.',
                    get_the_title($rbm_filter_instrument_id),
                    $result['category_name']
                );
            }
        }
    } elseif (!empty($_GET['category'])) {
        // docs/0917-1005-Copilot-REQUEST-Implement-Category-Click-Destination-Setting.txt: direct
        // Category group link (Instruments "Category Click Destination" = Faculty Group Page).
        $slug = sanitize_title(wp_unslash($_GET['category']));
        $term = get_term_by('slug', $slug, 'msch_instrument');
        if ($term && !is_wp_error($term)) {
            $category_teachers = rbm_msch_teachers_for_category_term($term->term_id);
            if (!empty($category_teachers)) {
                $teachers = $category_teachers;
                $rbm_filter_category_slug = $slug;
                $rbm_filter_category_name = $term->name;
            }
        }
    }

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

    // docs/0918-1532-...: filtered-results heading + #teachers landing anchor. An Instrument
    // request keeps the Instrument's own name even when it fell back to Category teachers (the
    // fallback note above already explains the discrepancy); a direct ?category= link uses the
    // Category's name.
    $rbm_is_filtered = ($rbm_filter_instrument_id || $rbm_filter_category_slug !== '');
    $rbm_teachers_heading = '';
    if ($rbm_filter_instrument_id) {
        $rbm_teachers_heading = get_the_title($rbm_filter_instrument_id) . ' Teachers';
    } elseif ($rbm_filter_category_slug !== '') {
        $rbm_teachers_heading = $rbm_filter_category_name . ' Teachers';
    }

    ob_start();
    rbm_faculty_enqueue_frontend_style();
    $js_path = RBM_FACULTY_DIR . '/assets/js/rbm-faculty.js';
    wp_enqueue_script('rbm-faculty', RBM_FACULTY_URL . 'assets/js/rbm-faculty.js', [], file_exists($js_path) ? filemtime($js_path) : false, true);
    ?>
    <div id="teachers">
    <?php if ($rbm_teachers_heading !== '') : ?>
        <h2 class="msch-teacher-results-heading"><?php echo esc_html($rbm_teachers_heading); ?></h2>
    <?php endif; ?>
    <?php if ($rbm_is_filtered) : ?>
        <p class="msch-teacher-signup-cta"><a class="thesis-cta-button" href="<?php echo esc_url(rbm_msch_signup_url()); ?>">Sign Up</a></p>
    <?php endif; ?>
    <?php if (!$rbm_is_filtered && !empty($filter_terms)) : ?>
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
    <?php if (!$rbm_is_filtered) : ?>
        <p class="msch-teacher-signup-cta"><a class="thesis-cta-button" href="<?php echo esc_url(rbm_msch_signup_url()); ?>">Sign Up</a></p>
    <?php endif; ?>
    <?php if ($rbm_is_filtered) : ?>
        <?php if ($rbm_fallback_note !== '') : ?>
            <p class="msch-teacher-fallback-note"><em><?php echo esc_html($rbm_fallback_note); ?></em></p>
        <?php endif; ?>
        <p class="msch-teacher-view-all"><a href="<?php echo esc_url(remove_query_arg(['instrument', 'category'])); ?>">View All Faculty</a></p>
    <?php endif; ?>
    <div class="thesis-lesson-card-grid">
        <?php foreach ($teachers as $teacher) : ?>
            <?php echo rbm_msch_teacher_card_html($teacher); ?>
        <?php endforeach; ?>
    </div>
    </div><!-- #teachers -->
    <?php
    return ob_get_clean();
}

// Single Teacher-card renderer, shared by the [msch_teachers] compact grid above and the
// standalone [rbm_teacher] shortcode below (docs/0917-1034-Copilot-REQUEST-Implement-Reusable-
// RBM-Shortcodes-And-Copy-Buttons.txt) — exactly one Teacher-card template.
function rbm_msch_teacher_card_html($teacher) {
    $instruments = get_the_terms($teacher->ID, 'msch_instrument');
    $instrument_slugs = (is_array($instruments) && !is_wp_error($instruments))
        ? wp_list_pluck($instruments, 'slug')
        : [];
    $teaches = trim((string) get_post_meta($teacher->ID, '_msch_teaches', true));
    $profile_url = get_permalink($teacher->ID);
    ob_start();
    ?>
    <div class="thesis-lesson-card thesis-teacher-card" data-msch-instruments="<?php echo esc_attr(implode(',', $instrument_slugs)); ?>">
        <a class="thesis-teacher-card-link" href="<?php echo esc_url($profile_url); ?>">
            <span class="thesis-lesson-card-image"><?php echo rbm_msch_render_card_photo($teacher->ID); ?></span>
            <span class="thesis-lesson-card-title"><?php echo esc_html(get_the_title($teacher)); ?></span>
            <?php if ($teaches !== '') : ?>
                <span class="thesis-lesson-card-line thesis-teacher-card-instrument"><?php echo esc_html($teaches); ?></span>
            <?php endif; ?>
        </a>
        <div class="thesis-teacher-card-actions">
            <a class="thesis-teacher-card-view-profile" href="<?php echo esc_url($profile_url); ?>">View Profile</a>
            <a class="thesis-cta-button" href="<?php echo esc_url(rbm_msch_signup_url()); ?>">Sign Up</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// docs/0917-1034-Copilot-REQUEST-Implement-Reusable-RBM-Shortcodes-And-Copy-Buttons.txt: single
// reusable Teacher card for manual placement on any Avada page/content area (Text Block, Shortcode
// element, etc.). Fails silently for a missing/unpublished Teacher — no PHP warnings/notices.
add_shortcode('rbm_teacher', 'rbm_teacher_shortcode');
function rbm_teacher_shortcode($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'rbm_teacher');
    $teacher = get_post((int) $atts['id']);
    if (!$teacher || $teacher->post_type !== 'msch_teacher' || $teacher->post_status !== 'publish') {
        return '';
    }
    return '<div class="thesis-lesson-card-grid">' . rbm_msch_teacher_card_html($teacher) . '</div>';
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
