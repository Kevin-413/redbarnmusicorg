<?php
/**
 * Lessons Page Display admin selector (docs/0912-1751-Copilot-REQUEST-Lessons-Page-Display-Admin-Selector.txt):
 * lets the admin choose which public Lessons landing experience displays, reusing the existing
 * Categories and Instruments We Teach rendering paths in rbm-lessons.php. Display-only - never
 * touches category/instrument assignments or Direct Display term meta.
 */

define('RBM_LESSONS_DISPLAY_MODE_OPTION', 'rbm_lessons_display_mode');

// '' | 'categories' | 'instruments' | 'none'. '' means no explicit choice has been saved yet,
// which preserves the pre-existing combined (Categories + Instruments We Teach) behavior untouched.
function rbm_lessons_get_display_mode() {
    $mode = get_option(RBM_LESSONS_DISPLAY_MODE_OPTION, '');
    return in_array($mode, ['categories', 'instruments', 'none'], true) ? $mode : '';
}

// docs/0913-1010-Copilot-REQUEST-Add-Choose-Instrument-Pill-Display-Option.txt: independent
// Show/Hide control for the green "Choose Instrument" pill/button, decoupled from the display
// mode above. Default 'hide' preserves the pill's existing forced-hidden CSS behavior
// (.msch-lesson-filter{display:none} in rbm-lessons.php) unless the admin explicitly picks Show.
define('RBM_INSTRUMENTS_CHOOSE_INSTRUMENT_PILL_OPTION', 'rbm_instruments_choose_instrument_pill');

function rbm_instruments_get_choose_instrument_pill_mode() {
    $mode = get_option(RBM_INSTRUMENTS_CHOOSE_INSTRUMENT_PILL_OPTION, 'hide');
    return in_array($mode, ['show', 'hide'], true) ? $mode : 'hide';
}

// docs/0913-1208-Copilot-REQUEST-Implement-Category-Display-Order.txt: 'alphabetical' (default,
// preserves existing A-Z behavior) or 'display_order' (numbered Categories first low-to-high,
// ties/blank fall back to A-Z). Applies wherever normal active Categories are shown as an ordered
// list (admin Category dropdown, public Category select + icon grid); see
// rbm_msch_category_sort_terms() in rbm-lessons.php.
define('RBM_MSCH_CATEGORY_ORDER_MODE_OPTION', 'rbm_msch_category_order_mode');

function rbm_msch_category_order_mode() {
    $mode = get_option(RBM_MSCH_CATEGORY_ORDER_MODE_OPTION, 'alphabetical');
    return $mode === 'display_order' ? 'display_order' : 'alphabetical';
}

// docs/0913-1226-Copilot-REQUEST-Add-Instrument-Display-Order.txt: 'alphabetical' (default,
// preserves existing A-Z tile behavior) or 'display_order' (numbered Instruments first low-to-high,
// ties/blank fall back to A-Z). Applies to public Instrument tile rendering; see
// rbm_msch_lesson_sort_posts() in rbm-lessons.php. Independent of Category Order above.
define('RBM_MSCH_LESSON_TILE_ORDER_MODE_OPTION', 'rbm_msch_lesson_tile_order_mode');

function rbm_msch_lesson_tile_order_mode() {
    $mode = get_option(RBM_MSCH_LESSON_TILE_ORDER_MODE_OPTION, 'alphabetical');
    return $mode === 'display_order' ? 'display_order' : 'alphabetical';
}

// docs/0917-1005-Copilot-REQUEST-Implement-Category-Click-Destination-Setting.txt: 'tiles'
// (default, preserves the existing category-click-shows-instrument-tiles behavior) or 'faculty'
// (a Category tile instead links straight to that Category's Faculty group; see
// rbm_msch_faculty_category_url() in rbm-lessons.php).
define('RBM_MSCH_CATEGORY_CLICK_DESTINATION_OPTION', 'rbm_msch_category_click_destination');

function rbm_msch_category_click_destination() {
    $mode = get_option(RBM_MSCH_CATEGORY_CLICK_DESTINATION_OPTION, 'tiles');
    return $mode === 'faculty' ? 'faculty' : 'tiles';
}

// docs/0914-Copilot-REQUEST-Add-Show-Date-Column-Setting.txt: Instruments admin list only (see
// rbm_msch_lesson_admin_columns() in rbm-lessons.php) — no effect on Posts, Pages, Faculty, or any
// other admin list. Default off, matching the list's existing hard-coded-hidden behavior.
define('RBM_MSCH_LESSON_SHOW_DATE_COLUMN_OPTION', 'rbm_msch_lesson_show_date_column');

function rbm_msch_lesson_show_date_column() {
    return get_option(RBM_MSCH_LESSON_SHOW_DATE_COLUMN_OPTION, '') === '1';
}

// docs/0919-1126-Copilot-REQUEST-Add-Global-And-Custom-Sign-Up-URL-Modes.txt: single shared
// option read directly (get_option(), no cross-plugin function call) by both rbm-instruments and
// rbm-faculty, so either plugin can be deactivated without a fatal error. Used by any Instrument
// or Teacher whose own Sign Up Link mode is "Use global URL" (see rbm_msch_lesson_resolve_signup_url()
// below and rbm_faculty_global_signup_url() in plugins/rbm-faculty/includes/rbm-teachers.php).
define('RBM_MSCH_GLOBAL_SIGNUP_URL_OPTION', 'rbm_msch_global_signup_url');

function rbm_msch_lesson_global_signup_url() {
    return get_option(RBM_MSCH_GLOBAL_SIGNUP_URL_OPTION, '');
}

// Resolves a single Instrument's Sign Up URL per its explicit Global/Custom mode
// (_msch_lesson_signup_mode). Safe-upgrade rule for a pre-existing record with no saved mode yet:
// a non-empty existing _msch_lesson_signup_url is treated as Custom (preserving current behavior);
// otherwise Global. The mode is only persisted the next time the record is saved (rbm-lesson-
// form.php), never migrated in bulk here.
function rbm_msch_lesson_resolve_signup_url($lesson_id) {
    $custom = get_post_meta($lesson_id, '_msch_lesson_signup_url', true);
    $mode = get_post_meta($lesson_id, '_msch_lesson_signup_mode', true);
    if ($mode !== 'global' && $mode !== 'custom') {
        $mode = ($custom !== '') ? 'custom' : 'global';
    }
    if ($mode === 'custom' && $custom !== '') {
        return $custom;
    }
    return rbm_msch_lesson_global_signup_url();
}

// Single source of truth for the plaintext list: the same Instruments We Teach category
// relationship already used by the Instrument editor checkbox and the Instruments list status
// column (rbm_msch_lesson_is_instruments_we_teach() in includes/rbm-lessons.php). Sorted
// alphabetically by title, matching the plugin's own established default shortcode order
// ('order' => 'name' in rbm_msch_lessons_shortcode(), already used on the live Lessons page).
function rbm_lessons_get_instruments_we_teach_titles() {
    $lessons = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    $titles = [];
    foreach ($lessons as $lesson) {
        if (rbm_msch_lesson_is_instruments_we_teach($lesson->ID)) {
            $titles[] = $lesson->post_title;
        }
    }
    return $titles;
}

add_action('admin_menu', 'rbm_lessons_add_display_settings_page', 20);
function rbm_lessons_add_display_settings_page() {
    add_submenu_page(
        'edit.php?post_type=msch_lesson',
        'Instruments Page Display',
        'Settings',
        'manage_options',
        'rbm-lessons-display-settings',
        'rbm_lessons_render_display_settings_page'
    );
}

function rbm_lessons_render_display_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $current_mode = rbm_lessons_get_display_mode();
    $current_pill_mode = rbm_instruments_get_choose_instrument_pill_mode();
    $current_category_order_mode = rbm_msch_category_order_mode();
    $current_tile_order_mode = rbm_msch_lesson_tile_order_mode();
    $current_show_date_column = rbm_msch_lesson_show_date_column();
    $current_category_click_destination = rbm_msch_category_click_destination();
    $current_global_signup_url = rbm_msch_lesson_global_signup_url();
    ?>
    <div class="wrap">
        <h1>Instruments Page Display</h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Settings saved.</p></div>
        <?php endif; ?>
        <?php if (isset($_GET['signup_bulk_instruments'])) : ?>
            <div class="updated"><p>Reset to Global: <?php echo (int) $_GET['signup_bulk_instruments']; ?> Instrument record(s) and <?php echo (int) $_GET['signup_bulk_teachers']; ?> Teacher record(s) changed. Saved Custom URLs were not changed.</p></div>
        <?php endif; ?>
        <?php if (isset($_GET['signup_cleared_instruments'])) : ?>
            <div class="updated"><p><?php echo (int) $_GET['signup_cleared_instruments']; ?> Instrument custom URL(s) and <?php echo (int) $_GET['signup_cleared_teachers']; ?> Teacher custom URL(s) cleared. All affected records now use the Global Sign Up URL mode.</p></div>
        <?php endif; ?>
        <p><button type="submit" form="rbm-lessons-display-settings-form" class="button button-primary">Save Changes</button></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="rbm-lessons-display-settings-form">
            <input type="hidden" name="action" value="rbm_save_lessons_display_settings">
            <?php wp_nonce_field('rbm_save_lessons_display_settings', 'rbm_lessons_display_settings_nonce'); ?>
            <p><strong>Instruments Page Display</strong></p>
            <p>
                <label><input type="radio" name="rbm_lessons_display_mode" value="categories" <?php checked($current_mode, 'categories'); ?>> Categories only</label><br>
                <label><input type="radio" name="rbm_lessons_display_mode" value="instruments" <?php checked($current_mode, 'instruments'); ?>> Instruments We Teach only</label><br>
                <label><input type="radio" name="rbm_lessons_display_mode" value="none" <?php checked($current_mode, 'none'); ?>> None</label>
            </p>
            <?php if ($current_mode === '') : ?>
                <p class="description">No explicit choice has been saved yet. The public Instruments page currently shows the existing combined Categories + Instruments We Teach view. Choose an option above and Save Changes to switch to a single mode.</p>
            <?php endif; ?>
            <p><strong>Choose Instrument pill</strong></p>
            <p>
                <label><input type="radio" name="rbm_instruments_choose_instrument_pill" value="show" <?php checked($current_pill_mode, 'show'); ?>> Show</label><br>
                <label><input type="radio" name="rbm_instruments_choose_instrument_pill" value="hide" <?php checked($current_pill_mode, 'hide'); ?>> Hide</label>
            </p>
            <p class="description">Controls the green "Choose Instrument" pill/button independently of the display mode above. Only has a visible effect when categories are shown (Categories only, or the default combined view); there are no categories to choose from in Instruments We Teach only mode.</p>
            <p><strong>Category Order</strong></p>
            <p>
                <label><input type="radio" name="rbm_msch_category_order_mode" value="alphabetical" <?php checked($current_category_order_mode, 'alphabetical'); ?>> Alphabetical</label><br>
                <label><input type="radio" name="rbm_msch_category_order_mode" value="display_order" <?php checked($current_category_order_mode, 'display_order'); ?>> Display Order</label>
            </p>
            <p class="description">Display Order uses each Category's optional numeric Display Order field (set on the Category edit screen), low to high; ties and Categories left blank fall back to alphabetical, with blank always last. Applies to the Instruments admin Category dropdown and the public Category selector/icon grid. Instruments We Teach / Direct Display is unaffected either way.</p>
            <p><strong>Instrument Tile Order</strong></p>
            <p>
                <label><input type="radio" name="rbm_msch_lesson_tile_order_mode" value="alphabetical" <?php checked($current_tile_order_mode, 'alphabetical'); ?>> Alphabetical</label><br>
                <label><input type="radio" name="rbm_msch_lesson_tile_order_mode" value="display_order" <?php checked($current_tile_order_mode, 'display_order'); ?>> Display Order</label>
            </p>
            <p class="description">Display Order uses each Instrument's optional numeric Display Order field (Instrument edit screen), low to high; ties and Instruments left blank fall back to alphabetical, with blank always last. Applies to the public Instrument tile grid. Has no effect on any [msch_lessons order="manual"] shortcode usage, which keeps using its own explicit Order field.</p>
            <p><strong>Category Click Destination</strong></p>
            <p>
                <label><input type="radio" name="rbm_msch_category_click_destination" value="tiles" <?php checked($current_category_click_destination, 'tiles'); ?>> Show Instrument Tiles</label><br>
                <label><input type="radio" name="rbm_msch_category_click_destination" value="faculty" <?php checked($current_category_click_destination, 'faculty'); ?>> Faculty Group Page</label>
            </p>
            <p class="description">Show Instrument Tiles (default) keeps the current behavior: clicking a Category opens that Category's Instrument tiles. Faculty Group Page instead sends visitors straight to the Faculty page filtered to that Category's full group.</p>
            <p><strong>Show Date Column</strong></p>
            <p>
                <label><input type="checkbox" name="rbm_msch_lesson_show_date_column" value="1" <?php checked($current_show_date_column); ?>> Show the WordPress publication date in the Instruments list</label>
            </p>
            <p class="description">Controls only the Instruments admin list's Date column. Off by default. Does not affect Posts, Pages, Faculty, or any other admin list.</p>
            <p><strong>Global Sign Up URL</strong></p>
            <p>
                <input type="url" id="rbm_msch_global_signup_url" name="rbm_msch_global_signup_url" class="widefat" style="max-width:480px;" value="<?php echo esc_attr($current_global_signup_url); ?>" placeholder="https://">
            </p>
            <p class="description">Used by any Instrument or Teacher whose Sign Up Link is set to "Use global URL" on its own edit screen. Changing this value updates every record using Global immediately, without editing those records.</p>
            <?php submit_button('Save Changes'); ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Reset all Instrument and Teacher Sign Up Links to use the Global Sign Up URL? Saved Custom URLs will be preserved.');">
            <input type="hidden" name="action" value="rbm_set_all_signup_records_global">
            <?php wp_nonce_field('rbm_set_all_signup_records_global', 'rbm_set_all_signup_records_global_nonce'); ?>
            <p><button type="submit" class="button">Reset All to Global URL</button></p>
            <p class="description">Switches every Instrument and Teacher record's Sign Up Link to "Use global URL". Does not clear or change any record's saved Custom URL.</p>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Permanently delete all saved custom Sign Up URLs for Instruments and Teachers? All records will be switched to \'Use global URL.\' This cannot be undone.');">
            <input type="hidden" name="action" value="rbm_clear_all_custom_signup_urls">
            <?php wp_nonce_field('rbm_clear_all_custom_signup_urls', 'rbm_clear_all_custom_signup_urls_nonce'); ?>
            <p><button type="submit" class="button button-link-delete">Clear All Custom URLs</button></p>
            <p class="description">Permanently deletes every Instrument's and Teacher's saved Custom Sign Up URL and switches them to "Use global URL". This cannot be undone. Does not change the Global Sign Up URL setting above.</p>
        </form>

        <h2>INSTRUMENTS WE TEACH</h2>
        <?php
        $instrument_titles = rbm_lessons_get_instruments_we_teach_titles();
        if (empty($instrument_titles)) :
        ?>
            <p>No instruments are currently assigned to INSTRUMENTS WE TEACH.</p>
        <?php else : ?>
            <p>
                <textarea id="rbm-iwt-list" readonly rows="<?php echo (int) max(3, count($instrument_titles)); ?>" style="width:320px;max-width:100%;font-family:monospace;"><?php echo esc_textarea(implode("\n", $instrument_titles)); ?></textarea>
            </p>
            <p>
                <button type="button" class="button rbm-copy-list-trigger" data-copy-target="rbm-iwt-list" data-status-target="rbm-iwt-copy-status">Copy List</button>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_download_instruments_we_teach'), 'rbm_download_instruments_we_teach')); ?>">Download .txt</a>
                <span id="rbm-iwt-copy-status" style="margin-left:8px;"></span>
            </p>
        <?php endif; ?>
    </div>
    <?php
}

add_action('admin_post_rbm_save_lessons_display_settings', 'rbm_lessons_save_display_settings');
function rbm_lessons_save_display_settings() {
    if (!isset($_POST['rbm_lessons_display_settings_nonce']) || !wp_verify_nonce($_POST['rbm_lessons_display_settings_nonce'], 'rbm_save_lessons_display_settings')) {
        wp_die('Security check failed.');
    }
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    update_option(RBM_MSCH_GLOBAL_SIGNUP_URL_OPTION, esc_url_raw(wp_unslash($_POST['rbm_msch_global_signup_url'] ?? '')));

    $mode = sanitize_key(wp_unslash($_POST['rbm_lessons_display_mode'] ?? ''));
    if (!in_array($mode, ['categories', 'instruments', 'none'], true)) {
        $mode = 'categories';
    }
    update_option(RBM_LESSONS_DISPLAY_MODE_OPTION, $mode);

    $pill_mode = sanitize_key(wp_unslash($_POST['rbm_instruments_choose_instrument_pill'] ?? ''));
    if (!in_array($pill_mode, ['show', 'hide'], true)) {
        $pill_mode = 'hide';
    }
    update_option(RBM_INSTRUMENTS_CHOOSE_INSTRUMENT_PILL_OPTION, $pill_mode);

    $category_order_mode = sanitize_key(wp_unslash($_POST['rbm_msch_category_order_mode'] ?? ''));
    if (!in_array($category_order_mode, ['alphabetical', 'display_order'], true)) {
        $category_order_mode = 'alphabetical';
    }
    update_option(RBM_MSCH_CATEGORY_ORDER_MODE_OPTION, $category_order_mode);

    $tile_order_mode = sanitize_key(wp_unslash($_POST['rbm_msch_lesson_tile_order_mode'] ?? ''));
    if (!in_array($tile_order_mode, ['alphabetical', 'display_order'], true)) {
        $tile_order_mode = 'alphabetical';
    }
    update_option(RBM_MSCH_LESSON_TILE_ORDER_MODE_OPTION, $tile_order_mode);

    update_option(RBM_MSCH_LESSON_SHOW_DATE_COLUMN_OPTION, !empty($_POST['rbm_msch_lesson_show_date_column']) ? '1' : '');

    $category_click_destination = sanitize_key(wp_unslash($_POST['rbm_msch_category_click_destination'] ?? ''));
    if (!in_array($category_click_destination, ['tiles', 'faculty'], true)) {
        $category_click_destination = 'tiles';
    }
    update_option(RBM_MSCH_CATEGORY_CLICK_DESTINATION_OPTION, $category_click_destination);

    wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lessons-display-settings&saved=1'));
    exit;
}

// docs/0919-1348-Copilot-REQUEST-Add-Global-Sign-Up-URL-Reset.txt: switches every Instrument and
// every Teacher (when rbm-faculty is active) to "Use global URL"; never touches their saved
// custom URL meta, so switching back to Custom later still recovers it. Only counts/reports
// records whose mode actually changed, so repeating this is safe and shows 0/0 when nothing was
// left in Custom mode.
add_action('admin_post_rbm_set_all_signup_records_global', 'rbm_set_all_signup_records_global');
function rbm_set_all_signup_records_global() {
    if (!isset($_POST['rbm_set_all_signup_records_global_nonce']) || !wp_verify_nonce($_POST['rbm_set_all_signup_records_global_nonce'], 'rbm_set_all_signup_records_global')) {
        wp_die('Security check failed.');
    }
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    $lesson_ids = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    $lesson_changed = 0;
    foreach ($lesson_ids as $lesson_id) {
        if (get_post_meta($lesson_id, '_msch_lesson_signup_mode', true) !== 'global') {
            update_post_meta($lesson_id, '_msch_lesson_signup_mode', 'global');
            $lesson_changed++;
        }
    }

    $teacher_changed = 0;
    if (post_type_exists('msch_teacher')) {
        $teacher_ids = get_posts([
            'post_type'      => 'msch_teacher',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        foreach ($teacher_ids as $teacher_id) {
            if (get_post_meta($teacher_id, '_msch_teacher_signup_mode', true) !== 'global') {
                update_post_meta($teacher_id, '_msch_teacher_signup_mode', 'global');
                $teacher_changed++;
            }
        }
    }

    wp_safe_redirect(add_query_arg([
        'signup_bulk_instruments' => $lesson_changed,
        'signup_bulk_teachers'    => $teacher_changed,
    ], admin_url('edit.php?post_type=msch_lesson&page=rbm-lessons-display-settings')));
    exit;
}

// docs/0919-1354-Copilot-REQUEST-Add-Clear-All-Custom-Sign-Up-URLs.txt: destructive, separate from
// the Reset action above — permanently deletes every saved Custom Sign Up URL (never just blanks
// it, so a later "Use custom URL" switch shows a genuinely empty field, not a stale value) and
// also sets every record to "Use global URL". Leaves the Global Sign Up URL option untouched.
add_action('admin_post_rbm_clear_all_custom_signup_urls', 'rbm_clear_all_custom_signup_urls');
function rbm_clear_all_custom_signup_urls() {
    if (!isset($_POST['rbm_clear_all_custom_signup_urls_nonce']) || !wp_verify_nonce($_POST['rbm_clear_all_custom_signup_urls_nonce'], 'rbm_clear_all_custom_signup_urls')) {
        wp_die('Security check failed.');
    }
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    $lesson_ids = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    $lesson_cleared = 0;
    foreach ($lesson_ids as $lesson_id) {
        if ((string) get_post_meta($lesson_id, '_msch_lesson_signup_url', true) !== '') {
            delete_post_meta($lesson_id, '_msch_lesson_signup_url');
            $lesson_cleared++;
        }
        update_post_meta($lesson_id, '_msch_lesson_signup_mode', 'global');
    }

    $teacher_cleared = 0;
    if (post_type_exists('msch_teacher')) {
        $teacher_ids = get_posts([
            'post_type'      => 'msch_teacher',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        foreach ($teacher_ids as $teacher_id) {
            if ((string) get_post_meta($teacher_id, '_msch_teacher_signup_url', true) !== '') {
                delete_post_meta($teacher_id, '_msch_teacher_signup_url');
                $teacher_cleared++;
            }
            update_post_meta($teacher_id, '_msch_teacher_signup_mode', 'global');
        }
    }

    wp_safe_redirect(add_query_arg([
        'signup_cleared_instruments' => $lesson_cleared,
        'signup_cleared_teachers'    => $teacher_cleared,
    ], admin_url('edit.php?post_type=msch_lesson&page=rbm-lessons-display-settings')));
    exit;
}

add_action('admin_post_rbm_download_instruments_we_teach', 'rbm_lessons_download_instruments_we_teach');
function rbm_lessons_download_instruments_we_teach() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_download_instruments_we_teach');
    $titles = rbm_lessons_get_instruments_we_teach_titles();
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="instruments-we-teach.txt"');
    echo implode("\n", $titles) . (empty($titles) ? '' : "\n");
    exit;
}
