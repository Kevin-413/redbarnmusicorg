/**
 * Admin behavior for rbm-instruments (Instruments list, Categories taxonomy screens, Instrument/
 * Category edit screens, Instruments -> Settings page). Extracted from inline <script> blocks
 * across includes/rbm-lesson-form.php and includes/rbm-lessons.php (docs/0917-1053-PLAN-Extract-
 * Inline-JS-CSS-From-RBM-Plugins.txt). Each initializer feature-detects its own markup, so loading
 * this one file on any relevant rbm-instruments admin screen is safe even if a given screen only
 * has some of the elements below.
 */
(function ($) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Slug/URL Mode column info icons (docs/0919-1441-Copilot-REQUEST-Add-Slug-And-URL-Mode-
        // Columns.txt): CSS already shows the tip on hover/focus; click/tap toggles it too, and it
        // dismisses on Escape or when focus moves elsewhere.
        document.querySelectorAll('.rbm-info-icon').forEach(function (icon) {
            icon.addEventListener('click', function (e) {
                e.preventDefault();
                var tip = icon.nextElementSibling;
                if (tip) {
                    tip.classList.toggle('is-visible');
                }
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.rbm-info-tip.is-visible').forEach(function (tip) {
                    tip.classList.remove('is-visible');
                });
            }
        });
        document.addEventListener('focusout', function (e) {
            var wrap = e.target.closest ? e.target.closest('.rbm-info-icon-wrap') : null;
            if (!wrap) {
                return;
            }
            setTimeout(function () {
                if (!wrap.contains(document.activeElement)) {
                    var tip = wrap.querySelector('.rbm-info-tip');
                    if (tip) {
                        tip.classList.remove('is-visible');
                    }
                }
            }, 0);
        });


        // Instrument slug-duplicate warning (docs/0919-1305-Copilot-REQUEST-Add-Instrument-Slug-
        // Duplicate-Checker.txt): the dialog only exists in the markup when the previous save was
        // blocked for reusing another Instrument's slug. The plain .notice above it in the markup
        // is always visible as the no-JS fallback; this just layers a focused modal on top of it.
        var slugConflictDialog = document.getElementById('rbm-slug-conflict-dialog');
        if (slugConflictDialog && typeof slugConflictDialog.showModal === 'function') {
            slugConflictDialog.showModal();
            var slugConflictReturn = document.getElementById('rbm-slug-conflict-return');
            if (slugConflictReturn) {
                slugConflictReturn.addEventListener('click', function () {
                    slugConflictDialog.close();
                    var slugField = document.getElementById('rbm_lesson_slug');
                    if (slugField) {
                        slugField.focus();
                    }
                });
            }
        }

        // Instruments list only: move the native "Add Instrument" action into the top-right
        // controls row. Scoped by WP's own body class so this never runs on the Categories list
        // too (which also has a top-right controls row + its own jump-link handling below).
        if (document.body.classList.contains('post-type-msch_lesson')) {
            var lessonAddBtn = document.querySelector('.wrap .page-title-action');
            var lessonSlot = document.getElementById('rbm-add-instrument-slot');
            if (lessonAddBtn && lessonSlot) {
                lessonAddBtn.className = 'button';
                lessonSlot.appendChild(lessonAddBtn);
            }
        }

        // Categories list only: add a jump link to the (relocated) native Add form, and move it
        // into the top-right controls row too.
        if (document.body.classList.contains('taxonomy-msch_instrument') && document.getElementById('col-container')) {
            var catHeading = document.querySelector('.wp-heading-inline');
            if (catHeading && !document.getElementById('rbm-jump-to-add-instrument')) {
                var link = document.createElement('a');
                link.id = 'rbm-jump-to-add-instrument';
                link.href = '#col-left';
                link.className = 'page-title-action';
                link.textContent = 'Add Instrument';
                catHeading.insertAdjacentElement('afterend', link);
            }
            var jumpLink = document.getElementById('rbm-jump-to-add-instrument');
            var catSlot = document.getElementById('rbm-add-instrument-slot');
            if (jumpLink && catSlot && jumpLink.parentElement !== catSlot) {
                jumpLink.className = 'button';
                catSlot.appendChild(jumpLink);
            }
        }
        if (document.body.classList.contains('rbm-add-instrument-disabled')) {
            var addHeading = document.querySelector('#col-left h2');
            if (addHeading && addHeading.textContent.indexOf('INACTIVE FEATURE') === -1) {
                addHeading.textContent = addHeading.textContent + ' - INACTIVE FEATURE';
            }
            var addSubmit = document.querySelector('#col-left form#addtag #submit');
            if (addSubmit) {
                addSubmit.disabled = true;
            }
        }
        if (catSlot) {
            // Moving #col-left (the Add Tag form) to the bottom via CSS order puts WP core's own
            // auto-focus of its #tag-name field below the fold, causing the page to jump on load.
            window.scrollTo(0, 0);
        }

        // Edit Category page: relabel the heading/title from "Edit Instrument" to "Edit Category"
        // (only rendered when rbm_lessons_relabel_edit_term_heading() enqueues this file).
        if (document.body.classList.contains('rbm-relabel-edit-category')) {
            var heading = document.querySelector('.wrap h1');
            if (heading && heading.textContent.trim() === 'Edit Instrument') {
                heading.textContent = 'Edit Category';
            }
            if (document.title.indexOf('Edit Instrument') === 0) {
                document.title = document.title.replace('Edit Instrument', 'Edit Category');
            }

            // Extra Save button at the top of the page, wired to the real "Update" submit button
            // (WP core's Edit Term form has no id on this input, only #edittag > [type=submit]).
            var realSubmit = document.querySelector('#edittag input[type="submit"], #edittag button[type="submit"]');
            if (heading && realSubmit) {
                var topSave = document.createElement('button');
                topSave.type = 'button';
                topSave.className = 'button button-primary';
                topSave.textContent = 'Save';
                topSave.style.marginLeft = '12px';
                topSave.addEventListener('click', function () {
                    realSubmit.click();
                });
                heading.insertAdjacentElement('afterend', topSave);
            }
        }

        // Category Tile Preview (Add/Edit Category screens): live-sync the Name field into the
        // preview. WP's native field id is "tag-name" on Add, "name" on Edit.
        var categoryNameField = document.getElementById('tag-name') || document.getElementById('name');
        var tilePreviewName = document.getElementById('rbm_category_tile_preview_name');
        if (categoryNameField && tilePreviewName) {
            categoryNameField.addEventListener('input', function (e) {
                tilePreviewName.textContent = e.target.value;
            });
        }

        // Clickable Icon column thumbnail preview (Instruments and Categories admin lists): opens
        // the same image already shown as the thumbnail, capped at 500px, closes via its own
        // button, clicking outside it, or Escape. Inside DOMContentLoaded so the overlay markup
        // (printed via admin_footer-*, which can run either before or after this enqueued script
        // tag) is always present by the time this runs.
        var iconOverlay = document.getElementById('rbm-icon-preview-overlay');
        if (iconOverlay) {
            var iconImg = document.getElementById('rbm-icon-preview-img');
            var openPreview = function (src) {
                iconImg.src = src;
                iconOverlay.style.display = 'flex';
            };
            var closePreview = function () {
                iconOverlay.style.display = 'none';
                iconImg.src = '';
            };
            document.addEventListener('click', function (e) {
                var trigger = e.target.closest('.rbm-icon-preview-trigger');
                if (trigger) {
                    openPreview(trigger.getAttribute('data-full-src'));
                    return;
                }
                if (e.target === iconOverlay || e.target.id === 'rbm-icon-preview-close') {
                    closePreview();
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && iconOverlay.style.display !== 'none') {
                    closePreview();
                }
            });
        }
    });

    // Generic drag-and-drop Display Order reorder (Instruments list, Categories list). Each screen
    // calls window.rbmInitDragSort() with its own small config via wp_add_inline_script().
    window.rbmInitDragSort = function (containerSelector, idPrefix, ajaxData) {
        if (!window.jQuery || !jQuery.fn.sortable) {
            return;
        }
        var $list = jQuery(containerSelector);
        if (!$list.length) {
            return;
        }
        $list.sortable({
            items: '> tr',
            handle: '.rbm-drag-handle',
            axis: 'y',
            update: function () {
                var ids = $list.children('tr').map(function () {
                    return parseInt(jQuery(this).attr('id').replace(idPrefix, ''), 10);
                }).get();
                var data = jQuery.extend({}, ajaxData, { ids: ids });
                jQuery.post(ajaxurl, data)
                    .done(function (r) {
                        if (!r || !r.success) {
                            location.reload();
                            return;
                        }
                        $list.children('tr').each(function (i) {
                            jQuery(this).find('.column-rbm_display_order').text(i + 1);
                        });
                        jQuery('<div class="notice notice-success is-dismissible"><p>Display order saved.</p></div>').insertAfter('.wp-heading-inline').delay(2000).fadeOut(300, function () { jQuery(this).remove(); });
                    })
                    .fail(function () { location.reload(); });
            }
        });
    };

    // Copy Shortcode clipboard control (Teacher/Instrument/Category edit screens).
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

    // Media Library picker for the Instrument Tile Image and Category Image fields.
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
                if (button.data('target') === 'rbm_category_image_attachment_id') {
                    var tilePreviewIcon = document.getElementById('rbm_category_tile_preview_icon');
                    if (tilePreviewIcon) {
                        var mediumUrl = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                        tilePreviewIcon.innerHTML = '<img src="' + mediumUrl + '" alt="" style="max-width:120px;height:auto;display:block;">';
                    }
                }
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
            if (targetId === '#rbm_category_image_attachment_id') {
                var tilePreviewIcon = document.getElementById('rbm_category_tile_preview_icon');
                if (tilePreviewIcon) {
                    tilePreviewIcon.innerHTML = '';
                }
            }
        });
    });
})(jQuery);
