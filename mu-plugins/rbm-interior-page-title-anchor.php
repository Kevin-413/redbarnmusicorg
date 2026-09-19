<?php
/**
 * Site-wide interior-page landing anchor (docs/0918-1532-PLAN-Filtered-Faculty-And-Interior-Page-
 * Title-Landing.txt): provides a stable #page-title anchor with scroll-margin-top just below
 * Avada's sticky header, present on every interior page, so a link ending in #page-title lands
 * with the page title/content visible instead of behind the header. Reuses Avada's own
 * avada_after_page_title_bar hook, fired from header.php on every front-end page load right after
 * the Title Bar section (whether the bar itself is shown or hidden) — no theme core file is
 * edited. Homepage is intentionally excluded; it must keep loading at the true top.
 *
 * Scope note (per the PLAN's SCOPE CONTROL): this adds the anchor + CSS, plus wires it into the
 * site's actual navigation (docs/0919-Copilot-REQUEST-Wire-Page-Title-Anchor-To-Navigation.txt):
 * the registered WP nav menu (main/mobile/sticky header), and the two footer Text widgets
 * ("Quick Links" and "Connect") that contain hand-typed internal links. The mobile bottom quick-
 * action bar (themes/Avada-Child-Theme/functions.php, redbarn_mobile_bottom_action_bar()) is edited
 * directly there instead, since it's a real PHP function, not filtered content.
 */

add_action('avada_after_page_title_bar', 'rbm_output_interior_page_title_anchor');
function rbm_output_interior_page_title_anchor() {
    if (is_admin() || is_front_page()) {
        return;
    }
    echo '<span id="page-title"></span>';
}

add_action('wp_head', 'rbm_interior_page_title_anchor_style');
function rbm_interior_page_title_anchor_style() {
    if (is_admin() || is_front_page()) {
        return;
    }
    // 92px (was 80px): +12px so the title text no longer clips under the sticky header.
    echo '<style>#page-title{scroll-margin-top:92px;}</style>';
}

// docs/0919-Copilot-REQUEST-Fix-Page-Title-Anchor-Bounce.txt: Avada's own fusion-scroll-to-anchor.js
// intercepts the click on any link (registered as a bubble-phase delegated handler), rewrites the
// hash to a non-existent "#_<id>" so the browser's native jump never fires, then on the destination
// page animates an eased scroll to the real target and swaps the hash back afterward — visible as a
// slide-then-bounce, since the animation overshoots before the corrective step. A direct address-
// bar load of the same #page-title URL was confirmed clean (no rewrite, no animation, instant native
// jump), so the fix only needs to stop Avada's click-time interception for these specific links,
// not touch scrolling anywhere else. A capture-phase listener runs before any bubble-phase delegated
// handler regardless of script load order, so this reliably wins the race without needing to find
// or modify Avada's own minified handler.
add_action('wp_footer', 'rbm_page_title_anchor_disable_smooth_scroll');
function rbm_page_title_anchor_disable_smooth_scroll() {
    if (is_admin()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var link = e.target && e.target.closest ? e.target.closest('a[href*="#page-title"]') : null;
        if (!link) {
            return;
        }
        // Stops Avada's own click handler (and anything else) from seeing this click at all;
        // native browser navigation + native anchor jump still proceed normally afterward.
        e.stopPropagation();
    }, true);
    </script>
    <?php
}

// Shared safety check: only append #page-title to a same-site, non-Home, plain page link. Skips
// external/mailto/tel links and anything that already carries its own fragment (e.g. a future
// #teachers-style deep link), so this never overrides a more specific existing contract.
function rbm_page_title_anchor_should_append($href) {
    $href = trim((string) $href);
    if ($href === '' || strpos($href, '#') !== false) {
        return false;
    }
    if (stripos($href, 'mailto:') === 0 || stripos($href, 'tel:') === 0 || stripos($href, 'javascript:') === 0) {
        return false;
    }
    $home = untrailingslashit(home_url('/'));
    if (untrailingslashit($href) === $home) {
        return false; // Home link: must keep loading at the true top.
    }
    if (preg_match('#^https?://#i', $href)) {
        $href_host = wp_parse_url($href, PHP_URL_HOST);
        $home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        if ($href_host !== $home_host) {
            return false; // external link
        }
    }
    return true;
}

// Main/mobile/sticky header menu (all three theme locations share the same registered menu).
// Avada's actual header render calls wp_nav_menu() with a direct 'menu' ID (Header Builder), not
// 'theme_location' — so both are checked, resolving the location -> menu ID mapping dynamically
// rather than hard-coding a term ID.
function rbm_page_title_anchor_menu_ids() {
    static $ids = null;
    if ($ids === null) {
        $locations = get_nav_menu_locations();
        $ids = [];
        foreach (['main_navigation', 'mobile_navigation', 'sticky_navigation'] as $location) {
            if (!empty($locations[$location])) {
                $ids[] = (int) $locations[$location];
            }
        }
        $ids = array_unique($ids);
    }
    return $ids;
}

add_filter('nav_menu_link_attributes', 'rbm_page_title_anchor_nav_menu_links', 10, 3);
function rbm_page_title_anchor_nav_menu_links($atts, $item, $args) {
    if (is_admin()) {
        return $atts;
    }
    $locations = ['main_navigation', 'mobile_navigation', 'sticky_navigation'];
    $matches_location = !empty($args->theme_location) && in_array($args->theme_location, $locations, true);
    $matches_menu_id = !empty($args->menu) && in_array((int) $args->menu, rbm_page_title_anchor_menu_ids(), true);
    if (!$matches_location && !$matches_menu_id) {
        return $atts;
    }
    if (empty($atts['href']) || !rbm_page_title_anchor_should_append($atts['href'])) {
        return $atts;
    }
    $atts['href'] .= '#page-title';
    return $atts;
}

// Footer "Quick Links" (text-5) and "Connect" (text-4) Text widgets: hand-typed HTML, not a
// registered menu, so rewritten here instead of edited by hand — keeps the widget's own saved
// content untouched and reusable if the widget is ever re-saved.
// Note: core's widget_text_content filter never fires for these — Avada's footer widget render
// path doesn't run through it — so widget_display_callback (which does fire, confirmed by direct
// testing) is used instead, rewriting $instance['text'] before the widget outputs it.
add_filter('widget_display_callback', 'rbm_page_title_anchor_footer_widgets', 10, 2);
function rbm_page_title_anchor_footer_widgets($instance, $widget) {
    if (is_admin() || empty($widget->id) || !in_array($widget->id, ['text-4', 'text-5'], true)) {
        return $instance;
    }
    if (empty($instance['text'])) {
        return $instance;
    }
    $instance['text'] = preg_replace_callback('#href=("|\')([^"\']*)\1#i', function ($matches) {
        $quote = $matches[1];
        $href = $matches[2];
        if (!rbm_page_title_anchor_should_append($href)) {
            return $matches[0];
        }
        return 'href=' . $quote . $href . '#page-title' . $quote;
    }, $instance['text']);
    return $instance;
}
