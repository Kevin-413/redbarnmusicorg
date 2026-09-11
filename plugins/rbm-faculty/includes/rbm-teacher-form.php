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
    ?>
    <div class="wrap">
        <h1><?php echo $teacher_id ? 'Edit Teacher' : 'Add Teacher'; ?></h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Teacher saved.</p></div>
        <?php endif; ?>
        <p><button type="submit" form="rbm-teacher-form" class="button button-primary">Save Teacher</button></p>
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

            <p><?php submit_button('Save Teacher', 'primary', 'submit', false); ?></p>
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
