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
        'Instruments Page Display',
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
    ?>
    <div class="wrap">
        <h1>Instruments Page Display</h1>
        <?php if (isset($_GET['saved'])) : ?>
            <div class="updated"><p>Settings saved.</p></div>
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
                <p class="description">No explicit choice has been saved yet. The public Lessons page currently shows the existing combined Categories + Instruments We Teach view. Choose an option above and Save Changes to switch to a single mode.</p>
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
            <?php submit_button('Save Changes'); ?>
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
                <button type="button" class="button" id="rbm-iwt-copy">Copy List</button>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=rbm_download_instruments_we_teach'), 'rbm_download_instruments_we_teach')); ?>">Download .txt</a>
                <span id="rbm-iwt-copy-status" style="margin-left:8px;"></span>
            </p>
            <script>
            (function () {
                var btn = document.getElementById('rbm-iwt-copy');
                var status = document.getElementById('rbm-iwt-copy-status');
                if (!btn) {
                    return;
                }
                btn.addEventListener('click', function () {
                    var ta = document.getElementById('rbm-iwt-list');
                    ta.select();
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(ta.value).then(function () {
                            status.textContent = 'Copied';
                            setTimeout(function () { status.textContent = ''; }, 2000);
                        });
                    } else {
                        document.execCommand('copy');
                        status.textContent = 'Copied';
                        setTimeout(function () { status.textContent = ''; }, 2000);
                    }
                });
            })();
            </script>
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

    wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&page=rbm-lessons-display-settings&saved=1'));
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
