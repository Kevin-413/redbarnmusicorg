<?php
/**
 * Lessons data model (Stage 2 of docs/0909-1310-PLAN-Lessons-Instruments-Module-Staged-Reuse-Plan.txt).
 * Data model only: CPT + shared taxonomy attachment. No admin form, no public output yet.
 */

add_action('init', 'rbm_register_lesson_cpt');
function rbm_register_lesson_cpt() {
    register_post_type('msch_lesson', [
        'labels' => [
            'name'               => 'Instruments',
            'singular_name'      => 'Instrument',
            'add_new_item'       => 'Add Instrument',
            'edit_item'          => 'Edit Instrument',
            'new_item'           => 'Add Instrument',
            'all_items'          => 'Instruments',
            'view_item'          => 'View Instrument',
            'search_items'       => 'Search Instruments',
            'not_found'          => 'No instruments found',
            'not_found_in_trash' => 'No instruments found in Trash',
            'menu_name'          => 'Instruments',
        ],
        'public'              => true,
        'has_archive'         => false,
        'publicly_queryable'  => true,
        'exclude_from_search' => true,
        'show_in_nav_menus'   => false,
        'rewrite'             => ['slug' => 'lesson', 'with_front' => false],
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-format-audio',
        'supports'            => ['title', 'thumbnail', 'page-attributes'],
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
    ]);
}

// msch_instrument is registered by plugins/rbm-faculty (for msch_teacher); attach it here for
// msch_lesson too rather than editing that registration, so Faculty is never touched.
add_action('init', 'rbm_attach_instrument_taxonomy_to_lesson', 20);
function rbm_attach_instrument_taxonomy_to_lesson() {
    if (taxonomy_exists('msch_instrument')) {
        register_taxonomy_for_object_type('msch_instrument', 'msch_lesson');
    }
}

// Sidebar cleanup (see docs/0910-0430-...): drop the native "Add Lesson" submenu (redundant with
// the standalone form's own Add flow) and relabel the shared msch_instrument taxonomy submenu to
// "Categories" only under the Lessons menu — the taxonomy's own labels stay "Instruments" so the
// Faculty menu (which shares this taxonomy) is unaffected.
add_action('admin_menu', 'rbm_lessons_cleanup_admin_menu', 999);
function rbm_lessons_cleanup_admin_menu() {
    remove_submenu_page('edit.php?post_type=msch_lesson', 'post-new.php?post_type=msch_lesson');

    global $submenu;
    if (empty($submenu['edit.php?post_type=msch_lesson'])) {
        return;
    }
    foreach ($submenu['edit.php?post_type=msch_lesson'] as &$item) {
        // WP stores this submenu's URL pre-escaped (querystring "&" as "&amp;").
        if (strpos($item[2], 'edit-tags.php?taxonomy=msch_instrument') === 0) {
            $item[0] = 'Categories';
        }
    }
    unset($item);
}

// The submenu text above is only the sidebar link label — the page you actually land on still
// showed its real registered label ("Instruments" for the taxonomy, "Lessons" for the CPT list),
// which didn't match. Align both page headings/titles with what the sidebar link says, scoped to
// the Lessons post_type context only so Faculty's own "Instruments" taxonomy screen is unaffected.
add_action('load-edit-tags.php', 'rbm_lessons_align_taxonomy_page_heading');
function rbm_lessons_align_taxonomy_page_heading() {
    if (($_GET['taxonomy'] ?? '') !== 'msch_instrument' || ($_GET['post_type'] ?? '') !== 'msch_lesson') {
        return;
    }
    global $wp_taxonomies;
    if (isset($wp_taxonomies['msch_instrument'])) {
        $wp_taxonomies['msch_instrument']->labels->name = 'Categories';
    }
}

add_action('load-edit.php', 'rbm_lessons_align_list_page_heading');
function rbm_lessons_align_list_page_heading() {
    if (($_GET['post_type'] ?? '') !== 'msch_lesson') {
        return;
    }
    global $wp_post_types;
    if (isset($wp_post_types['msch_lesson'])) {
        $wp_post_types['msch_lesson']->labels->name = 'Instruments';
    }
}

// Some shared msch_instrument terms are Faculty-only (e.g. "Keyboards" — a teacher instrument, not
// yet a Lesson category) and shouldn't clutter the Lessons > Categories list. Flag those terms with
// _msch_instrument_hide_from_categories term meta; they still show normally under Faculty > Instruments.
add_filter('get_terms_args', 'rbm_lessons_hide_faculty_only_categories', 10, 2);
function rbm_lessons_hide_faculty_only_categories($args, $taxonomies) {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['post_type'] ?? '') !== 'msch_lesson' || !in_array('msch_instrument', $taxonomies, true)) {
        return $args;
    }
    global $wpdb;
    // Direct query (not get_terms()) to avoid re-triggering this same get_terms_args filter.
    $hidden = $wpdb->get_col($wpdb->prepare(
        "SELECT tm.term_id FROM {$wpdb->termmeta} tm
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id AND tt.taxonomy = %s
         WHERE tm.meta_key = '_msch_instrument_hide_from_categories' AND tm.meta_value = '1'",
        'msch_instrument'
    ));
    if (!empty($hidden)) {
        $args['exclude'] = array_merge((array) ($args['exclude'] ?? []), array_map('intval', $hidden));
    }
    return $args;
}

// Lessons list: remove the Bulk Actions/Filter bar entirely — bulk edit doesn't apply to this
// simplified admin flow (Lessons are managed one at a time via the dedicated Add/Edit Lesson form).
add_filter('bulk_actions-edit-msch_lesson', '__return_empty_array');
// Same for the Instruments/Categories taxonomy list (shared by Faculty and Lessons entry points).
add_filter('bulk_actions-edit-msch_instrument', '__return_empty_array');
add_filter('disable_months_dropdown', 'rbm_msch_lesson_disable_months_dropdown', 10, 2);
function rbm_msch_lesson_disable_months_dropdown($disable, $post_type) {
    return $post_type === 'msch_lesson' ? true : $disable;
}
add_action('admin_head-edit.php', 'rbm_msch_lesson_hide_tablenav_bar');
function rbm_msch_lesson_hide_tablenav_bar() {
    if (($_GET['post_type'] ?? '') !== 'msch_lesson') {
        return;
    }
    ?>
    <style>
        .tablenav .actions { display: none !important; }
        .wp-list-table .column-date { white-space: nowrap; width: 160px; }
    </style>
    <?php
}

// Edit Category page (term.php): relabel the heading from "Edit Instrument" to "Edit Category"
// only when reached from the Lessons entry point; the taxonomy's own labels stay "Instrument" so
// Faculty's own Edit screen (which shares this taxonomy) is unaffected.
add_action('admin_head-term.php', 'rbm_lessons_relabel_edit_term_heading');
function rbm_lessons_relabel_edit_term_heading() {
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'msch_instrument' || ($_GET['post_type'] ?? '') !== 'msch_lesson') {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var heading = document.querySelector('.wrap h1');
        if (heading && heading.textContent.trim() === 'Edit Instrument') {
            heading.textContent = 'Edit Category';
        }
        if (document.title.indexOf('Edit Instrument') === 0) {
            document.title = document.title.replace('Edit Instrument', 'Edit Category');
        }
    });
    </script>
    <?php
}

// Categories screen (docs/0910-0444-...): move the native "Add Instrument" box to the bottom of
// the page and add a jump link at the top, so the term list isn't pushed below the fold. Applies
// to the shared msch_instrument taxonomy screen regardless of entry point (Lessons or Faculty).
add_action('admin_head-edit-tags.php', 'rbm_instrument_page_move_add_form_to_bottom');
function rbm_instrument_page_move_add_form_to_bottom() {
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'msch_instrument') {
        return;
    }
    // Add Instrument needs a responsive image-upload step we haven't built yet; disable just the
    // Lessons entry point's Add form until that's ready (Faculty's own Add Instrument is untouched).
    $disable_add_form = (($_GET['post_type'] ?? '') === 'msch_lesson');
    ?>
    <style>
        #col-container { display: flex; flex-direction: column; }
        #col-container #col-left, #col-container #col-right { float: none; width: 100%; }
        #col-container #col-left { order: 2; margin-top: 20px; }
        #col-container #col-right { order: 1; }
        <?php if ($disable_add_form): ?>
        #col-left form#addtag { opacity: 0.5; pointer-events: none; }
        <?php endif; ?>
        /* Bulk Actions is disabled on this list (see bulk_actions-edit-msch_instrument); hide the now-empty tablenav bar. */
        .tablenav .actions { display: none !important; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var heading = document.querySelector('.wp-heading-inline');
        if (heading && !document.getElementById('rbm-jump-to-add-instrument')) {
            var link = document.createElement('a');
            link.id = 'rbm-jump-to-add-instrument';
            link.href = '#col-left';
            link.className = 'page-title-action';
            link.textContent = 'Add Instrument';
            heading.insertAdjacentElement('afterend', link);
        }
        <?php if ($disable_add_form): ?>
        var addHeading = document.querySelector('#col-left h2');
        if (addHeading && addHeading.textContent.indexOf('INACTIVE FEATURE') === -1) {
            addHeading.textContent = addHeading.textContent + ' - INACTIVE FEATURE';
        }
        var addSubmit = document.querySelector('#col-left form#addtag #submit');
        if (addSubmit) {
            addSubmit.disabled = true;
        }
        <?php endif; ?>
        // Moving #col-left (the Add Tag form) to the bottom via CSS order puts WP core's own
        // auto-focus of its #tag-name field below the fold, causing the page to jump on load.
        window.scrollTo(0, 0);
    });
    </script>
    <?php
}

// One-time add of the 2 approved new category terms; never touches existing terms.
add_action('init', 'rbm_seed_lesson_category_terms', 20);
function rbm_seed_lesson_category_terms() {
    if (get_option('rbm_msch_lesson_category_seeded')) {
        return;
    }
    if (!taxonomy_exists('msch_instrument')) {
        return;
    }
    foreach (['Early Childhood', 'Workshops'] as $term) {
        if (!term_exists($term, 'msch_instrument')) {
            wp_insert_term($term, 'msch_instrument');
        }
    }
    update_option('rbm_msch_lesson_category_seeded', 1);
}

// --- Admin list columns: Active/Inactive status on both the Lessons and Instruments lists ---

add_filter('manage_msch_lesson_posts_columns', 'rbm_msch_lesson_admin_columns');
function rbm_msch_lesson_admin_columns($columns) {
    // Bulk Actions is disabled on this list, so the row-select checkboxes serve no purpose.
    unset($columns['cb']);
    // Drag-and-drop reorder handle: only in the exact scope Reset Display Order is enabled for
    // (docs/0913-1512-Copilot-REQUEST-Add-Drag-And-Drop-Display-Order-To-Instruments-And-Categories.txt).
    $drag_enabled = (rbm_msch_lesson_current_view() === 'iwt' && rbm_msch_lesson_current_status_filter() === 'active');
    // Insert right after Title (Instruments/Date already appear via WP's own taxonomy/date logic).
    $new_columns = [];
    foreach ($columns as $key => $label) {
        // Relabel only on the Lessons list; the taxonomy's own "Instruments" label stays intact
        // everywhere else (e.g. Faculty), since this filter is scoped to manage_msch_lesson_posts_columns.
        if ($key === 'taxonomy-msch_instrument') {
            $label = 'Categories';
        }
        if ($key === 'title') {
            if ($drag_enabled) {
                $new_columns['rbm_drag'] = '';
            }
            $new_columns['rbm_icon'] = 'Icon';
        }
        $new_columns[$key] = $label;
        if ($key === 'title') {
            $new_columns['rbm_status'] = 'Status';
            $new_columns['rbm_display_order'] = 'Display Order';
        }
        if ($key === 'taxonomy-msch_instrument') {
            $new_columns['rbm_iwt_status'] = 'INSTRUMENTS WE TEACH';
        }
    }
    return $new_columns;
}

add_action('manage_msch_lesson_posts_custom_column', 'rbm_msch_lesson_admin_column_content', 10, 2);
function rbm_msch_lesson_admin_column_content($column, $post_id) {
    if ($column === 'rbm_drag') {
        echo '<span class="dashicons dashicons-menu rbm-drag-handle" style="cursor:move;" title="Drag to reorder"></span>';
        return;
    }
    if ($column === 'rbm_icon') {
        $icon_url = rbm_msch_lesson_catalog_image_url(get_post_meta($post_id, '_msch_lesson_image_filename', true));
        if ($icon_url === '') {
            echo '&#8212;';
            return;
        }
        echo '<img src="' . esc_url($icon_url) . '" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">';
        return;
    }
    if ($column === 'rbm_iwt_status') {
        $is_iwt = rbm_msch_lesson_is_instruments_we_teach($post_id);
        $label = $is_iwt ? 'Instruments We Teach: Yes' : 'Instruments We Teach: No';
        printf(
            '<span style="display:inline-block;width:12px;height:12px;border-radius:50%%;vertical-align:middle;background:%s;" title="%s" aria-label="%s" role="img"></span>',
            $is_iwt ? '#1a7e1a' : '#a00',
            esc_attr($label),
            esc_attr($label)
        );
        return;
    }
    if ($column === 'rbm_display_order') {
        $order = get_post_meta($post_id, '_msch_lesson_display_order', true);
        echo ($order === '') ? '&#8212;' : esc_html($order);
        return;
    }
    if ($column !== 'rbm_status') {
        return;
    }
    $is_active = get_post_status($post_id) === 'publish';
    echo $is_active
        ? '<span style="color:#1a7e1a;font-weight:600;">Active</span>'
        : '<span style="color:#a00;font-weight:600;">Inactive</span>';
}

// Make Status and Categories sortable on the Lessons list. Status sorts directly on post_status;
// Categories sorts on the joined term name (a lesson normally has one Category, but the JOIN/GROUP BY
// below keeps pagination counts correct even if a lesson is ever tagged with more than one).
add_filter('manage_edit-msch_lesson_sortable_columns', 'rbm_msch_lesson_sortable_columns');
function rbm_msch_lesson_sortable_columns($columns) {
    $columns['rbm_status'] = 'rbm_status';
    $columns['taxonomy-msch_instrument'] = 'taxonomy-msch_instrument';
    $columns['rbm_display_order'] = 'rbm_display_order';
    return $columns;
}

add_filter('posts_orderby', 'rbm_msch_lesson_custom_orderby', 10, 2);
function rbm_msch_lesson_custom_orderby($orderby, $query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'msch_lesson') {
        return $orderby;
    }
    global $wpdb;
    $order = strtoupper((string) $query->get('order')) === 'DESC' ? 'DESC' : 'ASC';
    if ($query->get('orderby') === 'rbm_status') {
        return "{$wpdb->posts}.post_status {$order}";
    }
    if ($query->get('orderby') === 'taxonomy-msch_instrument') {
        return "rbm_instrument_term.name {$order}";
    }
    if ($query->get('orderby') === 'rbm_display_order') {
        // docs/0913-1315-...: numbered Instruments low-to-high, blank/unset always last, ties A-Z.
        return "(rbm_display_order_meta.meta_value IS NULL OR rbm_display_order_meta.meta_value = '') ASC,"
            . " CAST(rbm_display_order_meta.meta_value AS SIGNED) ASC, {$wpdb->posts}.post_title ASC";
    }
    return $orderby;
}

add_filter('posts_join', 'rbm_msch_lesson_custom_join', 10, 2);
function rbm_msch_lesson_custom_join($join, $query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'msch_lesson') {
        return $join;
    }
    global $wpdb;
    if ($query->get('orderby') === 'taxonomy-msch_instrument') {
        $join .= " LEFT JOIN {$wpdb->term_relationships} AS rbm_instrument_tr ON ({$wpdb->posts}.ID = rbm_instrument_tr.object_id)";
        $join .= " LEFT JOIN {$wpdb->term_taxonomy} AS rbm_instrument_tt ON (rbm_instrument_tr.term_taxonomy_id = rbm_instrument_tt.term_taxonomy_id AND rbm_instrument_tt.taxonomy = 'msch_instrument')";
        $join .= " LEFT JOIN {$wpdb->terms} AS rbm_instrument_term ON (rbm_instrument_tt.term_id = rbm_instrument_term.term_id)";
    }
    if ($query->get('orderby') === 'rbm_display_order') {
        $join .= $wpdb->prepare(
            " LEFT JOIN {$wpdb->postmeta} AS rbm_display_order_meta ON ({$wpdb->posts}.ID = rbm_display_order_meta.post_id AND rbm_display_order_meta.meta_key = %s)",
            '_msch_lesson_display_order'
        );
    }
    return $join;
}

add_filter('posts_groupby', 'rbm_msch_lesson_custom_groupby', 10, 2);
function rbm_msch_lesson_custom_groupby($groupby, $query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'msch_lesson' || $query->get('orderby') !== 'taxonomy-msch_instrument') {
        return $groupby;
    }
    global $wpdb;
    return empty($groupby) ? "{$wpdb->posts}.ID" : $groupby;
}

// Remembers this user's last filters/sort on the Instruments list, so opening the page fresh (no
// query args) reopens where they left off. Generic by design (no hardcoded list of filter keys):
// whatever's in the URL besides post_type/paged gets saved. First-time visit (nothing saved yet)
// falls through to WP's own defaults: alphabetical by Name, no filters.
add_action('load-edit.php', 'rbm_msch_lesson_remember_list_state');
function rbm_msch_lesson_remember_list_state() {
    if (($_GET['post_type'] ?? '') !== 'msch_lesson') {
        return;
    }
    $state = array_diff_key($_GET, array_flip(['post_type', 'paged']));
    $user_id = get_current_user_id();
    if (!empty($state)) {
        $sanitized = [];
        foreach ($state as $key => $value) {
            if (is_string($value)) {
                $sanitized[sanitize_key($key)] = sanitize_text_field(wp_unslash($value));
            }
        }
        update_user_meta($user_id, '_rbm_msch_lesson_list_state', $sanitized);
        return;
    }
    $saved = get_user_meta($user_id, '_rbm_msch_lesson_list_state', true);
    if (!empty($saved) && is_array($saved)) {
        wp_safe_redirect(add_query_arg($saved));
        exit;
    }
}

// Instruments list "View By" control (docs/0913-1112-...): Categories (existing list, unchanged)
// vs Lessons We Teach (existing INSTRUMENTS WE TEACH direct-display relationship, read-only filter
// of the same list — no new data model, no change to categories/assignments).
function rbm_msch_lesson_current_view() {
    $view = isset($_GET['rbm_view']) ? sanitize_key(wp_unslash($_GET['rbm_view'])) : 'categories';
    return $view === 'iwt' ? 'iwt' : 'categories';
}

// Selected Category dropdown value (docs/0913-1150-...). Only meaningful in the 'categories' view;
// Lessons We Teach ignores it since it's a separate, mutually-exclusive filter.
function rbm_msch_lesson_current_category() {
    if (rbm_msch_lesson_current_view() === 'iwt' || !isset($_GET['rbm_category'])) {
        return '';
    }
    return sanitize_title(wp_unslash($_GET['rbm_category']));
}

// Status filter (docs/0913-1319-Copilot-REQUEST-Add-Status-Filters-To-Instruments-And-Categories.txt):
// independent of View By/Sort By. Active/Inactive map directly to the existing publish/draft
// source of truth (same as the Status column); no new status field.
function rbm_msch_lesson_current_status_filter() {
    $status = isset($_GET['rbm_status']) ? sanitize_key(wp_unslash($_GET['rbm_status'])) : 'active';
    return in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'active';
}

add_action('admin_notices', 'rbm_msch_lesson_view_by_control');
function rbm_msch_lesson_view_by_control() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-msch_lesson') {
        return;
    }

    // Reset Display Order (docs/0913-1338-...): clears then renumbers _msch_lesson_display_order
    // (1, 2, 3...) by alpha title order, scoped to the same Lessons We Teach + Active list this
    // button is enabled for; does not touch status, assignments, images, or other post meta. Sits
    // level with View By / Status, with the native "Add Instrument" button (moved via JS) next to it.
    $reset_count = count(rbm_msch_lesson_display_order_reset_scope());
    $view = rbm_msch_lesson_current_view();
    $current_category = rbm_msch_lesson_current_category();
    $base_url = remove_query_arg(['rbm_view', 'rbm_category', 'paged']);

    $terms = get_terms(['taxonomy' => 'msch_instrument', 'hide_empty' => false]);
    if (is_wp_error($terms)) {
        $terms = [];
    }
    // Active normal categories only, with at least one assigned Instrument; Instruments We Teach /
    // Direct Display stays out of this dropdown and is reached solely via the button below.
    $eligible_terms = [];
    foreach ($terms as $term) {
        if (rbm_msch_category_is_direct_display($term->term_id) || !rbm_msch_category_is_active($term->term_id)) {
            continue;
        }
        $has_lessons = get_posts([
            'post_type'      => 'msch_lesson',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'msch_instrument',
                'field'    => 'term_id',
                'terms'    => [$term->term_id],
            ]],
        ]);
        if (empty($has_lessons)) {
            continue;
        }
        $eligible_terms[] = $term;
    }
    $eligible_terms = rbm_msch_category_sort_terms($eligible_terms);
    $status_filter = rbm_msch_lesson_current_status_filter();
    $status_base_url = remove_query_arg(['rbm_status', 'paged']);
    // Reset Display Order only makes sense while viewing the list it reorders: Lessons We Teach,
    // Active status (matches the view Drag-and-Drop reordering will use).
    $reset_enabled = ($view === 'iwt' && $status_filter === 'active');
    ?>
    <div style="clear:both; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:20px; margin:10px 0;">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:20px;">
            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=msch_instrument&post_type=msch_lesson')); ?>" class="button" style="font-weight:700; font-size:15px;" title="Switch to Categories">&lt;&lt;</a>
            <div class="rbm-instruments-view-by">
                <strong>View By:</strong>
                <select onchange="if(this.value){window.location.href=this.value;}">
                    <option value="<?php echo esc_url($base_url); ?>"<?php selected($view === 'categories' && $current_category === ''); ?>>All Categories</option>
                    <?php foreach ($eligible_terms as $term) : ?>
                        <option value="<?php echo esc_url(add_query_arg('rbm_category', $term->slug, $base_url)); ?>"<?php selected($view === 'categories' && $current_category === $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="<?php echo esc_url(add_query_arg('rbm_view', 'iwt', $base_url)); ?>" class="button<?php echo $view === 'iwt' ? ' button-primary' : ''; ?>">Lessons We Teach</a>
            </div>
            <div class="rbm-instruments-status-filter" style="display:flex; align-items:center; gap:6px;">
                <strong>Status:</strong>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'active', $status_base_url)); ?>" class="button<?php echo $status_filter === 'active' ? ' button-primary' : ''; ?>">Active</a>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'inactive', $status_base_url)); ?>" class="button<?php echo $status_filter === 'inactive' ? ' button-primary' : ''; ?>">Inactive</a>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'all', $status_base_url)); ?>" class="button<?php echo $status_filter === 'all' ? ' button-primary' : ''; ?>">All</a>
            </div>
        </div>
        <div id="rbm-top-right-controls" style="display:flex; align-items:center; gap:10px;">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('This will clear Display Order for ALL Instruments (including Inactive/All) and renumber the <?php echo (int) $reset_count; ?> in Lessons We Teach / Active by alphabetical order (1, 2, 3...). Continue?');">
                <input type="hidden" name="action" value="rbm_reset_lesson_display_order">
                <?php wp_nonce_field('rbm_reset_lesson_display_order'); ?>
                <button type="submit" class="button"<?php disabled($reset_enabled, false); ?> title="<?php echo $reset_enabled ? '' : esc_attr('Only available in Lessons We Teach + Active view'); ?>">Reset Display Order<?php echo $reset_count ? ' (' . (int) $reset_count . ')' : ''; ?></button>
            </form>
            <span id="rbm-add-instrument-slot"></span>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var addBtn = document.querySelector('.wrap .page-title-action');
        var slot = document.getElementById('rbm-add-instrument-slot');
        if (addBtn && slot) {
            addBtn.className = 'button';
            slot.appendChild(addBtn);
        }
    });
    </script>
    <?php
    if ($reset_enabled) :
        wp_enqueue_script('jquery-ui-sortable');
        ?>
        <script>
        jQuery(function ($) {
            var $list = $('#the-list');
            $list.sortable({
                items: '> tr',
                handle: '.rbm-drag-handle',
                axis: 'y',
                update: function () {
                    var ids = $list.children('tr').map(function () {
                        return parseInt($(this).attr('id').replace('post-', ''), 10);
                    }).get();
                    $.post(ajaxurl, { action: 'rbm_save_lesson_display_order', nonce: '<?php echo esc_js(wp_create_nonce('rbm_drag_lesson_display_order')); ?>', ids: ids })
                        .done(function (r) {
                            if (!r || !r.success) {
                                location.reload();
                                return;
                            }
                            $list.children('tr').each(function (i) {
                                $(this).find('.column-rbm_display_order').text(i + 1);
                            });
                            $('<div class="notice notice-success is-dismissible"><p>Display order saved.</p></div>').insertAfter('.wp-heading-inline').delay(2000).fadeOut(300, function () { $(this).remove(); });
                        })
                        .fail(function () { location.reload(); });
                }
            });
        });
        </script>
        <?php
    endif;
}

add_action('admin_post_rbm_reset_lesson_display_order', 'rbm_reset_lesson_display_order');
function rbm_reset_lesson_display_order() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_reset_lesson_display_order');
    // Clear every Instrument's value first, including ones outside Lessons We Teach / Active
    // (Inactive, All), so no stale numbers are left behind; then renumber just the enabled scope.
    $all_ids = get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => '_msch_lesson_display_order',
    ]);
    foreach ($all_ids as $id) {
        delete_post_meta($id, '_msch_lesson_display_order');
    }
    $lessons = rbm_msch_lesson_display_order_reset_scope();
    usort($lessons, function ($a, $b) {
        return strcasecmp($a->post_title, $b->post_title);
    });
    foreach ($lessons as $i => $lesson) {
        update_post_meta($lesson->ID, '_msch_lesson_display_order', $i + 1);
    }
    wp_safe_redirect(admin_url('edit.php?post_type=msch_lesson&rbm_view=iwt&rbm_status=active&reset_display_order=1'));
    exit;
}

// Same Lessons We Teach + Active scope the Reset button is enabled for (docs/0913-1319-...,
// 0913-1338-...): published Instruments assigned to a direct-display ("Instruments We Teach") term.
function rbm_msch_lesson_display_order_reset_scope() {
    global $wpdb;
    $direct_term_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT tm.term_id FROM {$wpdb->termmeta} tm
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id AND tt.taxonomy = %s
         WHERE tm.meta_key = '_msch_category_display_mode' AND tm.meta_value = 'direct'",
        'msch_instrument'
    ));
    if (empty($direct_term_ids)) {
        return [];
    }
    return get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query'      => [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'term_id',
            'terms'    => array_map('intval', $direct_term_ids),
        ]],
    ]);
}

// Persist the selected view/category/status through the list's own search form submission
// (pagination and column sort links already carry them via the current URL, since they're
// generated with add_query_arg()).
add_action('restrict_manage_posts', 'rbm_msch_lesson_view_by_hidden_field');
function rbm_msch_lesson_view_by_hidden_field($post_type) {
    if ($post_type !== 'msch_lesson') {
        return;
    }
    echo '<input type="hidden" name="rbm_view" value="' . esc_attr(rbm_msch_lesson_current_view()) . '">';
    $current_category = rbm_msch_lesson_current_category();
    if ($current_category !== '') {
        echo '<input type="hidden" name="rbm_category" value="' . esc_attr($current_category) . '">';
    }
    echo '<input type="hidden" name="rbm_status" value="' . esc_attr(rbm_msch_lesson_current_status_filter()) . '">';
}

add_action('pre_get_posts', 'rbm_msch_lesson_filter_iwt_view');
function rbm_msch_lesson_filter_iwt_view($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'msch_lesson') {
        return;
    }
    // Status filter is independent of View By/Sort By, so it's applied unconditionally here.
    $status_filter = rbm_msch_lesson_current_status_filter();
    if ($status_filter === 'active') {
        $query->set('post_status', 'publish');
    } elseif ($status_filter === 'inactive') {
        $query->set('post_status', 'draft');
    } else {
        $query->set('post_status', ['publish', 'draft']);
    }
    if (rbm_msch_lesson_current_view() !== 'iwt') {
        $current_category = rbm_msch_lesson_current_category();
        if ($current_category !== '') {
            $query->set('tax_query', [[
                'taxonomy' => 'msch_instrument',
                'field'    => 'slug',
                'terms'    => [$current_category],
            ]]);
        }
        return;
    }
    global $wpdb;
    // Same direct query pattern as rbm_lessons_hide_faculty_only_categories() above: read term
    // meta directly rather than via get_terms(), which this filter doesn't need to touch.
    $direct_term_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT tm.term_id FROM {$wpdb->termmeta} tm
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id AND tt.taxonomy = %s
         WHERE tm.meta_key = '_msch_category_display_mode' AND tm.meta_value = 'direct'",
        'msch_instrument'
    ));
    if (empty($direct_term_ids)) {
        $query->set('post__in', [0]);
        return;
    }
    $query->set('tax_query', [[
        'taxonomy' => 'msch_instrument',
        'field'    => 'term_id',
        'terms'    => array_map('intval', $direct_term_ids),
    ]]);
    if ($status_filter === 'active') {
        // Drag-and-drop scope: show every reorderable row on one page, already in the order it
        // persists (docs/0913-1512-Copilot-REQUEST-Add-Drag-And-Drop-...).
        $query->set('posts_per_page', -1);
        $query->set('orderby', 'rbm_display_order');
        $query->set('order', 'ASC');
    }
}

// Instruments (Categories) list: replace the default "Description" column (unused for this
// taxonomy — icons/alt text are set via term meta instead) with a small icon thumbnail and an
// Active/Inactive indicator reflecting whether the category has at least one currently-Active
// (published) lesson.
add_filter('manage_edit-msch_instrument_columns', 'rbm_msch_instrument_admin_columns');
function rbm_msch_instrument_admin_columns($columns) {
    if (isset($columns['description'])) {
        unset($columns['description']);
    }
    // Bulk Actions is disabled on this list, so the row-select checkboxes serve no purpose.
    unset($columns['cb']);
    // Drag-and-drop reorder handle: only when Status = Active (same scope Reset Display Order uses).
    $drag_enabled = (rbm_msch_category_current_status_filter() === 'active');
    $new_columns = [];
    foreach ($columns as $key => $label) {
        if ($key === 'name') {
            if ($drag_enabled) {
                $new_columns['rbm_drag'] = '';
            }
            $new_columns['rbm_icon'] = 'Icon';
        }
        $new_columns[$key] = $label;
        if ($key === 'name') {
            $new_columns['rbm_status'] = 'Status';
            $new_columns['rbm_display_order'] = 'Display Order';
        }
    }
    return $new_columns;
}

add_filter('manage_msch_instrument_custom_column', 'rbm_msch_instrument_admin_column_content', 10, 3);
function rbm_msch_instrument_admin_column_content($content, $column_name, $term_id) {
    if ($column_name === 'rbm_drag') {
        return '<span class="dashicons dashicons-menu rbm-drag-handle" style="cursor:move;" title="Drag to reorder"></span>';
    }
    if ($column_name === 'rbm_icon') {
        $icon_url = rbm_msch_category_icon_url(get_term_meta($term_id, '_msch_category_icon_filename', true));
        if ($icon_url === '') {
            return '&#8212;';
        }
        return '<img src="' . esc_url($icon_url) . '" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">';
    }
    if ($column_name === 'rbm_display_order') {
        $order = get_term_meta($term_id, '_msch_category_display_order', true);
        return ($order === '') ? '&#8212;' : esc_html($order);
    }
    if ($column_name !== 'rbm_status') {
        return $content;
    }
    return rbm_msch_category_is_active($term_id)
        ? '<span style="color:#1a7e1a;font-weight:600;">Active</span>'
        : '<span style="color:#a00;font-weight:600;">Inactive</span>';
}

// Drag-and-drop scope (docs/0913-1512-Copilot-REQUEST-Add-Drag-And-Drop-...): force the
// Categories list into Display Order sort, and show every row on one page, whenever Status =
// Active (same scope Reset Display Order uses) — no need to click the Sort By column first.
add_action('load-edit-tags.php', 'rbm_msch_category_force_display_order_sort', 5);
function rbm_msch_category_force_display_order_sort() {
    if (($_GET['taxonomy'] ?? '') !== 'msch_instrument') {
        return;
    }
    if (rbm_msch_category_current_status_filter() === 'active') {
        $_GET['orderby'] = 'rbm_display_order';
        $_GET['order'] = 'asc';
    }
}

add_filter('get_terms_args', 'rbm_msch_category_unlimited_when_active', 10, 2);
function rbm_msch_category_unlimited_when_active($args, $taxonomies) {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['taxonomy'] ?? '') !== 'msch_instrument' || !in_array('msch_instrument', $taxonomies, true)) {
        return $args;
    }
    if (rbm_msch_category_current_status_filter() === 'active') {
        $args['number'] = 0;
    }
    return $args;
}

// Remembers this user's last filters/sort on the Categories list, so opening the page fresh (no
// query args) reopens where they left off. Generic by design (no hardcoded list of filter keys):
// whatever's in the URL besides taxonomy/post_type/paged gets saved. First-time visit (nothing
// saved yet) falls through to WP's own defaults: alphabetical by Name, no filters.
add_action('load-edit-tags.php', 'rbm_msch_category_remember_list_state');
function rbm_msch_category_remember_list_state() {
    if (($_GET['taxonomy'] ?? '') !== 'msch_instrument') {
        return;
    }
    $state = array_diff_key($_GET, array_flip(['taxonomy', 'post_type', 'paged']));
    $user_id = get_current_user_id();
    if (!empty($state)) {
        $sanitized = [];
        foreach ($state as $key => $value) {
            if (is_string($value)) {
                $sanitized[sanitize_key($key)] = sanitize_text_field(wp_unslash($value));
            }
        }
        update_user_meta($user_id, '_rbm_msch_category_list_state', $sanitized);
        return;
    }
    $saved = get_user_meta($user_id, '_rbm_msch_category_list_state', true);
    if (!empty($saved) && is_array($saved)) {
        wp_safe_redirect(add_query_arg($saved));
        exit;
    }
}

// Status filter (docs/0913-1319-Copilot-REQUEST-Add-Status-Filters-To-Instruments-And-Categories.txt):
// uses the existing rbm_msch_category_is_active() rule, no second definition of active.
function rbm_msch_category_current_status_filter() {
    $status = isset($_GET['rbm_status']) ? sanitize_key(wp_unslash($_GET['rbm_status'])) : 'active';
    return in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'active';
}

add_action('admin_notices', 'rbm_msch_category_status_filter_control');
function rbm_msch_category_status_filter_control() {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['taxonomy'] ?? '') !== 'msch_instrument') {
        return;
    }

    // Reset Display Order (docs/0913-1338-...): clears then renumbers _msch_category_display_order
    // (1, 2, 3...) by alpha name order, scoped to the same Active Categories this button is
    // enabled for; does not touch assignments, active state, names, or other term meta. Sits level
    // with Status, with the "Add Instrument" jump link (moved via JS) next to it.
    $reset_count = count(rbm_msch_category_display_order_reset_scope());
    $status_filter = rbm_msch_category_current_status_filter();
    $base_url = remove_query_arg(['rbm_status', 'paged']);
    // Reset Display Order only makes sense while viewing Active Categories (matches the view
    // Drag-and-Drop reordering will use).
    $reset_enabled = ($status_filter === 'active');
    ?>
    <div style="clear:both; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:20px; margin:10px 0;">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:20px;">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=msch_lesson')); ?>" class="button" style="font-weight:700; font-size:15px;" title="Switch to Instruments">&lt;&lt;</a>
            <div class="rbm-category-status-filter" style="display:flex; align-items:center; gap:6px;">
                <strong>Status:</strong>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'active', $base_url)); ?>" class="button<?php echo $status_filter === 'active' ? ' button-primary' : ''; ?>">Active</a>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'inactive', $base_url)); ?>" class="button<?php echo $status_filter === 'inactive' ? ' button-primary' : ''; ?>">Inactive</a>
                <a href="<?php echo esc_url(add_query_arg('rbm_status', 'all', $base_url)); ?>" class="button<?php echo $status_filter === 'all' ? ' button-primary' : ''; ?>">All</a>
            </div>
        </div>
        <div id="rbm-top-right-controls" style="display:flex; align-items:center; gap:10px;">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('This will clear Display Order for ALL Categories (including Inactive/All) and renumber the <?php echo (int) $reset_count; ?> in Active by alphabetical order (1, 2, 3...). Continue?');">
                <input type="hidden" name="action" value="rbm_reset_category_display_order">
                <?php wp_nonce_field('rbm_reset_category_display_order'); ?>
                <button type="submit" class="button"<?php disabled($reset_enabled, false); ?> title="<?php echo $reset_enabled ? '' : esc_attr('Only available in Active view'); ?>">Reset Display Order<?php echo $reset_count ? ' (' . (int) $reset_count . ')' : ''; ?></button>
            </form>
            <span id="rbm-add-instrument-slot"></span>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var addLink = document.getElementById('rbm-jump-to-add-instrument');
        var slot = document.getElementById('rbm-add-instrument-slot');
        if (addLink && slot) {
            addLink.className = 'button';
            slot.appendChild(addLink);
        }
    });
    </script>
    <?php
    if ($reset_enabled) :
        wp_enqueue_script('jquery-ui-sortable');
        ?>
        <script>
        jQuery(function ($) {
            var $list = $('#the-list');
            $list.sortable({
                items: '> tr',
                handle: '.rbm-drag-handle',
                axis: 'y',
                update: function () {
                    var ids = $list.children('tr').map(function () {
                        return parseInt($(this).attr('id').replace('tag-', ''), 10);
                    }).get();
                    $.post(ajaxurl, { action: 'rbm_save_category_display_order', nonce: '<?php echo esc_js(wp_create_nonce('rbm_drag_category_display_order')); ?>', ids: ids })
                        .done(function (r) {
                            if (!r || !r.success) {
                                location.reload();
                                return;
                            }
                            $list.children('tr').each(function (i) {
                                $(this).find('.column-rbm_display_order').text(i + 1);
                            });
                            $('<div class="notice notice-success is-dismissible"><p>Display order saved.</p></div>').insertAfter('.wp-heading-inline').delay(2000).fadeOut(300, function () { $(this).remove(); });
                        })
                        .fail(function () { location.reload(); });
                }
            });
        });
        </script>
        <?php
    endif;
}

add_action('admin_post_rbm_reset_category_display_order', 'rbm_reset_category_display_order');
function rbm_reset_category_display_order() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    check_admin_referer('rbm_reset_category_display_order');
    // Clear every Category's value first, including ones outside Active (Inactive, All), so no
    // stale numbers are left behind; then renumber just the enabled (Active) scope.
    global $wpdb;
    $all_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT tm.term_id FROM {$wpdb->termmeta} tm
         INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id AND tt.taxonomy = %s
         WHERE tm.meta_key = %s",
        'msch_instrument', '_msch_category_display_order'
    ));
    foreach ($all_ids as $id) {
        delete_term_meta((int) $id, '_msch_category_display_order');
    }
    $terms = rbm_msch_category_display_order_reset_scope();
    usort($terms, function ($a, $b) {
        return strcasecmp($a->name, $b->name);
    });
    foreach ($terms as $i => $term) {
        update_term_meta($term->term_id, '_msch_category_display_order', $i + 1);
    }
    wp_safe_redirect(admin_url('edit-tags.php?taxonomy=msch_instrument&post_type=msch_lesson&rbm_status=active&reset_display_order=1'));
    exit;
}

// Same Active scope the Reset button is enabled for (docs/0913-1319-..., 0913-1338-...).
function rbm_msch_category_display_order_reset_scope() {
    $terms = get_terms(['taxonomy' => 'msch_instrument', 'hide_empty' => false]);
    if (is_wp_error($terms)) {
        return [];
    }
    return array_values(array_filter($terms, function ($term) {
        return rbm_msch_category_is_active($term->term_id);
    }));
}

// Drag-and-drop AJAX save handlers (docs/0913-1512-Copilot-REQUEST-Add-Drag-And-Drop-...). Both
// reject any submitted ID list that doesn't exactly match the canonical scope helper's current set
// (no partial/extra/out-of-scope IDs), then derive sequential 1..N from the submitted order.
add_action('wp_ajax_rbm_save_lesson_display_order', 'rbm_ajax_save_lesson_display_order');
function rbm_ajax_save_lesson_display_order() {
    check_ajax_referer('rbm_drag_lesson_display_order', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }
    $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
    $scope_ids = wp_list_pluck(rbm_msch_lesson_display_order_reset_scope(), 'ID');
    sort($ids);
    sort($scope_ids);
    if ($ids !== $scope_ids) {
        wp_send_json_error('scope_mismatch', 400);
    }
    foreach ((array) ($_POST['ids'] ?? []) as $i => $id) {
        update_post_meta((int) $id, '_msch_lesson_display_order', $i + 1);
    }
    wp_send_json_success();
}

add_action('wp_ajax_rbm_save_category_display_order', 'rbm_ajax_save_category_display_order');
function rbm_ajax_save_category_display_order() {
    check_ajax_referer('rbm_drag_category_display_order', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }
    $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
    $scope_ids = wp_list_pluck(rbm_msch_category_display_order_reset_scope(), 'term_id');
    sort($ids);
    sort($scope_ids);
    if ($ids !== $scope_ids) {
        wp_send_json_error('scope_mismatch', 400);
    }
    foreach ((array) ($_POST['ids'] ?? []) as $i => $id) {
        update_term_meta((int) $id, '_msch_category_display_order', $i + 1);
    }
    wp_send_json_success();
}

// Filters (not sorts) the Categories list by Status, independent of the Sort By column below.
add_filter('get_terms', 'rbm_msch_instrument_filter_terms_by_status', 9, 3);
function rbm_msch_instrument_filter_terms_by_status($terms, $taxonomies, $args) {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['taxonomy'] ?? '') !== 'msch_instrument') {
        return $terms;
    }
    if (!is_array($terms) || empty($terms) || !($terms[0] instanceof WP_Term)) {
        return $terms;
    }
    $status_filter = rbm_msch_category_current_status_filter();
    if ($status_filter === 'all') {
        return $terms;
    }
    return array_values(array_filter($terms, function ($term) use ($status_filter) {
        $is_active = rbm_msch_category_is_active($term->term_id);
        return $status_filter === 'active' ? $is_active : !$is_active;
    }));
}

// Make Status sortable on the Categories list. Status isn't a plain DB column (it's the override/
// auto-detect logic in rbm_msch_category_is_active()), so instead of a SQL ORDER BY we sort the
// already-fetched $terms array via the 'get_terms' filter, scoped tightly to this exact admin screen
// so it never affects Faculty's screen or any other get_terms() call on the site.
add_filter('manage_edit-msch_instrument_sortable_columns', 'rbm_msch_instrument_sortable_columns');
function rbm_msch_instrument_sortable_columns($columns) {
    $columns['rbm_status'] = 'rbm_status';
    $columns['rbm_display_order'] = 'rbm_display_order';
    return $columns;
}

add_filter('get_terms', 'rbm_msch_instrument_sort_terms_by_status', 10, 3);
function rbm_msch_instrument_sort_terms_by_status($terms, $taxonomies, $args) {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['taxonomy'] ?? '') !== 'msch_instrument' || ($_GET['orderby'] ?? '') !== 'rbm_status') {
        return $terms;
    }
    if (!is_array($terms) || empty($terms) || !($terms[0] instanceof WP_Term)) {
        return $terms;
    }
    $direction = strtoupper((string) ($_GET['order'] ?? 'ASC')) === 'DESC' ? -1 : 1;
    usort($terms, function ($a, $b) use ($direction) {
        $a_active = rbm_msch_category_is_active($a->term_id) ? 1 : 0;
        $b_active = rbm_msch_category_is_active($b->term_id) ? 1 : 0;
        return ($a_active <=> $b_active) * $direction;
    });
    return $terms;
}

// Sort-by-column counterpart to rbm_msch_category_sort_terms(): always applies the Display Order
// comparator when this specific column is clicked, regardless of the global Category Order setting.
add_filter('get_terms', 'rbm_msch_instrument_sort_terms_by_display_order', 10, 3);
function rbm_msch_instrument_sort_terms_by_display_order($terms, $taxonomies, $args) {
    if (($GLOBALS['pagenow'] ?? '') !== 'edit-tags.php' || ($_GET['taxonomy'] ?? '') !== 'msch_instrument' || ($_GET['orderby'] ?? '') !== 'rbm_display_order') {
        return $terms;
    }
    if (!is_array($terms) || empty($terms) || !($terms[0] instanceof WP_Term)) {
        return $terms;
    }
    usort($terms, function ($a, $b) {
        $a_order = get_term_meta($a->term_id, '_msch_category_display_order', true);
        $b_order = get_term_meta($b->term_id, '_msch_category_display_order', true);
        $a_has = ($a_order !== '' && $a_order !== false);
        $b_has = ($b_order !== '' && $b_order !== false);
        if ($a_has && $b_has) {
            $cmp = (int) $a_order <=> (int) $b_order;
        } else {
            $cmp = ($a_has === $b_has) ? 0 : ($a_has ? -1 : 1);
        }
        return $cmp !== 0 ? $cmp : strcasecmp($a->name, $b->name);
    });
    return $terms;
}

// Manual override (set on the Edit Category screen) wins if present; otherwise fall back to the
// existing automatic rule (has at least one published Lesson tagged with this category).
function rbm_msch_category_is_active($term_id) {
    $override = get_term_meta($term_id, '_msch_category_status_override', true);
    if ($override === 'active' || $override === 'inactive') {
        return $override === 'active';
    }
    return !empty(get_posts([
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'tax_query'      => [[
            'taxonomy' => 'msch_instrument',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ]],
    ]));
}

// --- Lesson Tile Image catalog (plugin-owned folder, not the Media Library — see
// docs/0909-1356-Copilot-REQUEST-Move-Lessons-Tile-Images-To-Plugin-Catalog-Folder.txt) ---

// Alphabetical list of image filenames currently present in rbm-lessons/assets/images/.
function rbm_msch_lesson_catalog_images() {
    $dir = RBM_LESSONS_DIR . '/assets/images/';
    if (!is_dir($dir)) {
        return [];
    }
    $allowed_exts = ['png', 'jpg', 'jpeg', 'webp'];
    $files = [];
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..' || strpos($file, '.') === 0 || !is_file($dir . $file)) {
            continue; // skip hidden/system files and subdirectories
        }
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $allowed_exts, true)) {
            $files[] = $file;
        }
    }
    sort($files, SORT_STRING | SORT_FLAG_CASE);
    return $files;
}

// Public URL for a catalog filename; '' if the filename isn't currently a valid catalog image.
function rbm_msch_lesson_catalog_image_url($filename) {
    $filename = basename((string) $filename);
    if ($filename === '' || !in_array($filename, rbm_msch_lesson_catalog_images(), true)) {
        return '';
    }
    return RBM_LESSONS_URL . 'assets/images/' . rawurlencode($filename);
}

// Custom Image Alt Text if set, otherwise a safe SEO/accessibility fallback built from the lesson title.
function rbm_msch_lesson_image_alt($lesson_id, $title) {
    $custom = trim((string) get_post_meta($lesson_id, '_msch_lesson_image_alt', true));
    if ($custom !== '') {
        return $custom;
    }
    return trim((string) $title) . ' lessons at Red Barn Music School';
}

// --- Category icon catalog (docs/0909-1411-PLAN-Lessons-Category-Icon-Landing-And-Filtered-Tile-Groups.txt) ---
// One icon per msch_instrument term, stored as term meta pointing at a plugin-owned file (not the Media Library).

function rbm_msch_category_icon_images() {
    $dir = RBM_LESSONS_DIR . '/assets/category-icons/';
    if (!is_dir($dir)) {
        return [];
    }
    $allowed_exts = ['png', 'jpg', 'jpeg', 'webp'];
    $files = [];
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..' || strpos($file, '.') === 0 || !is_file($dir . $file)) {
            continue;
        }
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $allowed_exts, true)) {
            $files[] = $file;
        }
    }
    sort($files, SORT_STRING | SORT_FLAG_CASE);
    return $files;
}

function rbm_msch_category_icon_url($filename) {
    $filename = basename((string) $filename);
    if ($filename === '' || !in_array($filename, rbm_msch_category_icon_images(), true)) {
        return '';
    }
    return RBM_LESSONS_URL . 'assets/category-icons/' . rawurlencode($filename);
}

function rbm_msch_category_icon_alt($term_id, $term_name) {
    $custom = trim((string) get_term_meta($term_id, '_msch_category_icon_alt', true));
    if ($custom !== '') {
        return $custom;
    }
    return trim((string) $term_name) . ' music lessons at Red Barn Music School';
}

// --- Responsive tile image delivery (docs/0910-0334-Copilot-REQUEST-Harden-Lessons-Plugin-Image-Delivery.txt) ---
// Masters stay plugin-local (assets/images/, assets/category-icons/); 320/640/1024px derivatives are
// pre-generated one time into the same folder as "<base>-<width>.<ext>" and picked up here only if present,
// so a missing derivative never breaks the tile — it just falls back to the next available source.
function rbm_msch_responsive_tile_image($dir, $url_dir, $filename, $alt, $sizes_attr, $loading = 'lazy') {
    $filename = basename((string) $filename);
    if ($filename === '' || !is_file($dir . $filename)) {
        return '';
    }
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $candidates = [];
    foreach ([320, 640, 1024] as $w) {
        $derivative = $base . '-' . $w . '.' . $ext;
        if (is_file($dir . $derivative)) {
            $candidates[$w] = $derivative;
        }
    }
    $master_size = @getimagesize($dir . $filename);
    $master_width = ($master_size !== false) ? (int) $master_size[0] : null;
    $master_height = ($master_size !== false) ? (int) $master_size[1] : null;
    // Only add the master to srcset if it's actually wider than the largest derivative (avoids a duplicate).
    if ($master_width !== null && (empty($candidates) || $master_width > max(array_keys($candidates)))) {
        $candidates[$master_width] = $filename;
    }
    ksort($candidates, SORT_NUMERIC);
    $srcset_parts = [];
    foreach ($candidates as $w => $file) {
        $srcset_parts[] = esc_url($url_dir . rawurlencode($file)) . ' ' . (int) $w . 'w';
    }
    $html = '<img src="' . esc_url($url_dir . rawurlencode($filename)) . '"';
    if (!empty($srcset_parts)) {
        $html .= ' srcset="' . implode(', ', $srcset_parts) . '" sizes="' . esc_attr($sizes_attr) . '"';
    }
    if ($master_width !== null && $master_height !== null) {
        $html .= ' width="' . $master_width . '" height="' . $master_height . '"';
    }
    $html .= ' alt="' . esc_attr($alt) . '"';
    if ($loading) {
        $html .= ' loading="' . esc_attr($loading) . '"';
    }
    $html .= ' decoding="async">';
    return $html;
}

// Category Icon fields on the native msch_instrument Add/Edit Category screens (edit-tags.php) — no new admin screen.
add_action('msch_instrument_add_form_fields', 'rbm_msch_category_icon_add_form_field');
function rbm_msch_category_icon_add_form_field($taxonomy) {
    $catalog = rbm_msch_category_icon_images();
    ?>
    <div class="form-field">
        <label for="rbm_category_icon_filename">Category Icon</label>
        <select name="rbm_category_icon_filename" id="rbm_category_icon_filename">
            <option value="">No Icon</option>
            <?php foreach ($catalog as $file) : ?>
                <option value="<?php echo esc_attr($file); ?>"><?php echo esc_html($file); ?></option>
            <?php endforeach; ?>
        </select>
        <p>Populated from <code>wp-content/plugins/rbm-instruments/assets/category-icons/</code> — add files there to expand this list.</p>
    </div>
    <div class="form-field">
        <label for="rbm_category_icon_alt">Icon Alt Text</label>
        <input type="text" name="rbm_category_icon_alt" id="rbm_category_icon_alt" value="">
        <p>Leave blank to use "{Category} music lessons at Red Barn Music School".</p>
    </div>
    <div class="form-field">
        <label>Tile Preview</label>
        <?php rbm_msch_category_tile_preview_markup('', ''); ?>
    </div>
    <?php
    rbm_msch_category_tile_preview_script($catalog, 'tag-name');
}

add_action('msch_instrument_edit_form_fields', 'rbm_msch_category_icon_edit_form_field');
function rbm_msch_category_icon_edit_form_field($term) {
    $catalog = rbm_msch_category_icon_images();
    $current_icon = get_term_meta($term->term_id, '_msch_category_icon_filename', true);
    $current_alt = get_term_meta($term->term_id, '_msch_category_icon_alt', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="rbm_category_icon_filename">Category Icon</label></th>
        <td>
            <select name="rbm_category_icon_filename" id="rbm_category_icon_filename">
                <option value="">No Icon</option>
                <?php foreach ($catalog as $file) : ?>
                    <option value="<?php echo esc_attr($file); ?>" <?php selected($current_icon, $file); ?>><?php echo esc_html($file); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description">Populated from <code>wp-content/plugins/rbm-instruments/assets/category-icons/</code> — add files there to expand this list.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="rbm_category_icon_alt">Icon Alt Text</label></th>
        <td>
            <input type="text" name="rbm_category_icon_alt" id="rbm_category_icon_alt" value="<?php echo esc_attr($current_alt); ?>" class="regular-text">
            <p class="description">Leave blank to use "{Category} music lessons at Red Barn Music School".</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label>Tile Preview</label></th>
        <td>
            <?php rbm_msch_category_tile_preview_markup(rbm_msch_category_icon_url($current_icon), $term->name); ?>
        </td>
    </tr>
    <?php
    rbm_msch_category_tile_preview_script($catalog, 'name');
}

// Shared markup/JS for the Category-tile live preview on both the Add and Edit Category screens —
// mirrors the real .msch-lesson-category-card/-icon/-name markup+CSS from the public Lessons page
// (rbm_msch_lessons_shortcode()) so what the admin sees here matches what visitors actually see.
function rbm_msch_category_tile_preview_markup($icon_url, $name) {
    ?>
    <div id="rbm_category_tile_preview" style="display:inline-flex;flex-direction:column;align-items:center;gap:6px;padding:16px;border:1px solid #ddd;border-radius:8px;background:#fff;width:180px;text-align:center;">
        <span id="rbm_category_tile_preview_icon" style="display:flex;align-items:center;justify-content:center;min-height:80px;">
            <?php if ($icon_url !== '') : ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="" style="max-width:120px;height:auto;display:block;">
            <?php endif; ?>
        </span>
        <span id="rbm_category_tile_preview_name" style="font-size:15px;font-weight:600;"><?php echo esc_html($name); ?></span>
    </div>
    <?php
}

function rbm_msch_category_tile_preview_script($catalog, $name_field_id) {
    ?>
    <script>
    (function(){
        var urls = <?php echo wp_json_encode(array_combine($catalog, array_map('rbm_msch_category_icon_url', $catalog))); ?>;
        var select = document.getElementById('rbm_category_icon_filename');
        var nameField = document.getElementById('<?php echo esc_js($name_field_id); ?>');
        if (select) {
            select.addEventListener('change', function (e) {
                var icon = document.getElementById('rbm_category_tile_preview_icon');
                var url = urls[e.target.value];
                icon.innerHTML = url ? '<img src="' + url + '" alt="" style="max-width:120px;height:auto;display:block;">' : '';
            });
        }
        if (nameField) {
            nameField.addEventListener('input', function (e) {
                document.getElementById('rbm_category_tile_preview_name').textContent = e.target.value;
            });
        }
    })();
    </script>
    <?php
}

add_action('created_msch_instrument', 'rbm_msch_category_icon_save_term_meta');
add_action('edited_msch_instrument', 'rbm_msch_category_icon_save_term_meta');
function rbm_msch_category_icon_save_term_meta($term_id) {
    if (!isset($_POST['rbm_category_icon_filename'])) {
        return;
    }
    $filename = basename(sanitize_text_field(wp_unslash($_POST['rbm_category_icon_filename'])));
    if ($filename !== '' && in_array($filename, rbm_msch_category_icon_images(), true)) {
        update_term_meta($term_id, '_msch_category_icon_filename', $filename);
    } else {
        delete_term_meta($term_id, '_msch_category_icon_filename');
    }
    update_term_meta($term_id, '_msch_category_icon_alt', sanitize_text_field(wp_unslash($_POST['rbm_category_icon_alt'] ?? '')));
}

// Manual Active/Inactive override for a Category — supplements the automatic Status column rule
// (see rbm_msch_category_is_active()) for cases where a Category should be forced on/off regardless
// of its current published-Lesson count.
add_action('msch_instrument_add_form_fields', 'rbm_msch_category_status_add_form_field');
function rbm_msch_category_status_add_form_field($taxonomy) {
    ?>
    <div class="form-field">
        <label>Status Override</label>
        <fieldset>
            <label><input type="radio" name="rbm_category_status_override" value="" checked> Automatic (based on published Instruments)</label><br>
            <label><input type="radio" name="rbm_category_status_override" value="active"> Active</label><br>
            <label><input type="radio" name="rbm_category_status_override" value="inactive"> Inactive</label>
        </fieldset>
        <p>Overrides the automatic Status shown in the Categories list. Leave on Automatic unless this Category needs to be forced Active or Inactive.</p>
    </div>
    <?php
}

add_action('msch_instrument_edit_form_fields', 'rbm_msch_category_status_edit_form_field');
function rbm_msch_category_status_edit_form_field($term) {
    $current_override = get_term_meta($term->term_id, '_msch_category_status_override', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label>Status Override</label></th>
        <td>
            <fieldset>
                <label><input type="radio" name="rbm_category_status_override" value="" <?php checked($current_override, ''); ?>> Automatic (based on published Instruments)</label><br>
                <label><input type="radio" name="rbm_category_status_override" value="active" <?php checked($current_override, 'active'); ?>> Active</label><br>
                <label><input type="radio" name="rbm_category_status_override" value="inactive" <?php checked($current_override, 'inactive'); ?>> Inactive</label>
            </fieldset>
            <p class="description">Overrides the automatic Status shown in the Categories list. Leave on Automatic unless this Category needs to be forced Active or Inactive.</p>
        </td>
    </tr>
    <?php
}

add_action('created_msch_instrument', 'rbm_msch_category_status_save_term_meta');
add_action('edited_msch_instrument', 'rbm_msch_category_status_save_term_meta');
function rbm_msch_category_status_save_term_meta($term_id) {
    if (!isset($_POST['rbm_category_status_override'])) {
        return;
    }
    $override = sanitize_text_field(wp_unslash($_POST['rbm_category_status_override']));
    if ($override === 'active' || $override === 'inactive') {
        update_term_meta($term_id, '_msch_category_status_override', $override);
    } else {
        delete_term_meta($term_id, '_msch_category_status_override');
    }
}

// Direct Display mode for a Category (docs/0912-1650-PLAN-Direct-Display-Lessons-Category.txt):
// when set, the category's own tile/select option is not rendered on the Lessons page — its
// assigned Active lessons render immediately instead. Same category record, assignments, and
// instrument-card renderer as every other category; this is only an additional display behavior.
add_action('msch_instrument_add_form_fields', 'rbm_msch_category_display_mode_add_form_field');
function rbm_msch_category_display_mode_add_form_field($taxonomy) {
    ?>
    <div class="form-field">
        <label>Display Mode</label>
        <fieldset>
            <label><input type="radio" name="rbm_category_display_mode" value="" checked> Normal Category Tile</label><br>
            <label><input type="radio" name="rbm_category_display_mode" value="direct"> Display Assigned Instruments Directly</label>
        </fieldset>
        <p>Direct Display categories don't show as a clickable tile — their assigned Active Instruments appear immediately on the Lessons page instead.</p>
    </div>
    <?php
}

add_action('msch_instrument_edit_form_fields', 'rbm_msch_category_display_mode_edit_form_field');
function rbm_msch_category_display_mode_edit_form_field($term) {
    $current_mode = get_term_meta($term->term_id, '_msch_category_display_mode', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label>Display Mode</label></th>
        <td>
            <fieldset>
                <label><input type="radio" name="rbm_category_display_mode" value="" <?php checked($current_mode, ''); ?>> Normal Category Tile</label><br>
                <label><input type="radio" name="rbm_category_display_mode" value="direct" <?php checked($current_mode, 'direct'); ?>> Display Assigned Instruments Directly</label>
            </fieldset>
            <p class="description">Direct Display categories don't show as a clickable tile — their assigned Active Instruments appear immediately on the Lessons page instead.</p>
        </td>
    </tr>
    <?php
}

add_action('created_msch_instrument', 'rbm_msch_category_display_mode_save_term_meta');
add_action('edited_msch_instrument', 'rbm_msch_category_display_mode_save_term_meta');
function rbm_msch_category_display_mode_save_term_meta($term_id) {
    if (!isset($_POST['rbm_category_display_mode'])) {
        return;
    }
    $mode = sanitize_text_field(wp_unslash($_POST['rbm_category_display_mode']));
    if ($mode === 'direct') {
        update_term_meta($term_id, '_msch_category_display_mode', 'direct');
    } else {
        delete_term_meta($term_id, '_msch_category_display_mode');
    }
}

function rbm_msch_category_is_direct_display($term_id) {
    return get_term_meta($term_id, '_msch_category_display_mode', true) === 'direct';
}

// Category Display Order (docs/0913-1208-Copilot-REQUEST-Implement-Category-Display-Order.txt):
// optional numeric term meta on normal Categories, used only when Category Order is set to
// Display Order (see rbm_msch_category_order_mode() in rbm-lessons-display-settings.php). Not
// applicable to Instruments We Teach / Direct Display, which is never shown in an ordered list.
add_action('msch_instrument_add_form_fields', 'rbm_msch_category_display_order_add_form_field');
function rbm_msch_category_display_order_add_form_field($taxonomy) {
    ?>
    <div class="form-field">
        <label for="rbm_category_display_order">Display Order</label>
        <input type="number" step="1" id="rbm_category_display_order" name="rbm_category_display_order" value="">
        <p>Optional. Only used when Category Order is set to Display Order (Instruments Page Display settings). Lower numbers appear first; ties and blank values fall back to alphabetical, with blank always sorting after any numbered Category. Not used for Instruments We Teach / Direct Display.</p>
    </div>
    <?php
}

add_action('msch_instrument_edit_form_fields', 'rbm_msch_category_display_order_edit_form_field');
function rbm_msch_category_display_order_edit_form_field($term) {
    $current_order = get_term_meta($term->term_id, '_msch_category_display_order', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="rbm_category_display_order">Display Order</label></th>
        <td>
            <input type="number" step="1" id="rbm_category_display_order" name="rbm_category_display_order" value="<?php echo esc_attr($current_order); ?>">
            <p class="description">Optional. Only used when Category Order is set to Display Order (Instruments Page Display settings). Lower numbers appear first; ties and blank values fall back to alphabetical, with blank always sorting after any numbered Category. Not used for Instruments We Teach / Direct Display.</p>
        </td>
    </tr>
    <?php
}

add_action('created_msch_instrument', 'rbm_msch_category_display_order_save_term_meta');
add_action('edited_msch_instrument', 'rbm_msch_category_display_order_save_term_meta');
function rbm_msch_category_display_order_save_term_meta($term_id) {
    if (!isset($_POST['rbm_category_display_order'])) {
        return;
    }
    $raw = trim((string) wp_unslash($_POST['rbm_category_display_order']));
    if ($raw === '' || !is_numeric($raw)) {
        delete_term_meta($term_id, '_msch_category_display_order');
        return;
    }
    update_term_meta($term_id, '_msch_category_display_order', (int) $raw);
}

// Sorts msch_instrument term objects per the selected Category Order setting (docs/0913-1208-...).
// Alphabetical (default): current A-Z by name, unchanged.
// Display Order: numbered Categories low-to-high; ties and blank/unset values fall back to A-Z,
// with blank/unset always sorting after any numbered Category.
function rbm_msch_category_sort_terms($terms) {
    if (rbm_msch_category_order_mode() !== 'display_order') {
        usort($terms, function ($a, $b) {
            return strcasecmp($a->name, $b->name);
        });
        return $terms;
    }
    usort($terms, function ($a, $b) {
        $a_order = get_term_meta($a->term_id, '_msch_category_display_order', true);
        $b_order = get_term_meta($b->term_id, '_msch_category_display_order', true);
        $a_has = ($a_order !== '' && $a_order !== false);
        $b_has = ($b_order !== '' && $b_order !== false);
        if ($a_has && $b_has) {
            $cmp = (int) $a_order <=> (int) $b_order;
        } else {
            $cmp = ($a_has === $b_has) ? 0 : ($a_has ? -1 : 1);
        }
        return $cmp !== 0 ? $cmp : strcasecmp($a->name, $b->name);
    });
    return $terms;
}

// Sorts msch_lesson post objects per the selected Instrument Tile Order setting
// (docs/0913-1226-Copilot-REQUEST-Add-Instrument-Display-Order.txt).
// Alphabetical (default): title A-Z, unchanged.
// Display Order: numbered Instruments low-to-high; ties and blank/unset values fall back to A-Z,
// with blank/unset always sorting after any numbered Instrument.
function rbm_msch_lesson_sort_posts($lessons) {
    if (rbm_msch_lesson_tile_order_mode() !== 'display_order') {
        usort($lessons, function ($a, $b) {
            return strcasecmp($a->post_title, $b->post_title);
        });
        return $lessons;
    }
    usort($lessons, function ($a, $b) {
        $a_order = get_post_meta($a->ID, '_msch_lesson_display_order', true);
        $b_order = get_post_meta($b->ID, '_msch_lesson_display_order', true);
        $a_has = ($a_order !== '' && $a_order !== false);
        $b_has = ($b_order !== '' && $b_order !== false);
        if ($a_has && $b_has) {
            $cmp = (int) $a_order <=> (int) $b_order;
        } else {
            $cmp = ($a_has === $b_has) ? 0 : ($a_has ? -1 : 1);
        }
        return $cmp !== 0 ? $cmp : strcasecmp($a->post_title, $b->post_title);
    });
    return $lessons;
}

// True if this Instrument (Lesson) is assigned to the existing Direct Display ("Instruments We
// Teach") category. Reads the live term relationship only — no separate yes/no value is stored.
function rbm_msch_lesson_is_instruments_we_teach($post_id) {
    $terms = get_the_terms($post_id, 'msch_instrument');
    if (!is_array($terms) || is_wp_error($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if (rbm_msch_category_is_direct_display($term->term_id)) {
            return true;
        }
    }
    return false;
}

// --- [msch_lessons] shortcode (Stage 4: public tile grid only — image, name, Sign Up button) ---

// Shared single-card renderer (image, title, Sign Up button) — used both by the normal filtered
// tile grid and by the Direct Display section below, so there is exactly one card implementation.
function rbm_msch_lesson_card_html($lesson, $hidden = false) {
    $signup_url = get_post_meta($lesson->ID, '_msch_lesson_signup_url', true);
    $image_filename = get_post_meta($lesson->ID, '_msch_lesson_image_filename', true);
    $image_url = rbm_msch_lesson_catalog_image_url($image_filename);
    $tile_image = ($image_url !== '')
        ? rbm_msch_responsive_tile_image(
            RBM_LESSONS_DIR . '/assets/images/',
            RBM_LESSONS_URL . 'assets/images/',
            $image_filename,
            rbm_msch_lesson_image_alt($lesson->ID, get_the_title($lesson)),
            '(max-width: 260px) 100vw, 260px'
        )
        : '';
    $l_terms = get_the_terms($lesson->ID, 'msch_instrument');
    $l_slugs = (is_array($l_terms) && !is_wp_error($l_terms)) ? wp_list_pluck($l_terms, 'slug') : [];
    ob_start();
    ?>
    <div class="thesis-lesson-card" data-msch-lesson-instruments="<?php echo esc_attr(implode(',', $l_slugs)); ?>"<?php echo $hidden ? ' style="display:none;"' : ''; ?>>
        <span class="thesis-lesson-card-image"><?php echo $tile_image; ?></span>
        <span class="thesis-lesson-card-title"><?php echo esc_html(get_the_title($lesson)); ?></span>
        <span style="display:block;padding:4px 16px 16px;">
            <?php if (!empty($signup_url)) : ?>
                <a class="thesis-cta-button" href="<?php echo esc_url($signup_url); ?>" target="_blank" rel="noopener noreferrer">Sign Up</a>
            <?php endif; ?>
            <a class="thesis-cta-button" style="margin-left:6px;" href="<?php echo esc_url(rbm_msch_faculty_filter_url($lesson->post_name)); ?>">Meet the Teachers</a>
        </span>
    </div>
    <?php
    return ob_get_clean();
}

// Docs/0913-1644-Copilot-REQUEST-Implement-Teacher-By-Instrument-Filtering-And-Tile-Links.txt:
// resolves the Faculty page URL dynamically (never hardcoded) with ?instrument=<canonical Lesson
// slug> appended, so rbm-faculty's server-side filter can match this exact Instrument. Also carries
// the current Lessons page URL as ?return= (docs/0913-1731-...) so rbm-faculty's terminal fallback
// can send a visitor back to the exact Lessons context they came from, same-site only.
function rbm_msch_faculty_filter_url($lesson_slug) {
    $page = get_posts(['post_type' => 'page', 'name' => 'faculty', 'posts_per_page' => 1]);
    $base = !empty($page) ? get_permalink($page[0]) : home_url('/faculty/');
    $url = add_query_arg('instrument', $lesson_slug, $base);
    $current_url = home_url(add_query_arg(null, null));
    return add_query_arg('return', rawurlencode($current_url), $url);
}

add_shortcode('msch_lessons', 'rbm_msch_lessons_shortcode');
function rbm_msch_lessons_shortcode($atts) {
    $atts = shortcode_atts([
        'instrument' => '',
        'order'      => 'name', // "name" = alphabetical; "manual" = numeric Order field
    ], $atts, 'msch_lessons');

    $query_args = [
        'post_type'      => 'msch_lesson',
        'post_status'    => 'publish', // Active only (publish/draft = Active/Inactive, see Stage 2/3)
        'posts_per_page' => -1,
        'orderby'        => ($atts['order'] === 'manual')
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

    $lessons = get_posts($query_args);
    if (empty($lessons)) {
        return '';
    }
    // Instrument Tile Order setting (docs/0913-1226-...) only governs the default alphabetical
    // path; an explicit order="manual" shortcode attribute keeps using the native menu_order field
    // untouched, exactly as before.
    if ($atts['order'] !== 'manual') {
        $lessons = rbm_msch_lesson_sort_posts($lessons);
    }

    // Distinct instrument terms among the lessons actually being displayed, ordered per the
    // selected Category Order setting (docs/0913-1208-...; alphabetical by default, unchanged).
    $filter_terms = [];
    $filter_term_objects = [];
    foreach ($lessons as $l) {
        $l_terms = get_the_terms($l->ID, 'msch_instrument');
        if (is_array($l_terms) && !is_wp_error($l_terms)) {
            foreach ($l_terms as $term) {
                // Honor the existing canonical Category Active/Inactive status (docs/0913-1639-...):
                // a Category with a manual Inactive override must not appear as a public tile/filter
                // option, even if it still has a published Lesson.
                if (!rbm_msch_category_is_active($term->term_id)) {
                    continue;
                }
                $filter_terms[$term->slug] = $term->name;
                $filter_term_objects[$term->slug] = $term;
            }
        }
    }

    // Direct Display categories (docs/0912-1650-PLAN-Direct-Display-Lessons-Category.txt): pull
    // these out of the normal tile/select list — they don't get a clickable tile — and separately
    // query their assigned Active lessons for immediate display further below. Only applies to the
    // main, unfiltered shortcode instance (a per-category instance already shows only that category).
    $direct_slugs = [];
    if (empty($atts['instrument'])) {
        foreach ($filter_terms as $slug => $name) {
            $term = get_term_by('slug', $slug, 'msch_instrument');
            if ($term && rbm_msch_category_is_direct_display($term->term_id)) {
                $direct_slugs[] = $slug;
            }
        }
        foreach ($direct_slugs as $slug) {
            unset($filter_terms[$slug]);
            unset($filter_term_objects[$slug]);
        }
    }

    $sorted_filter_terms = rbm_msch_category_sort_terms(array_values($filter_term_objects));
    $filter_terms = [];
    foreach ($sorted_filter_terms as $term) {
        $filter_terms[$term->slug] = $term->name;
    }
    $direct_lessons = [];
    if (!empty($direct_slugs)) {
        $direct_lessons = get_posts([
            'post_type'      => 'msch_lesson',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => ($atts['order'] === 'manual')
                ? ['menu_order' => 'ASC', 'title' => 'ASC']
                : ['title' => 'ASC'],
            'tax_query'      => [[
                'taxonomy' => 'msch_instrument',
                'field'    => 'slug',
                'terms'    => $direct_slugs,
            ]],
        ]);
        if ($atts['order'] !== 'manual') {
            $direct_lessons = rbm_msch_lesson_sort_posts($direct_lessons);
        }
    }

    // Lessons Page Display admin selector (docs/0912-1751-...): '' means no explicit choice has
    // been saved yet, which preserves this pre-existing combined rendering unchanged. Only applies
    // to the main/unfiltered shortcode instance, same scope as the Direct Display gating above.
    $rbm_display_mode = empty($atts['instrument']) ? rbm_lessons_get_display_mode() : '';
    if ($rbm_display_mode === 'none') {
        return '<div class="msch-lessons msch-lessons-under-construction"><p>Under Construction</p><p>Please check back later.</p></div>';
    }
    if ($rbm_display_mode === 'categories') {
        $direct_lessons = [];
    }
    $rbm_hide_categories_section = ($rbm_display_mode === 'instruments');
    if ($rbm_hide_categories_section) {
        $filter_terms = [];
    }

    static $rbm_lessons_instance = 0;
    $rbm_lessons_instance++;
    $filter_id = 'msch-lesson-filter-' . $rbm_lessons_instance;

    // docs/0913-1010-Copilot-REQUEST-Add-Choose-Instrument-Pill-Display-Option.txt: independent
    // Show/Hide control for the green Choose Instrument pill, decoupled from the display mode
    // above. Default 'hide' preserves the prior forced-hidden behavior (category icon grid was
    // the only visible way to choose a category; select stayed in the DOM only to drive
    // Previous/Next and deep-link JS).
    $rbm_choose_instrument_pill_display = (rbm_instruments_get_choose_instrument_pill_mode() === 'show') ? 'block' : 'none';

    static $rbm_lessons_css_printed = false;
    ob_start();
    if (!$rbm_lessons_css_printed) {
        $rbm_lessons_css_printed = true;
        ?>
        <style>
        .msch-lesson-filter{display:<?php echo esc_attr($rbm_choose_instrument_pill_display); ?>;margin:0 0 1.5em;}
        .msch-lesson-filter-select{
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
            max-width:100%;
            transition:background-color 0.15s ease;
        }
        .msch-lesson-filter-select:hover,
        .msch-lesson-filter-select:focus{
            background:#5aa932;
            outline:none;
        }
        .msch-lesson-category-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
            gap:4px;
            margin:0 0 1.5em;
        }
        .msch-lesson-category-card{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:6px;
            padding:6px;
            border:1px solid #ddd;
            border-radius:8px;
            background:#fff;
            cursor:pointer;
            font:inherit;
            color:inherit;
            text-align:center;
        }
        .msch-lesson-category-card:hover,
        .msch-lesson-category-card:focus-visible{
            border-color:#6cbf3f;
            outline:none;
        }
        .msch-lesson-category-card.is-active{
            background:#eef7e6;
            border-color:#6cbf3f;
        }
        .msch-lesson-category-card.is-active .msch-lesson-category-name{
            font-weight:700;
            text-decoration:underline;
        }
        .msch-lesson-category-icon img{
            max-width:220px;
            height:auto;
            display:block;
        }
        .msch-lesson-category-icon--placeholder{
            width:220px;
            height:220px;
            border-radius:50%;
            background:#f0f0f0;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:56px;
            font-weight:700;
            color:#888;
        }
        .msch-lesson-category-name{
            font-size:20px;
            font-weight:600;
        }
        .msch-lesson-tiles-wrap[hidden]{
            display:none;
        }
        .msch-lesson-category-grid[hidden]{
            display:none;
        }
        .msch-lesson-tiles-header{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:12px;
            margin:0 0 1em;
            text-align:center;
        }
        .msch-lesson-tiles-category-name{
            font-size:20px;
            font-weight:700;
        }
        .msch-lesson-tiles-nav{
            display:flex;
            align-items:center;
            justify-content:center;
            flex-wrap:wrap;
            gap:12px;
        }
        .msch-lesson-tiles-nav button{
            font-size:14px;
            font-weight:600;
            font-family:inherit;
            padding:8px 16px;
            border:1px solid #6cbf3f;
            border-radius:999px;
            background:#fff;
            color:#5aa932;
            cursor:pointer;
        }
        .msch-lesson-tiles-nav button:hover,
        .msch-lesson-tiles-nav button:focus-visible{
            background:#eef7e6;
            outline:none;
        }
        .msch-lesson-tiles-nav button:disabled{
            opacity:0.45;
            cursor:not-allowed;
        }
        .msch-lesson-tiles-category-name:empty{
            display:none;
        }
        </style>
        <script>
        document.addEventListener('change', function (e) {
            if (!e.target || !e.target.classList || !e.target.classList.contains('msch-lesson-filter-select')) {
                return;
            }
            var container = e.target.closest('.msch-lessons');
            // Scoped to the filterable tiles wrap only — the Direct Display grid (docs/0912-1650-
            // PLAN-Direct-Display-Lessons-Category.txt) also uses .thesis-lesson-card-grid but is
            // never filtered/hidden by category selection, so it must not be matched here.
            var grid = container ? container.querySelector('.msch-lesson-tiles-wrap .thesis-lesson-card-grid') : null;
            if (!grid) {
                return;
            }
            var value = e.target.value;
            var cards = grid.querySelectorAll('[data-msch-lesson-instruments]');
            cards.forEach(function (card) {
                if (!value || value === 'all') {
                    card.style.display = '';
                    return;
                }
                var terms = (card.getAttribute('data-msch-lesson-instruments') || '').split(',');
                card.style.display = (terms.indexOf(value) !== -1) ? '' : 'none';
            });
        }, false);
        // Top nav (green control + Previous/Back/Next) is persistent above the category grid / tile grid in both
        // states (docs/0910-0313 menu-bar screenshot request): reveal the lesson tiles and hide the category
        // grid only once a real category (or All Lessons) is chosen, label the category view, keep the
        // shareable ?category= URL param in sync, and refresh Previous/Back/Next enabled state (never hidden).
        // All lookups are scoped to the shortcode's own `.msch-lessons` container so DOM order can change freely.
        document.addEventListener('change', function (e) {
            if (!e.target || !e.target.classList || !e.target.classList.contains('msch-lesson-filter-select')) {
                return;
            }
            var container = e.target.closest('.msch-lessons');
            if (!container) {
                return;
            }
            var tilesWrap = container.querySelector('.msch-lesson-tiles-wrap');
            if (!tilesWrap) {
                return;
            }
            var grid = container.querySelector('.msch-lesson-category-grid');
            var nameEl = container.querySelector('.msch-lesson-tiles-category-name');
            var prevBtn = container.querySelector('.msch-lesson-tiles-prev');
            var backBtn = container.querySelector('.msch-lesson-tiles-back');
            var nextBtn = container.querySelector('.msch-lesson-tiles-next');
            var value = e.target.value;
            var url = new URL(window.location.href);
            if (!value) {
                tilesWrap.hidden = true;
                if (grid) {
                    grid.hidden = false;
                }
                if (nameEl) {
                    nameEl.textContent = '';
                }
                if (prevBtn) { prevBtn.disabled = true; }
                if (backBtn) { backBtn.disabled = true; }
                if (nextBtn) { nextBtn.disabled = true; }
                url.searchParams.delete('category');
                window.history.replaceState(null, '', url);
                return;
            }
            tilesWrap.hidden = false;
            if (grid) {
                grid.hidden = true;
            }
            var selectedOption = e.target.options[e.target.selectedIndex];
            if (nameEl && selectedOption) {
                nameEl.textContent = selectedOption.text;
            }
            if (backBtn) {
                backBtn.disabled = false;
            }
            // Previous/Next: walk the same alphabetical category list as the select; "All Lessons" doesn't participate
            // and keeps both buttons disabled (still visible) rather than hidden.
            var categoryOptions = Array.prototype.filter.call(e.target.options, function (o) { return o.value && o.value !== 'all'; });
            var categoryIndex = categoryOptions.findIndex(function (o) { return o.value === value; });
            if (prevBtn) {
                prevBtn.disabled = !(categoryIndex > 0);
            }
            if (nextBtn) {
                nextBtn.disabled = !(categoryIndex !== -1 && categoryIndex < categoryOptions.length - 1);
            }
            if (value === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', value);
            }
            window.history.replaceState(null, '', url);
        }, false);
        // Back to Categories: hide the tile view, reset the select, restore the category grid, clear the active
        // card, and disable (but keep visible) all three top nav buttons.
        document.addEventListener('click', function (e) {
            var backBtn = e.target.closest('.msch-lesson-tiles-back');
            if (!backBtn || backBtn.disabled) {
                return;
            }
            var container = backBtn.closest('.msch-lessons');
            if (!container) {
                return;
            }
            var tilesWrap = container.querySelector('.msch-lesson-tiles-wrap');
            if (tilesWrap) {
                tilesWrap.hidden = true;
            }
            var select = container.querySelector('.msch-lesson-filter-select');
            if (select) {
                select.selectedIndex = 0;
            }
            var grid = container.querySelector('.msch-lesson-category-grid');
            if (grid) {
                grid.hidden = false;
                grid.querySelectorAll('.msch-lesson-category-card').forEach(function (card) {
                    card.classList.remove('is-active');
                    card.setAttribute('aria-pressed', 'false');
                });
            }
            var nameEl = container.querySelector('.msch-lesson-tiles-category-name');
            if (nameEl) {
                nameEl.textContent = '';
            }
            var prevBtn = container.querySelector('.msch-lesson-tiles-prev');
            var nextBtn = container.querySelector('.msch-lesson-tiles-next');
            if (prevBtn) { prevBtn.disabled = true; }
            if (nextBtn) { nextBtn.disabled = true; }
            backBtn.disabled = true;
            var url = new URL(window.location.href);
            url.searchParams.delete('category');
            window.history.replaceState(null, '', url);
        }, false);
        // Previous/Next: step through the same alphabetical category list as the Choose Instrument select.
        // Scoped only to this shortcode's Instrument View; does not touch the legacy per-page category nav.
        document.addEventListener('click', function (e) {
            var navBtn = e.target.closest('.msch-lesson-tiles-prev, .msch-lesson-tiles-next');
            if (!navBtn || navBtn.disabled) {
                return;
            }
            var container = navBtn.closest('.msch-lessons');
            var select = container ? container.querySelector('.msch-lesson-filter-select') : null;
            if (!select) {
                return;
            }
            var categoryOptions = Array.prototype.filter.call(select.options, function (o) { return o.value && o.value !== 'all'; });
            var currentIndex = categoryOptions.findIndex(function (o) { return o.value === select.value; });
            if (currentIndex === -1) {
                return;
            }
            var nextIndex = currentIndex + (navBtn.classList.contains('msch-lesson-tiles-prev') ? -1 : 1);
            if (nextIndex < 0 || nextIndex >= categoryOptions.length) {
                return;
            }
            select.value = categoryOptions[nextIndex].value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }, false);
        // Deep link support: ?category=slug pre-selects a category on page load. Server-side rendering
        // (Stage 1 hardening) already renders the correct initial state, so this only needs to (a) sync any
        // additional .msch-lessons instances whose select doesn't already match, and (b) strip an invalid
        // ?category= value from the URL so it doesn't linger next to the visible Category View (Stage 2).
        document.addEventListener('DOMContentLoaded', function () {
            var initial = new URLSearchParams(window.location.search).get('category');
            if (!initial) {
                return;
            }
            var matchedAny = false;
            document.querySelectorAll('.msch-lesson-filter-select').forEach(function (select) {
                var hasOption = Array.prototype.some.call(select.options, function (o) { return o.value === initial; });
                if (!hasOption) {
                    return;
                }
                matchedAny = true;
                if (select.value !== initial) {
                    select.value = initial;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            if (!matchedAny) {
                var url = new URL(window.location.href);
                url.searchParams.delete('category');
                window.history.replaceState(null, '', url);
            }
        }, false);
        // Category card <-> Choose Instrument select stay synchronized (Stage 4).
        document.addEventListener('click', function (e) {
            var card = e.target.closest('.msch-lesson-category-card');
            if (!card) {
                return;
            }
            var container = card.closest('.msch-lessons');
            var select = container ? container.querySelector('.msch-lesson-filter-select') : null;
            if (!select) {
                return;
            }
            select.value = card.getAttribute('data-msch-category');
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }, false);
        document.addEventListener('change', function (e) {
            if (!e.target || !e.target.classList || !e.target.classList.contains('msch-lesson-filter-select')) {
                return;
            }
            var container = e.target.closest('.msch-lessons');
            var grid = container ? container.querySelector('.msch-lesson-category-grid') : null;
            if (!grid) {
                return;
            }
            var value = e.target.value;
            grid.querySelectorAll('.msch-lesson-category-card').forEach(function (card) {
                var active = (card.getAttribute('data-msch-category') === value);
                card.classList.toggle('is-active', active);
                card.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }, false);
        </script>
        <?php
    }
    // Top nav (green control + Previous/Back/Next) renders first so it always sits above the category grid /
    // tile grid in both states (docs/0910-0313 menu-bar screenshot request); the grid and tile wrap below toggle.

    // Deep-link initial state: render the correct view server-side for a valid ?category= so no-JS/crawler
    // access works (docs/0910-0325-Copilot-REQUEST-Harden-Lessons-Module-Against-Fragility.txt, Stage 1).
    $requested_category = isset($_GET['category']) ? sanitize_title(wp_unslash($_GET['category'])) : '';
    $initial_category = '';
    if ($requested_category !== '' && !empty($filter_terms)) {
        if ($requested_category === 'all' || array_key_exists($requested_category, $filter_terms)) {
            $initial_category = $requested_category;
        }
    }
    $category_slugs = array_keys($filter_terms); // already alphabetical (asort), same order as the select
    $initial_category_index = ($initial_category !== '' && $initial_category !== 'all')
        ? array_search($initial_category, $category_slugs, true)
        : false;
    $initial_prev_disabled = !($initial_category_index !== false && $initial_category_index > 0);
    $initial_next_disabled = !($initial_category_index !== false && $initial_category_index < count($category_slugs) - 1);
    $initial_back_disabled = ($initial_category === '');
    $initial_category_label = '';
    if ($initial_category === 'all') {
        $initial_category_label = 'All Lessons';
    } elseif ($initial_category !== '' && isset($filter_terms[$initial_category])) {
        $initial_category_label = $filter_terms[$initial_category];
    }
    ?>
    <div class="msch-lessons">
    <?php if (!empty($direct_lessons)) : ?>
        <div class="thesis-lesson-card-grid msch-lesson-direct-display-grid">
            <?php foreach ($direct_lessons as $lesson) : ?>
                <?php echo rbm_msch_lesson_card_html($lesson); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!$rbm_hide_categories_section) : ?>
    <?php if (!empty($filter_terms)) : ?>
        <div class="msch-lesson-filter">
            <select id="<?php echo esc_attr($filter_id); ?>" class="msch-lesson-filter-select" aria-label="Filter Lessons by instrument">
                <option value=""<?php echo $initial_category === '' ? ' disabled selected' : ' disabled'; ?>>Choose Instrument</option>
                <option value="all"<?php echo $initial_category === 'all' ? ' selected' : ''; ?>>All Lessons</option>
                <?php foreach ($filter_terms as $slug => $name) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php echo $initial_category === $slug ? ' selected' : ''; ?>><?php echo esc_html($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="msch-lesson-tiles-header">
            <div class="msch-lesson-tiles-nav">
                <button type="button" class="msch-lesson-tiles-prev"<?php echo $initial_prev_disabled ? ' disabled' : ''; ?>>&larr; Previous</button>
                <button type="button" class="msch-lesson-tiles-back"<?php echo $initial_back_disabled ? ' disabled' : ''; ?>>Back to Categories</button>
                <button type="button" class="msch-lesson-tiles-next"<?php echo $initial_next_disabled ? ' disabled' : ''; ?>>Next &rarr;</button>
            </div>
            <span class="msch-lesson-tiles-category-name" aria-live="polite"><?php echo esc_html($initial_category_label); ?></span>
        </div>
    <?php endif; ?>
    <?php
    // Category icon grid (Stage 3): only for the unfiltered, main-page view; only categories with Active lessons.
    if (empty($atts['instrument']) && !empty($filter_terms)) : ?>
        <div class="msch-lesson-category-grid" role="group" aria-label="Choose a lesson category"<?php echo $initial_category !== '' ? ' hidden' : ''; ?>>
            <?php foreach ($filter_terms as $slug => $name) :
                $term = get_term_by('slug', $slug, 'msch_instrument');
                $icon_file = $term ? get_term_meta($term->term_id, '_msch_category_icon_filename', true) : '';
                $icon_url = $icon_file ? rbm_msch_category_icon_url($icon_file) : '';
                $icon_alt = $term ? rbm_msch_category_icon_alt($term->term_id, $name) : ($name . ' music lessons at Red Barn Music School');
            ?>
                <button type="button" class="msch-lesson-category-card" data-msch-category="<?php echo esc_attr($slug); ?>" aria-pressed="false">
                    <?php if ($icon_url !== '') : ?>
                        <span class="msch-lesson-category-icon"><?php echo rbm_msch_responsive_tile_image(
                            RBM_LESSONS_DIR . '/assets/category-icons/',
                            RBM_LESSONS_URL . 'assets/category-icons/',
                            $icon_file,
                            $icon_alt,
                            '220px'
                        ); ?></span>
                    <?php else : ?>
                        <span class="msch-lesson-category-icon msch-lesson-category-icon--placeholder" aria-hidden="true"><?php echo esc_html(mb_substr($name, 0, 1)); ?></span>
                    <?php endif; ?>
                    <span class="msch-lesson-category-name"><?php echo esc_html($name); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="msch-lesson-tiles-wrap"<?php echo (!empty($filter_terms) && $initial_category === '') ? ' hidden' : ''; ?>>
        <div class="thesis-lesson-card-grid">
            <?php foreach ($lessons as $lesson) :
                $l_terms = get_the_terms($lesson->ID, 'msch_instrument');
                $l_slugs = (is_array($l_terms) && !is_wp_error($l_terms)) ? wp_list_pluck($l_terms, 'slug') : [];
                $initial_hide = ($initial_category !== '' && $initial_category !== 'all' && !in_array($initial_category, $l_slugs, true));
            ?>
                <?php echo rbm_msch_lesson_card_html($lesson, $initial_hide); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    </div><!-- .msch-lessons -->
    <?php
    return ob_get_clean();
}

// --- Category page Previous/Next navigation (Lessons page tile order; not a taxonomy archive) ---

add_filter('the_content', 'rbm_msch_lesson_category_nav');
function rbm_msch_lesson_category_nav($content) {
    if (!is_page() || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    // Same order as the tiles on the Lessons page.
    $category_page_ids = [6141, 6150, 19375, 6157, 537, 6128, 20398];
    $index = array_search(get_the_ID(), $category_page_ids, true);
    if ($index === false) {
        return $content;
    }
    $prev_id = ($index > 0) ? $category_page_ids[$index - 1] : 0;
    $next_id = ($index < count($category_page_ids) - 1) ? $category_page_ids[$index + 1] : 0;
    if (!$prev_id && !$next_id) {
        return $content;
    }

    ob_start();
    ?>
    <style>
    .msch-lesson-category-nav { margin-top: 24px; display: flex; justify-content: space-between; text-align: left; }
    </style>
    <div class="msch-lesson-category-nav single-navigation clearfix">
        <?php if ($prev_id) : ?>
            <a href="<?php echo esc_url(get_permalink($prev_id)); ?>" rel="prev">&lt; <?php echo esc_html(get_the_title($prev_id)); ?></a>
        <?php endif; ?>
        <?php if ($next_id) : ?>
            <a href="<?php echo esc_url(get_permalink($next_id)); ?>" rel="next"><?php echo esc_html(get_the_title($next_id)); ?> &gt;</a>
        <?php endif; ?>
    </div>
    <?php
    return $content . ob_get_clean();
}
