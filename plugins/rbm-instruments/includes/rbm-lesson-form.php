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
        'Add/Edit Instrument',
        'Add/Edit Instrument',
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
        wp_die('Instrument not found.');
    }

    // docs/0919-1305-Copilot-REQUEST-Add-Instrument-Slug-Duplicate-Checker.txt: one-time redisplay
    // of a blocked (duplicate-slug) submission. The transient is deleted immediately (single use)
    // so a later reload of this same URL just shows the normal form again.
    $conflict = null;
    $redisplay = null;
    if (!empty($_GET['slug_conflict'])) {
        $transient_key = 'rbm_lesson_slug_conflict_' . get_current_user_id();
        $stored = get_transient($transient_key);
        if (is_array($stored)) {
            delete_transient($transient_key);
            $conflict  = $stored['conflict'];
            $redisplay = $stored['submitted'];
        }
    }

    $title          = $post ? $post->post_title : '';
    $is_active      = $post ? ($post->post_status === 'publish') : true;
    $instrument_txt = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_instrument_text', true) : '';
    $signup_url     = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_signup_url', true) : '';
    // docs/0919-1126-Copilot-REQUEST-Add-Global-And-Custom-Sign-Up-URL-Modes.txt: safe-upgrade rule
    // for a pre-existing record with no saved mode yet — a non-empty Sign Up URL is treated as
    // Custom (preserving current behavior); otherwise Global. A brand-new record has no URL, so it
    // defaults to Global.
    $signup_mode    = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_signup_mode', true) : '';
    if ($signup_mode !== 'global' && $signup_mode !== 'custom') {
        $signup_mode = ($signup_url !== '') ? 'custom' : 'global';
    }
    $global_signup_url = rbm_msch_lesson_global_signup_url();
    $external_id    = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_external_id', true) : '';
    $memo           = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_admin_memo', true) : '';
    $image_attachment_id = $lesson_id ? (int) get_post_meta($lesson_id, '_msch_lesson_image_attachment_id', true) : 0;
    $image_alt      = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_image_alt', true) : '';
    $display_order  = $lesson_id ? get_post_meta($lesson_id, '_msch_lesson_display_order', true) : '';
    $selected_terms = $lesson_id ? wp_get_post_terms($lesson_id, 'msch_instrument', ['fields' => 'ids']) : [];
    $all_terms      = get_terms(['taxonomy' => 'msch_instrument', 'hide_empty' => false]);
    // Each Instrument may have only one normal Category (plus optionally Instruments We Teach);
    // a radio group enforces that at entry. Pre-existing data never has more than one (enforced
    // on every save), so taking the first match here is just a defensive fallback, not a real choice.
    $selected_normal_term_id = 0;
    foreach ($selected_terms as $term_id) {
        if (!rbm_msch_category_is_direct_display($term_id)) {
            $selected_normal_term_id = (int) $term_id;
            break;
        }
    }

    // A blocked submission's values take priority over whatever is saved in the database, so the
    // user never has to re-enter anything after a duplicate-slug warning.
    if ($redisplay) {
        $title                   = $redisplay['title'];
        $is_active               = $redisplay['is_active'];
        $instrument_txt          = $redisplay['instrument_txt'];
        $signup_url              = $redisplay['signup_url'];
        $signup_mode             = $redisplay['signup_mode'];
        $external_id             = $redisplay['external_id'];
        $memo                    = $redisplay['memo'];
        $image_attachment_id     = $redisplay['image_attachment_id'];
        $image_alt               = $redisplay['image_alt'];
        $display_order           = $redisplay['display_order'];
        $selected_normal_term_id = $redisplay['normal_term_id'];
        $selected_terms          = array_merge([$redisplay['normal_term_id']], $redisplay['iwt_term_ids']);
    }
    $slug = $redisplay ? $redisplay['slug'] : ($lesson_id ? get_post_field('post_name', $lesson_id) : '');
    ?>
    <div class="wrap">
        <h1><?php echo $lesson_id ? 'Edit Instrument' : 'Add Instrument'; ?></h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Instrument saved.</p></div>
        <?php endif; ?>
        <?php if ($conflict) : ?>
            <div class="notice notice-error" id="rbm-slug-conflict-notice">
                <p>
                    The slug "<?php echo esc_html($conflict['slug']); ?>" is already used by the Instrument "<?php echo esc_html($conflict['title']); ?>." Use the existing Instrument or enter a different slug before saving.
                    <a href="<?php echo esc_url($conflict['edit_url']); ?>" target="_blank" rel="noopener noreferrer">Edit Existing Instrument</a>
                </p>
            </div>
            <dialog id="rbm-slug-conflict-dialog" aria-labelledby="rbm-slug-conflict-title" style="max-width:420px;padding:20px;">
                <p id="rbm-slug-conflict-title"><strong>Duplicate Instrument slug</strong></p>
                <p>The slug "<?php echo esc_html($conflict['slug']); ?>" is already used by the Instrument "<?php echo esc_html($conflict['title']); ?>." Use the existing Instrument or enter a different slug before saving.</p>
                <p>
                    <a class="button" href="<?php echo esc_url($conflict['edit_url']); ?>" target="_blank" rel="noopener noreferrer">Edit Existing Instrument</a>
                    <button type="button" class="button button-primary" id="rbm-slug-conflict-return">Return and Change Slug</button>
                </p>
            </dialog>
        <?php endif; ?>
        <p><button type="submit" form="rbm-lesson-form" class="button button-primary">Save Instrument</button> <a href="<?php echo esc_url(admin_url('edit.php?post_type=msch_lesson')); ?>" class="button">Cancel</a></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="rbm-lesson-form" style="max-width:640px;">
            <input type="hidden" name="action" value="rbm_save_lesson_form">
            <input type="hidden" name="lesson_id" value="<?php echo esc_attr($lesson_id); ?>">
            <?php wp_nonce_field('rbm_save_lesson_meta', 'rbm_lesson_meta_nonce'); ?>

            <p>
                <label for="post_title"><strong>Instrument Name</strong></label><br>
                <input type="text" id="post_title" name="post_title" class="widefat" value="<?php echo esc_attr($title); ?>" required>
            </p>

            <p>
                <label for="rbm_lesson_slug"><strong>Slug</strong></label><br>
                <input type="text" id="rbm_lesson_slug" name="rbm_lesson_slug" class="widefat" value="<?php echo esc_attr($slug); ?>" placeholder="<?php echo esc_attr($lesson_id ? $slug : 'auto-generated from name if left blank'); ?>">
                <span class="description">Used in the public Instrument URL and Sign Up links (e.g. <code>?instrument=<?php echo esc_html($slug !== '' ? $slug : 'your-slug'); ?></code>). Must be unique among Instruments; leave blank on a new Instrument to auto-generate one from its Name.</span>
            </p>

            <p>
                <label for="rbm_msch_lesson_instrument_text"><strong>Instrument Text</strong></label><br>
                <input type="text" id="rbm_msch_lesson_instrument_text" name="rbm_msch_lesson_instrument_text" class="widefat" value="<?php echo esc_attr($instrument_txt); ?>" placeholder="e.g. Violin, Viola, Fiddle">
            </p>

            <p>
                <strong>Category</strong> <span class="description">(one only; use INSTRUMENTS WE TEACH below in addition if needed)</span><br>
                <label style="display:inline-block;margin:2px 12px 2px 0;">
                    <input type="radio" name="rbm_category" value="" <?php checked($selected_normal_term_id === 0); ?>>
                    <em>&#8212; None &#8212;</em>
                </label>
                <?php foreach ($all_terms as $term) :
                    if (rbm_msch_category_is_direct_display($term->term_id)) {
                        continue; // shown in its own separated section below (docs/0912-1751-...), same relationship
                    }
                ?>
                    <label style="display:inline-block;margin:2px 12px 2px 0;">
                        <input type="radio" name="rbm_category" value="<?php echo (int) $term->term_id; ?>" <?php checked($selected_normal_term_id === $term->term_id); ?>>
                        <?php echo esc_html($term->name); ?>
                    </label>
                <?php endforeach; ?>
                <br>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=msch_instrument&post_type=msch_lesson')); ?>">Add New Category</a>
            </p>
            <?php
            $direct_display_terms = array_filter($all_terms, function ($term) {
                return rbm_msch_category_is_direct_display($term->term_id);
            });
            if (!empty($direct_display_terms)) : ?>
            <p style="border-top:1px solid #ddd;padding-top:12px;margin-top:12px;">
                <strong>INSTRUMENTS WE TEACH</strong><br>
                <?php foreach ($direct_display_terms as $term) : ?>
                    <label style="display:inline-block;margin:2px 12px 2px 0;">
                        <input type="checkbox" name="rbm_categories_iwt[]" value="<?php echo (int) $term->term_id; ?>" <?php checked(in_array($term->term_id, $selected_terms, true)); ?>>
                        INSTRUMENTS WE TEACH
                    </label>
                <?php endforeach; ?>
            </p>
            <?php endif; ?>

            <p>
                <strong>Instrument Tile Image</strong> (Media Library)<br>
                <div id="rbm_lesson_image_preview"><?php echo $image_attachment_id ? wp_get_attachment_image($image_attachment_id, 'thumbnail') : ''; ?></div>
                <input type="hidden" id="rbm_lesson_image_attachment_id" name="rbm_lesson_image_attachment_id" value="<?php echo esc_attr($image_attachment_id); ?>">
                <button type="button" class="button rbm-media-picker" data-target="rbm_lesson_image_attachment_id" data-preview="rbm_lesson_image_preview"><?php echo $image_attachment_id ? 'Replace Image' : 'Select Image'; ?></button>
                <button type="button" class="button rbm-media-remove" data-target="rbm_lesson_image_attachment_id" data-preview="rbm_lesson_image_preview" <?php echo $image_attachment_id ? '' : 'style="display:none;"'; ?>>Remove Image</button>
                <span class="description" style="display:block;margin-top:4px;">Selected from the Media Library. The tile crops this image the same way on the live Instruments page.</span>
            </p>

            <p>
                <label for="rbm_msch_lesson_image_alt"><strong>Image Alt Text</strong></label><br>
                <input type="text" id="rbm_msch_lesson_image_alt" name="rbm_msch_lesson_image_alt" class="widefat" value="<?php echo esc_attr($image_alt); ?>" placeholder="e.g. Violin lessons at Red Barn Music School">
                <span class="description">Public-facing image <code>alt</code> text only (never shown as visible card text). Leave blank to use "{Instrument Name} lessons at Red Barn Music School" automatically.</span>
            </p>

            <?php if ($lesson_id) : ?>
                <?php rbm_lessons_render_copy_shortcode_field('Instrument Shortcode', '[rbm_instrument id="' . $lesson_id . '"]', 'rbm-instrument-shortcode-field'); ?>
            <?php endif; ?>

            <p>
                <strong>Sign Up Link</strong><br>
                <label><input type="radio" name="rbm_lesson_signup_mode" value="global" <?php checked($signup_mode, 'global'); ?>> Use global URL</label><br>
                <label><input type="radio" name="rbm_lesson_signup_mode" value="custom" <?php checked($signup_mode, 'custom'); ?>> Use custom URL</label>
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
                <label for="rbm_msch_lesson_signup_url"><strong>Custom URL</strong></label><br>
                <input type="url" id="rbm_msch_lesson_signup_url" name="rbm_msch_lesson_signup_url" class="widefat" value="<?php echo esc_attr($signup_url); ?>" placeholder="https://">
                <span class="description">Only used when "Use custom URL" is selected above. Switching to Global does not erase this saved value.</span>
            </p>

            <p>
                <label for="rbm_lesson_display_order"><strong>Display Order</strong></label><br>
                <input type="number" step="1" id="rbm_lesson_display_order" name="rbm_lesson_display_order" value="<?php echo esc_attr($display_order); ?>">
                <span class="description">Optional. Only used when Instrument Tile Order is set to Display Order (Instruments Page Display settings). Lower numbers appear first; ties and blank values fall back to alphabetical, with blank always last.</span>
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

            <p><?php submit_button('Save Instrument', 'primary', 'submit', false); ?> <a href="<?php echo esc_url(admin_url('edit.php?post_type=msch_lesson')); ?>" class="button">Cancel</a></p>
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
    $title = sanitize_text_field(wp_unslash($_POST['post_title'] ?? ''));

    // docs/0919-1305-Copilot-REQUEST-Add-Instrument-Slug-Duplicate-Checker.txt: the submitted Slug
    // field is authoritative; left blank (new Instrument), it falls back to the sanitized title,
    // same starting point WordPress's own default slug generation would use.
    $submitted_slug = sanitize_title(wp_unslash($_POST['rbm_lesson_slug'] ?? ''));
    if ($submitted_slug === '') {
        $submitted_slug = sanitize_title($title);
    }

    $conflict_post = rbm_msch_lesson_find_slug_conflict($submitted_slug, $lesson_id);
    if ($conflict_post) {
        // Blocks the save entirely — no wp_insert_post()/wp_update_post() call happens below, so
        // WordPress never gets a chance to silently suffix the slug (e.g. piano-2).
        rbm_msch_lesson_store_slug_conflict_and_redirect($lesson_id, $conflict_post, $submitted_slug, $title, $desired_status);
    }

    $postarr = [
        'post_type'   => 'msch_lesson',
        'post_title'  => $title,
        'post_name'   => $submitted_slug,
        'post_status' => $desired_status,
    ];

    if ($lesson_id) {
        $postarr['ID'] = $lesson_id;
        wp_update_post($postarr);
    } else {
        $lesson_id = wp_insert_post($postarr);
    }

    if ($lesson_id && !is_wp_error($lesson_id)) {
        // Enforce: at most one normal Category, plus optionally Instruments We Teach. The radio
        // group already limits this at entry; re-checked here (is_direct_display()) in case a
        // submitted value doesn't match what it claims to be.
        $term_ids = [];
        $normal_term_id = isset($_POST['rbm_category']) ? (int) $_POST['rbm_category'] : 0;
        if ($normal_term_id > 0 && !rbm_msch_category_is_direct_display($normal_term_id)) {
            $term_ids[] = $normal_term_id;
        }
        $iwt_term_ids = isset($_POST['rbm_categories_iwt']) ? array_map('intval', (array) $_POST['rbm_categories_iwt']) : [];
        foreach ($iwt_term_ids as $iwt_term_id) {
            if ($iwt_term_id > 0 && rbm_msch_category_is_direct_display($iwt_term_id)) {
                $term_ids[] = $iwt_term_id;
            }
        }
        wp_set_post_terms($lesson_id, $term_ids, 'msch_instrument', false);

        update_post_meta($lesson_id, '_msch_lesson_instrument_text', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_instrument_text'] ?? '')));
        update_post_meta($lesson_id, '_msch_lesson_signup_url', esc_url_raw(wp_unslash($_POST['rbm_msch_lesson_signup_url'] ?? '')));
        $signup_mode = (($_POST['rbm_lesson_signup_mode'] ?? '') === 'custom') ? 'custom' : 'global';
        update_post_meta($lesson_id, '_msch_lesson_signup_mode', $signup_mode);
        update_post_meta($lesson_id, '_msch_lesson_external_id', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_external_id'] ?? '')));
        update_post_meta($lesson_id, '_msch_lesson_admin_memo', sanitize_textarea_field(wp_unslash($_POST['rbm_msch_lesson_admin_memo'] ?? '')));

        $image_attachment_id = (int) ($_POST['rbm_lesson_image_attachment_id'] ?? 0);
        if ($image_attachment_id > 0 && wp_attachment_is_image($image_attachment_id)) {
            update_post_meta($lesson_id, '_msch_lesson_image_attachment_id', $image_attachment_id);
        } else {
            delete_post_meta($lesson_id, '_msch_lesson_image_attachment_id');
        }

        update_post_meta($lesson_id, '_msch_lesson_image_alt', sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_image_alt'] ?? '')));

        $display_order_raw = trim((string) wp_unslash($_POST['rbm_lesson_display_order'] ?? ''));
        if ($display_order_raw === '' || !is_numeric($display_order_raw)) {
            delete_post_meta($lesson_id, '_msch_lesson_display_order');
        } else {
            update_post_meta($lesson_id, '_msch_lesson_display_order', (int) $display_order_raw);
        }
    }

    wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form&lesson_id=' . (int) $lesson_id . '&saved=1'));
    exit;
}

// Authoritative server-side duplicate check (docs/0919-1305-Copilot-REQUEST-Add-Instrument-Slug-
// Duplicate-Checker.txt): another non-trashed msch_lesson record already using this exact slug.
// 'publish'/'draft' are the only two statuses this form ever sets (Active/Inactive); trash is
// excluded simply by not including it.
function rbm_msch_lesson_find_slug_conflict($slug, $exclude_lesson_id) {
    if ($slug === '') {
        return null;
    }
    $args = [
        'post_type'      => 'msch_lesson',
        'name'           => $slug,
        'post_status'    => ['publish', 'draft'],
        'posts_per_page' => 1,
    ];
    if ($exclude_lesson_id) {
        $args['post__not_in'] = [$exclude_lesson_id];
    }
    $matches = get_posts($args);
    return !empty($matches) ? $matches[0] : null;
}

// Stores the submitted (unsaved) form values plus the conflicting Instrument's details in a short-
// lived, per-user transient, then redirects back to the same Add/Edit Instrument screen so it can
// redisplay everything and show the warning. No database write happens for a blocked save.
function rbm_msch_lesson_store_slug_conflict_and_redirect($lesson_id, $conflict_post, $slug, $title, $desired_status) {
    $submitted = [
        'title'               => $title,
        'is_active'           => ($desired_status === 'publish'),
        'slug'                => $slug,
        'instrument_txt'      => sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_instrument_text'] ?? '')),
        'signup_url'          => esc_url_raw(wp_unslash($_POST['rbm_msch_lesson_signup_url'] ?? '')),
        'signup_mode'         => (($_POST['rbm_lesson_signup_mode'] ?? '') === 'custom') ? 'custom' : 'global',
        'external_id'         => sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_external_id'] ?? '')),
        'memo'                => sanitize_textarea_field(wp_unslash($_POST['rbm_msch_lesson_admin_memo'] ?? '')),
        'image_attachment_id' => (int) ($_POST['rbm_lesson_image_attachment_id'] ?? 0),
        'image_alt'           => sanitize_text_field(wp_unslash($_POST['rbm_msch_lesson_image_alt'] ?? '')),
        'display_order'       => trim((string) wp_unslash($_POST['rbm_lesson_display_order'] ?? '')),
        'normal_term_id'      => isset($_POST['rbm_category']) ? (int) $_POST['rbm_category'] : 0,
        'iwt_term_ids'        => isset($_POST['rbm_categories_iwt']) ? array_map('intval', (array) $_POST['rbm_categories_iwt']) : [],
    ];
    $conflict = [
        'title'    => $conflict_post->post_title,
        'ID'       => $conflict_post->ID,
        'status'   => $conflict_post->post_status,
        'slug'     => $slug,
        'edit_url' => admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form&lesson_id=' . $conflict_post->ID),
    ];
    set_transient('rbm_lesson_slug_conflict_' . get_current_user_id(), ['submitted' => $submitted, 'conflict' => $conflict], 60);

    $redirect_url = admin_url('edit.php?post_type=msch_lesson&page=rbm-lesson-form&slug_conflict=1');
    if ($lesson_id) {
        $redirect_url .= '&lesson_id=' . (int) $lesson_id;
    }
    wp_safe_redirect($redirect_url);
    exit;
}

// Media Library picker JS (Instrument Tile Image + Category Image fields) and the Copy Shortcode
// clipboard JS below now live in assets/js/rbm-instruments-admin.js, enqueued centrally by
// rbm_instruments_admin_assets() in includes/rbm-lessons.php (docs/0917-1053-PLAN-Extract-Inline-
// JS-CSS-From-RBM-Plugins.txt).

// Read-only Copy Shortcode control (docs/0917-1034-Copilot-REQUEST-Implement-Reusable-RBM-
// Shortcodes-And-Copy-Buttons.txt) — reused by the Instrument edit form above and the Category
// edit screen (rbm-lessons.php); same copy-to-clipboard pattern as the existing Instruments ->
// Settings "Copy List" control.
function rbm_lessons_render_copy_shortcode_field($label, $shortcode, $field_id) {
    ?>
    <p>
        <?php if ($label !== '') : ?><strong><?php echo esc_html($label); ?></strong><br><?php endif; ?>
        <input type="text" id="<?php echo esc_attr($field_id); ?>" class="widefat" readonly onclick="this.select();" value="<?php echo esc_attr($shortcode); ?>" style="max-width:360px;font-family:monospace;">
        <button type="button" class="button rbm-copy-shortcode" data-copy-target="<?php echo esc_attr($field_id); ?>">Copy Shortcode</button>
        <span class="rbm-copy-shortcode-status" style="margin-left:8px;"></span>
    </p>
    <?php
}
