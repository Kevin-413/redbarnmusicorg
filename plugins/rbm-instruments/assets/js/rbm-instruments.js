/**
 * Front-end behavior for the [msch_lessons] Instruments page and its Category tiles/cards.
 * Extracted from inline <script> blocks in includes/rbm-lessons.php (docs/0917-1053-PLAN-Extract-
 * Inline-JS-CSS-From-RBM-Plugins.txt). Logic unchanged from the inline originals.
 */
(function () {
    'use strict';

    document.addEventListener('change', function (e) {
        if (!e.target || !e.target.classList || !e.target.classList.contains('msch-lesson-filter-select')) {
            return;
        }
        var container = e.target.closest('.msch-lessons');
        if (!container) {
            return;
        }
        var value = e.target.value;
        // Direct Display / Instruments We Teach is a landing-page overview only; once a Category
        // (or All) is chosen, the tiles wrap below already shows the same Instruments, so it must
        // hide entirely instead of duplicating them.
        var directGrid = container.querySelector('.msch-lesson-direct-display-grid');
        if (directGrid) {
            directGrid.hidden = !!value;
        }
        var grid = container.querySelector('.msch-lesson-tiles-wrap .thesis-lesson-card-grid');
        if (!grid) {
            return;
        }
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

    // Top nav (green control + Previous/Back/Next) is persistent above the category grid / tile
    // grid in both states: reveal the lesson tiles and hide the category grid only once a real
    // category (or All Lessons) is chosen, label the category view, keep the shareable ?category=
    // URL param in sync, and refresh Previous/Back/Next enabled state (never hidden). All lookups
    // are scoped to the shortcode's own .msch-lessons container so DOM order can change freely.
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
        // Previous/Next: walk the same alphabetical category list as the select; "All Lessons"
        // doesn't participate and keeps both buttons disabled (still visible) rather than hidden.
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

    // Back to Categories: hide the tile view, reset the select, restore the category grid, clear
    // the active card, and disable (but keep visible) all three top nav buttons.
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
        // Restore the Direct Display / Instruments We Teach overview, hidden above while a
        // Category (or All) was selected.
        var directGrid = container.querySelector('.msch-lesson-direct-display-grid');
        if (directGrid) {
            directGrid.hidden = false;
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

    // Previous/Next: step through the same alphabetical category list as the Choose Instrument
    // select. Scoped only to this shortcode's Instrument View; does not touch the legacy per-page
    // category nav.
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
    // already renders the correct initial state, so this only needs to (a) sync any additional
    // .msch-lessons instances whose select doesn't already match, and (b) strip an invalid
    // ?category= value from the URL so it doesn't linger next to the visible Category View.
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

    // Category card <-> Choose Instrument select stay synchronized; or, when the Category Click
    // Destination setting is Faculty Group Page, navigate there instead.
    document.addEventListener('click', function (e) {
        var card = e.target.closest('.msch-lesson-category-card');
        if (!card) {
            return;
        }
        var facultyUrl = card.getAttribute('data-msch-category-faculty-url');
        if (facultyUrl) {
            window.location.href = facultyUrl;
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
})();
