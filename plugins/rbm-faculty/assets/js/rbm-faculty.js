/**
 * Front-end behavior for the [msch_teachers] Faculty filter select.
 * Extracted from an inline <script> block in includes/rbm-teachers.php (docs/0917-1053-PLAN-
 * Extract-Inline-JS-CSS-From-RBM-Plugins.txt). Logic unchanged from the inline original.
 */
(function () {
    'use strict';
    document.addEventListener('change', function (e) {
        if (!e.target || !e.target.classList || !e.target.classList.contains('msch-teacher-filter-select')) {
            return;
        }
        // docs/0919-1135-...: no longer assumes the grid is the filter's immediate next sibling -
        // the Sign Up CTA <p> (docs/0919-0141-...) now sits between them in the DOM.
        var wrap = e.target.closest('.msch-teacher-filter');
        var container = wrap ? wrap.closest('#teachers') : null;
        var grid = container ? container.querySelector('.thesis-lesson-card-grid') : null;
        if (!grid) {
            return;
        }
        var value = e.target.value;
        var cards = grid.querySelectorAll('.thesis-teacher-card');
        cards.forEach(function (card) {
            if (!value || value === 'all') {
                card.style.display = '';
                return;
            }
            var terms = (card.getAttribute('data-msch-instruments') || '').split(',');
            card.style.display = (terms.indexOf(value) !== -1) ? '' : 'none';
        });
    }, false);
})();
