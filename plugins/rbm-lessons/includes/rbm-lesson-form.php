<?php
/**
 * Standalone simplified Lesson Add/Edit form (Stage 3 of docs/0909-1310-PLAN-Lessons-Instruments-Module-Staged-Reuse-Plan.txt).
 * Mirrors plugins/rbm-faculty/includes/rbm-teacher-form.php. No public output.
 */

// Redirect the native post-new.php/post.php edit routes for msch_lesson to our standalone form.
add_action('admin_init', 'rbm_lesson_form_redirect_native_editor');
function rbm_lesson_form_redirect_native_editor() {
    $script = basename($_SERVER['PHP_SELF']);
    if ($script === 'post-new.php' && ($_GET['post_type'] ?? '') === 'msch_lesson') {
        wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form'));
        exit;
    }
    if ($script === 'post.php' && ($_GET['action'] ?? '') === 'edit' && isset($_GET['post'])) {
        $post_id = (int) $_GET['post'];
        if (get_post_type($post_id) === 'msch_lesson') {
            wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form&lesson_id=' . $post_id));
            exit;
        }
    }
}

add_action('admin_menu', 'rbm_add_lesson_form_page', 20);
function rbm_add_lesson_form_page() {
    add_submenu_page(
        'edit.php?post_type=msch_lesson',
        'Add/Edit Lesson',
        'Add/Edit Lesson',
        'edit_posts',
        'rbm-lesson-form',
        'rbm_render_lesson_form_page'
    );
    // Keep the page reachable but out of the visible submenu (native "Add Lesson" link already redirects here).
    add_action('admin_menu', function () {
        remove_submenu_page('edit.php?post_type=msch_lesson', 'rbm-lesson-form');
    }, 999);
}

// Hide unrelated WP/plugin admin notices on this page only; the form's own saved markup
// is printed directly in rbm_render_lesson_form_page(), not via admin_notices, so it is unaffected.
add_action('admin_init', 'rbm_lesson_form_suppress_notices');
function rbm_lesson_form_suppress_notices() {
    if ((sanitize_key(wp_unslash($_GET['page'] ?? ''))) !== 'rbm-lesson-form') {
        return;
    }
    add_action('in_admin_header', function () {
        remove_all_actions('user_admin_notices');
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }, 1);
}

function rbm_render_lesson_form_page() {
    if (!current_user_can('edit_posts')) {
        return;
    }
    $lesson_id = isset($_GET['lesson_id']) ? (int) $_GET['lesson_id'] : 0;
    $post = $lesson_id ? get_post($lesson_id) : null;
    if ($lesson_id && (!$post || $post->post_type !== 'msch_lesson')) {
        wp_die('Lesson not found.');
    }

    $title          = $post ? $post->post_title : '';
    $is_active      = $post ? ($post->post_status === 'publish') : true;
    $instrument_txt = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_instrument_text', true) : '';
    $signup_url     = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_signup_url', true) : '';
    $external_id    = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_external_id', true) : '';
    $memo           = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_admin_memo', true) : '';
    $image_filename = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_image_filename', true) : '';
    $image_alt      = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_image_alt', true) : '';
    $catalog_images = rbm_msch_lesson_catalog_images();
    $selected_terms = $lesson_id ? wp_get_post_terms($lesson_id, 'msch_instrument', ['fields' => 'ids']) : [];
    $all_terms      = get_terms(['taxonomy' => 'msch_instrument', 'hide_empty' => false]);
    ?>
    <div class="wrap">
        <h1><?php echo $lesson_id ? 'Edit Lesson' : 'Add Lesson'; ?></h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Lesson saved.</p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:640px;">
            <input type="hidden" name="action" value="rbm_save_lesson_form">
            <input type="hidden" name="lesson_id" value="<?php echo esc_attr($lesson_id); ?>">
            <?php wp_nonce_field('rbm_save_lesson_meta', 'rbm_lesson_meta_nonce'); ?>

            <p>
                <label for="post_title"><strong>Lesson / Instrument Name</strong></label><br>
                <input type="text" id="post_title" name="post_title" class="widefat" value="<?php echo esc_attr($title); ?>" required>
            </p>

            <p>
                <label for="rbm_msch_lesson_instrument_text"><strong>Instrument Text</strong></label><br>
                <input type="text" id="rbm_msch_lesson_instrument_text" name="rbm_msch_lesson_instrument_text" class="widefat" value="<?php echo esc_attr($instrument_txt); ?>" placeholder="e.g. Violin, Viola, Fiddle">
            </p>

            <p>
                <strong>Category</strong><br>
                <?php foreach ($all_terms as $term) : ?>
                    <label style="display:inline-block;margin:2px 12px 2px 0;">
                        <input type="checkbox" name="rbm_categories[]" value="<?php echo (int) $term->term_id; ?>" <?php checked(in_array($term->term_id, $selected_terms, true)); ?>>
                        <?php echo esc_html($term->name); ?>
                    </label>
                <?php endforeach; ?>
                <br>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=msch_instrument&post_type=msch_lesson')); ?>">Add New Category</a>
            </p>

            <p>
                <label for="rbm_lesson_image_filename"><strong>Lesson Tile Image</strong></label><br>
                <select id="rbm_lesson_image_filename" name="rbm_lesson_image_filename" class="widefat">
                    <option value=""<?php selected($image_filename, ''); ?>>No Image</option>
                    <?php foreach ($catalog_images as $file) : ?>
                        <option value="<?php echo esc_attr($file); ?>"<?php selected($image_filename, $file); ?>><?php echo esc_html($file); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php $preview_url = rbm_msch_lesson_catalog_image_url($image_filename); ?>
                <div style="margin-top:8px;">
                    <strong style="display:block;margin-bottom:4px;">Tile Preview</strong>
                    <div style="display:inline-flex;flex-direction:column;width:220px;border:1px solid #ddd;border-radius:8px;overflow:hidden;background:#fff;">
                        <span id="rbm_lesson_image_preview" style="display:block;width:100%;aspect-ratio:1/1;overflow:hidden;background:#f0f0f0;">
                            <?php if ($preview_url !== '') : ?>
                                <img src="<?php echo esc_url($preview_url); ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
                            <?php endif; ?>
                        </span>
                        <span id="rbm_lesson_title_preview" style="display:block;padding:12px 16px;font-size:16px;"><?php echo esc_html($title); ?></span>
                    </div>
                </div>
                <span class="description">Populated from <code>wp-content/plugins/rbm-lessons/assets/images/</code> — add files there to expand this list. The preview above crops the same way the tile does on the live Lessons page, so you can catch images that get cut off before saving.</span>
                <script>
                (function(){
                    var urls = <?php echo wp_json_encode(array_combine($catalog_images, array_map('rbm_msch_lesson_catalog_image_url', $catalog_images))); ?>;
                    document.getElementById('rbm_lesson_image_filename').addEventListener('change', function (e) {
                        var preview = document.getElementById('rbm_lesson_image_preview');
                        var url = urls[e.target.value];
                        preview.innerHTML = url ? '<img src="' + url + '" style="display:block;width:100%;height:100%;object-fit:cover;">' : '';
                    });
                    document.getElementById('post_title').addEventListener('input', function (e) {
                        document.getElementById('rbm_lesson_title_preview').textContent = e.target.value;
                    });
                })();
                </script>
            </p>

            <p>
                <label for="rbm_msch_lesson_image_alt"><strong>Image Alt Text</strong></label><br>
                <input type="text" id="rbm_msch_lesson_image_alt" name="rbm_msch_lesson_image_alt" class="widefat" value="<?php echo esc_attr($image_alt); ?>" placeholder="e.g. Violin lessons at Red Barn Music School">
                <span class="description">Public-facing image <code>alt</code> text only (never shown as visible card text). Leave blank to use "{Lesson Name} lessons at Red Barn Music School" automatically.</span>
            </p>

            <p>
                <label for="rbm_msch_lesson_signup_url"><strong>Sign Up URL</strong></label><br>
                <input type="url" id="rbm_msch_lesson_signup_url" name="rbm_msch_lesson_signup_url" class="widefat" value="<?php echo esc_attr($signup_url); ?>" placeholder="https://">
            </p>

            <p>
                <strong>Status</strong><br>
                <label><input type="radio" name="rbm_lesson_status" value="active" <?php checked($is_active); ?>> Active</label>
                &nbsp;&nbsp;
                <label><input type="radio" name="rbm_lesson_status" value="inactive" <?php checked(!$is_active); ?>> Inactive</label>
            </p>

            <div>
                <p><strong>Admin Info</strong> <em>(private, never shown publicly)</em></p>
                <p>
                    <label for="rbm_msch_lesson_external_id">External / RBAdmin ID</label><br>
                    <input type="text" id="rbm_msch_lesson_external_id" name="rbm_msch_lesson_external_id" class="widefat" value="<?php echo esc_attr($external_id); ?>">
                </p>
                <p>
                    <label for="rbm_msch_lesson_admin_memo">Admin Memo</label><br>
                    <textarea id="rbm_msch_lesson_admin_memo" name="rbm_msch_lesson_admin_memo" class="widefat" rows="3"><?php echo esc_textarea($memo); ?></textarea>
                </p>
            </div>

            <p><?php submit_button('Save Lesson', 'primary', 'submit', false); ?></p>
        </form>
    </div>
    <?php
}

add_action('admin_post_rbm_save_lesson_form', 'rbm_save_lesson_form_submit');
function rbm_save_lesson_form_submit() {
    if (!isset($_POST['rbm_lesson_meta_nonce']) || !wp_verify_nonce($_POST['rbm_lesson_meta_nonce'], 'rbm_save_lesson_meta')) {
        wp_die('Security check failed.');
    }
    if (!current_user_can('edit_posts')) {
        wp_die('Insufficient permissions.');
    }

    $lesson_id = (int) ($_POST['lesson_id'] ?? 0);
    $desired_status = (($_POST['rbm_lesson_status'] ?? '') === 'active') ? 'publish' : 'draft';
    $postarr = [
        'post_type'   => 'msch_lesson',
        'post_title'  => sanitize_text_field(wp_unslash($_POST['post_title'] ?? '')),
        'post_status' => $desired_status,
    ];

    if ($lesson_id) {
        $postarr['ID'] = $lesson_id;
        wp_update_post($postarr);
    } else {
        $lesson_id = wp_insert_post($postarr);
    }

    if ($lesson_id && !is_wp_error($lesson_id)) {
        $term_ids = isset($_POST['rbm_categories']) ? array_map('intval', (array) $_POST['rbm_categories']) : [];
        wp_set_post_terms($lesson_id, $term_ids, 'msch_instrument', false);

        update_post_meta($lesson_id, '_msch_lesson_instrument_text', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_instrument_text'] ?? '')));
        update_post_meta($lesson_id, '_msch_lesson_signup_url', esc_url_raw(wp_unslash($_POST['rbm_msch_lesson_signup_url'] ?? '')));
        update_post_meta($lesson_id, '_msch_lesson_external_id', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_external_id'] ?? '')));
        update_post_meta($lesson_id, '_msch_lesson_admin_memo', sanitize_textarea_field(wp_unslash($_POST['rbm_msch_lesson_admin_memo'] ?? '')));

        $image_filename = basename(sanitize_text_field(wp_unslash($_POST['rbm_lesson_image_filename'] ?? '')));
        if ($image_filename !== '' && in_array($image_filename, rbm_msch_lesson_catalog_images(), true)) {
            update_post_meta($lesson_id, '_msch_lesson_image_filename', $image_filename);
        } else {
            delete_post_meta($lesson_id, '_msch_lesson_image_filename');
        }

        update_post_meta($lesson_id, '_msch_lesson_image_alt', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_image_alt'] ?? '')));
    }

    wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form&lesson_id=' . (int) $lesson_id . '&saved=1'));
    exit;
}

// Lesson Tile Image is now selected from the plugin's assets/images/ catalog (not the Media Library);
// no wp.media enqueue is needed for this form. See rbm_msch_lesson_catalog_images() in rbm-lessons.php.
