/**
 * Admin behavior for rbm-faculty (Teacher edit screen, Faculty Placeholder settings page).
 * Extracted from inline <script> blocks in includes/rbm-teachers.php and includes/rbm-teacher-
 * form.php (docs/0917-1053-PLAN-Extract-Inline-JS-CSS-From-RBM-Plugins.txt). Kept independent from
 * rbm-instruments' own copy of this same pattern so neither plugin depends on the other.
 */
(function ($) {
    'use strict';

    // Copy Shortcode (Teacher edit screen) and Copy List (Faculty settings page) clipboard controls.
    document.addEventListener('click', function (e) {
        var button = e.target.closest('.rbm-copy-shortcode, .rbm-copy-list-trigger');
        if (!button) {
            return;
        }
        var field = document.getElementById(button.getAttribute('data-copy-target'));
        if (!field) {
            return;
        }
        var status = button.classList.contains('rbm-copy-list-trigger')
            ? document.getElementById(button.getAttribute('data-status-target'))
            : button.nextElementSibling;
        var done = function () {
            if (status) {
                status.textContent = 'Copied';
                setTimeout(function () { status.textContent = ''; }, 2000);
            }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(field.value).then(done);
        } else {
            field.select();
            document.execCommand('copy');
            done();
        }
    }, false);

    // Media Library picker for the Card Photo / Portrait / Second Profile Photo / Placeholder fields.
    jQuery(function ($) {
        var frame;
        $(document).on('click', '.rbm-media-picker', function (e) {
            e.preventDefault();
            var button = $(this);
            var targetId = '#' + button.data('target');
            var previewId = '#' + button.data('preview');
            frame = wp.media({ title: 'Select Image', multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $(targetId).val(attachment.id);
                var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
                $(previewId).html('<img src="' + url + '" style="max-width:150px;height:auto;display:block;">');
                button.text('Replace Image');
                button.siblings('.rbm-media-remove').show();
            });
            frame.open();
        });
        $(document).on('click', '.rbm-media-remove', function (e) {
            e.preventDefault();
            var button = $(this);
            var targetId = '#' + button.data('target');
            var previewId = '#' + button.data('preview');
            $(targetId).val('');
            $(previewId).html('');
            button.hide();
            button.siblings('.rbm-media-picker').text('Select Image');
        });
    });
})(jQuery);
