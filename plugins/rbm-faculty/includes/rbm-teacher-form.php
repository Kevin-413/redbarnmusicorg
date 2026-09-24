<?php
/**
 * Standalone simplified Teacher Add/Edit form, replacing the Gutenberg editor for msch_teacher.
 * See docs/0908-1326-Copilot-REQUEST-Build-Standalone-Simplified-Teacher-Add-Edit-Form-With-Memo.txt
 * Reuses the existing msch_teacher data model and rbm_save_teacher_meta() save logic (rbm-teachers.php).
 */

// Redirect the native post-new.php/post.php edit routes for msch_teacher to our standalone form.
add_action('admin_init', 'rbm_teacher_form_redirect_native_editor');
function rbm_teacher_form_redirect_native_editor() {
    $script = basename($_SERVER['PHP_SELF']);
    if ($script === 'post-new.php' && ($_GET['post_type'] ?? '') === 'msch_teacher') {
        wp_safe_redirect(admin_url('edit.php?post_type=msch_teacher&page=rbm-teacher-form'));
        exit;
    }
    if ($script === 'post.php' && ($_GET['action'] ?? '') === 'edit' && isset($_GET['post'])) {
        $post_id = (int) $_GET['post'];
        if (get_post_type($post_id) === 'msch_teacher') {
            wp_safe_redirect(admin_url('edit.php?post_type=msch_teacher&page=rbm-teacher-form&teacher_id=' . $post_id));
            exit;
        }
    }
}

add_action('admin_menu', 'rbm_add_teacher_form_page', 20);
function rbm_add_teacher_form_page() {
    $hook = add_submenu_page(
        'edit.php?post_type=msch_teacher',
        'Add/Edit Teacher',
        'Add/Edit Teacher',
        'edit_posts',
        'rbm-teacher-form',
        'rbm_render_teacher_form_page'
    );
    // Keep the page reachable but out of the visible submenu (native "Add Teacher" link already redirects here).
    add_action('admin_menu', function () {
        remove_submenu_page('edit.php?post_type=msch_teacher', 'rbm-teacher-form');
    }, 999);
}

// Hide unrelated WP/plugin admin notices on this page only; the form's own saved/error
// markup is printed directly in rbm_render_teacher_form_page(), not via admin_notices, so it is unaffected.
add_action('admin_init', 'rbm_teacher_form_suppress_notices');
function rbm_teacher_form_suppress_notices() {
    if ((sanitize_key(wp_unslash($_GET['page'] ?? ''))) !== 'rbm-teacher-form') {
        return;
    }
    add_action('in_admin_header', function () {
        remove_all_actions('user_admin_notices');
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }, 1);
}


function rbm_render_teacher_form_page() {
    $teacher_id = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : 0;
    // Editing an existing record requires per-post capability, not just the blanket 'edit_posts'
    // (otherwise any Author/Contributor could view another teacher's private Admin Info fields).
    if ($teacher_id ? !current_user_can('edit_post', $teacher_id) : !current_user_can('edit_posts')) {
        wp_die('Insufficient permissions.');
    }
    $post = $teacher_id ? get_post($teacher_id) : null;
    if ($teacher_id && (!$post || $post->post_type !== 'msch_teacher')) {
        wp_die('Teacher not found.');
    }

    $title       = $post ? $post->post_title : '';
    $is_active   = $post ? ($post->post_status === 'publish') : true;
    $short_bio   = $post ? $post->post_excerpt : '';
    $long_bio    = $post ? $post->post_content : '';
    $website     = $teacher_id ? get_post_meta($teacher_id, '_msch_website', true) : '';
    // docs/0919-1126-Copilot-REQUEST-Add-Global-And-Custom-Sign-Up-URL-Modes.txt: safe-upgrade
    // rule for a pre-existing record with no saved mode yet — a non-empty custom Sign Up URL is
    // treated as Custom (there is no pre-existing per-Teacher URL today, so this only matters going
    // forward); otherwise Global. A brand-new record has no URL, so it defaults to Global.
    $signup_url  = $teacher_id ? get_post_meta($teacher_id, '_msch_teacher_signup_url', true) : '';
    $signup_mode = $teacher_id ? get_post_meta($teacher_id, '_msch_teacher_signup_mode', true) : '';
    if ($signup_mode !== 'global' && $signup_mode !== 'custom') {
        $signup_mode = ($signup_url !== '') ? 'custom' : 'global';
    }
    $global_signup_url = rbm_faculty_global_signup_url();
    $memo        = $teacher_id ? get_post_meta($teacher_id, '_msch_admin_memo', true) : '';
    $email       = $teacher_id ? get_post_meta($teacher_id, '_msch_email', true) : '';
    $phone       = $teacher_id ? get_post_meta($teacher_id, '_msch_phone', true) : '';
    $external_id = $teacher_id ? get_post_meta($teacher_id, '_msch_external_id', true) : '';
    $card_id     = $teacher_id ? (int) get_post_meta($teacher_id, '_msch_card_photo_id', true) : 0;
    $second_photo_id = $teacher_id ? (int) get_post_meta($teacher_id, '_msch_second_profile_photo_id', true) : 0;
    $teaches     = $teacher_id ? get_post_meta($teacher_id, '_msch_teaches', true) : '';
    $portrait_id = $teacher_id ? (int) get_post_thumbnail_id($teacher_id) : 0;
    $selected_terms = $teacher_id ? wp_get_post_terms($teacher_id, 'msch_instrument', ['fields' => 'ids']) : [];
    $all_terms = get_terms(['taxonomy' => 'msch_instrument', 'hide_empty' => false]);
    $selected_filter_instruments = $teacher_id ? rbm_msch_teacher_get_filter_instruments($teacher_id) : [];
    $all_lessons = post_type_exists('msch_lesson') ? get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]) : [];
    ?>
    <div class="wrap">
        <h1><?php echo $teacher_id ? 'Edit Teacher' : 'Add Teacher'; ?></h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Teacher saved.</p></div>
        <?php endif; ?>
        <?php if ($teacher_id) : ?>
            <?php rbm_faculty_render_copy_shortcode_field('Teacher Shortcode', '[rbm_teacher id="' . $teacher_id . '"]', 'rbm-teacher-shortcode-field'); ?>
        <?php endif; ?>
        <p><button type="submit" form="rbm-teacher-form" class="button button-primary">Save Teacher</button> <a href="<?php echo esc_url(admin_url('edit.php?post_type=msch_teacher')); ?>" class="button">Cancel</a></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="rbm-teacher-form" style="max-width:640px;">
            <input type="hidden" name="action" value="rbm_save_teacher_form">
            <input type="hidden" name="teacher_id" value="<?php echo esc_attr($teacher_id); ?>">
            <?php wp_nonce_field('rbm_save_teacher_meta_' . $teacher_id, 'rbm_teacher_meta_nonce'); ?>

            <p>
                <label for="post_title"><strong>Teacher Name</strong></label><br>
                <input type="text" id="post_title" name="post_title" class="widefat" value="<?php echo esc_attr($title); ?>" required>
            </p>

            <p>
                <strong>Status</strong><br>
                <label><input type="radio" name="rbm_teacher_status" value="active" <?php checked($is_active); ?>> Active</label>
                &nbsp;&nbsp;
                <label><input type="radio" name="rbm_teacher_status" value="inactive" <?php checked(!$is_active); ?>> Inactive</label>
            </p>

            <p>
                <strong>Instrument Category</strong><br>
                <?php foreach ($all_terms as $term) : ?>
                    <label style="display:inline-block;margin:2px 12px 2px 0;">
                        <input type="checkbox" name="rbm_instruments[]" value="<?php echo (int) $term->term_id; ?>" <?php checked(in_array($term->term_id, $selected_terms, true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </label>
                <?php endforeach; ?>
                <br>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=msch_instrument&post_type=msch_teacher')); ?>">Add New Instrument</a>
            </p>

            <p>
                <label for="rbm_msch_teaches"><strong>Instruments Taught</strong></label><br>
                <input type="text" id="rbm_msch_teaches" name="rbm_msch_teaches" class="widefat" value="<?php echo esc_attr($teaches); ?>" placeholder="e.g. Violin &amp; Viola">
                <span class="description">Short public display string shown after the teacher's name on their Teacher Profile Page (e.g. "Carol Hutter, Violin &amp; Viola"). Independent from Instrument Category above; not auto-generated.</span>
            </p>

            <p>
                <strong>Filter Instruments</strong><br>
                <?php foreach ($all_lessons as $lesson) : ?>
                    <label style="display:inline-block;margin:2px 12px 2px 0;">
                        <input type="checkbox" name="rbm_filter_instruments[]" value="<?php echo (int) $lesson->ID; ?>" <?php checked(in_array($lesson->ID, $selected_filter_instruments, true)); ?>>
                        <?php echo esc_html(get_the_title($lesson)); ?>
                    </label>
                <?php endforeach; ?>
                <br>
                <span class="description">Used to match this teacher when visitors filter Faculty by a specific Instrument. Automatically filled from the teacher's Faculty category/categories. Edit only when this teacher teaches a different subset.</span>
            </p>

            <p>
                <strong>Portrait Photo</strong> (Teacher Profile Page)<br>
                <div id="rbm_portrait_preview"><?php echo $portrait_id ? wp_get_attachment_image($portrait_id, 'thumbnail') : ''; ?></div>
                <input type="hidden" id="rbm_portrait_photo_id" name="rbm_portrait_photo_id" value="<?php echo esc_attr($portrait_id); ?>">
                <button type="button" class="button rbm-media-picker" data-target="rbm_portrait_photo_id" data-preview="rbm_portrait_preview"><?php echo $portrait_id ? 'Replace Image' : 'Select Image'; ?></button>
                <button type="button" class="button rbm-media-remove" data-target="rbm_portrait_photo_id" data-preview="rbm_portrait_preview" <?php echo $portrait_id ? '' : 'style="display:none;"'; ?>>Remove Image</button>
            </p>

            <p>
                <strong>Second Profile Image</strong> (optional, shown below Portrait Photo on Teacher Profile page)<br>
                <div id="rbm_second_profile_photo_preview"><?php echo $second_photo_id ? wp_get_attachment_image($second_photo_id, 'thumbnail') : ''; ?></div>
                <input type="hidden" id="rbm_msch_second_profile_photo_id" name="rbm_msch_second_profile_photo_id" value="<?php echo esc_attr($second_photo_id); ?>">
                <button type="button" class="button rbm-media-picker" data-target="rbm_msch_second_profile_photo_id" data-preview="rbm_second_profile_photo_preview"><?php echo $second_photo_id ? 'Replace Image' : 'Select Image'; ?></button>
                <button type="button" class="button rbm-media-remove" data-target="rbm_msch_second_profile_photo_id" data-preview="rbm_second_profile_photo_preview" <?php echo $second_photo_id ? '' : 'style="display:none;"'; ?>>Remove Image</button>
            </p>

            <p>
                <strong>Square Card Photo</strong> (Faculty Card)<br>
                <div id="rbm_card_photo_preview"><?php echo $card_id ? wp_get_attachment_image($card_id, 'thumbnail') : ''; ?></div>
                <input type="hidden" id="rbm_msch_card_photo_id" name="rbm_msch_card_photo_id" value="<?php echo esc_attr($card_id); ?>">
                <button type="button" class="button rbm-media-picker" data-target="rbm_msch_card_photo_id" data-preview="rbm_card_photo_preview"><?php echo $card_id ? 'Replace Image' : 'Select Image'; ?></button>
                <button type="button" class="button rbm-media-remove" data-target="rbm_msch_card_photo_id" data-preview="rbm_card_photo_preview" <?php echo $card_id ? '' : 'style="display:none;"'; ?>>Remove Image</button>
            </p>

            <p>
                <label for="post_excerpt"><strong>Short Bio</strong></label><br>
                <textarea id="post_excerpt" name="post_excerpt" class="widefat" rows="3"><?php echo esc_textarea($short_bio); ?></textarea>
            </p>

            <p>
                <label for="post_content"><strong>Long Bio</strong></label><br>
                <textarea id="post_content" name="post_content" class="widefat" rows="8"><?php echo esc_textarea($long_bio); ?></textarea>
            </p>

            <p>
                <label for="rbm_msch_website"><strong>Website / Professional Link</strong></label><br>
                <input type="url" id="rbm_msch_website" name="rbm_msch_website" class="widefat" value="<?php echo esc_attr($website); ?>" placeholder="https://">
            </p>

            <p>
                <strong>Sign Up Link</strong><br>
                <label><input type="radio" name="rbm_teacher_signup_mode" value="global" <?php checked($signup_mode, 'global'); ?>> Use global URL</label><br>
                <label><input type="radio" name="rbm_teacher_signup_mode" value="custom" <?php checked($signup_mode, 'custom'); ?>> Use custom URL</label>
            </p>
            <p>
                <strong>Global URL:</strong>
                <?php if ($global_signup_url !== '') : ?>
                    <a href="<?php echo esc_url($global_signup_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($global_signup_url); ?></a>
                <?php else : ?>
                    <em>Not set — configure on the Instruments Page Display settings page.</em>
                <?php endif; ?>
            </p>
            <p>
                <label for="rbm_msch_teacher_signup_url"><strong>Custom URL</strong></label><br>
                <input type="url" id="rbm_msch_teacher_signup_url" name="rbm_msch_teacher_signup_url" class="widefat" value="<?php echo esc_attr($signup_url); ?>" placeholder="https://">
                <span class="description">Only used when "Use custom URL" is selected above. Switching to Global does not erase this saved value.</span>
            </p>

            <div>
                <p><strong>Admin Info</strong></p>
                <p>
                    <label for="rbm_msch_admin_memo">Comments / Memo <em>(private, never shown publicly)</em></label><br>
                    <textarea id="rbm_msch_admin_memo" name="rbm_msch_admin_memo" class="widefat" rows="3"><?php echo esc_textarea($memo); ?></textarea>
                </p>
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
            </div>

            <p><?php submit_button('Save Teacher', 'primary', 'submit', false); ?> <a href="<?php echo esc_url(admin_url('edit.php?post_type=msch_teacher')); ?>" class="button">Cancel</a></p>
        </form>
    </div>
    <?php
}

add_action('admin_post_rbm_save_teacher_form', 'rbm_save_teacher_form_submit');
function rbm_save_teacher_form_submit() {
    $teacher_id = (int) ($_POST['teacher_id'] ?? 0);
    if (!isset($_POST['rbm_teacher_meta_nonce']) || !wp_verify_nonce($_POST['rbm_teacher_meta_nonce'], 'rbm_save_teacher_meta_' . $teacher_id)) {
        wp_die('Security check failed.');
    }
    // Editing an existing record requires per-post capability, not just the blanket 'edit_posts'
    // (otherwise any Author/Contributor could overwrite another teacher's record).
    if ($teacher_id ? !current_user_can('edit_post', $teacher_id) : !current_user_can('edit_posts')) {
        wp_die('Insufficient permissions.');
    }
    $postarr = [
        'post_type'    => 'msch_teacher',
        'post_title'   => sanitize_text_field(wp_unslash($_POST['post_title'] ?? '')),
        'post_excerpt' => wp_kses_post(wp_unslash($_POST['post_excerpt'] ?? '')),
        'post_content' => wp_kses_post(wp_unslash($_POST['post_content'] ?? '')),
    ];

    if ($teacher_id) {
        $postarr['ID'] = $teacher_id;
        wp_update_post($postarr); // triggers save_post_msch_teacher -> rbm_save_teacher_meta() for status/website/memo/email/phone/external id/card photo
    } else {
        $postarr['post_status'] = 'draft'; // rbm_save_teacher_meta() adjusts to publish immediately if Active was selected
        $teacher_id = wp_insert_post($postarr);
    }

    if ($teacher_id && !is_wp_error($teacher_id)) {
        // Instruments and Portrait Photo are not covered by rbm_save_teacher_meta(); handled here.
        $term_ids = isset($_POST['rbm_instruments']) ? array_map('intval', (array) $_POST['rbm_instruments']) : [];
        wp_set_post_terms($teacher_id, $term_ids, 'msch_instrument', false);

        update_post_meta($teacher_id, '_msch_teaches', sanitize_text_field(wp_unslash($_POST['rbm_msch_teaches'] ?? '')));

        $filter_ids = isset($_POST['rbm_filter_instruments']) ? array_map('intval', (array) $_POST['rbm_filter_instruments']) : [];
        $filter_ids = array_values(array_filter($filter_ids, function ($id) { return get_post_type($id) === 'msch_lesson'; }));
        update_post_meta($teacher_id, '_msch_teacher_filter_instruments', $filter_ids);

        $portrait_id = (int) ($_POST['rbm_portrait_photo_id'] ?? 0);
        if ($portrait_id > 0 && wp_attachment_is_image($portrait_id)) {
            set_post_thumbnail($teacher_id, $portrait_id);
        } else {
            delete_post_thumbnail($teacher_id);
        }
    }

    wp_safe_redirect(admin_url('edit.php?post_type=msch_teacher&page=rbm-teacher-form&teacher_id=' . (int) $teacher_id . '&saved=1'));
    exit;
}

// Read-only Copy Shortcode control (docs/0917-1034-Copilot-REQUEST-Implement-Reusable-RBM-
// Shortcodes-And-Copy-Buttons.txt) — same copy-to-clipboard pattern as the existing Instruments ->
// Settings "Copy List" control. The clipboard JS lives in assets/js/rbm-faculty-admin.js, enqueued
// by rbm_teacher_media_picker_assets() in rbm-teachers.php (docs/0917-1053-PLAN-Extract-Inline-
// JS-CSS-From-RBM-Plugins.txt).
function rbm_faculty_render_copy_shortcode_field($label, $shortcode, $field_id) {
    ?>
    <p>
        <strong><?php echo esc_html($label); ?></strong><br>
        <input type="text" id="<?php echo esc_attr($field_id); ?>" class="widefat" readonly onclick="this.select();" value="<?php echo esc_attr($shortcode); ?>" style="max-width:360px;font-family:monospace;">
        <button type="button" class="button rbm-copy-shortcode" data-copy-target="<?php echo esc_attr($field_id); ?>">Copy Shortcode</button>
        <span class="rbm-copy-shortcode-status" style="margin-left:8px;"></span>
    </p>
    <?php
}
