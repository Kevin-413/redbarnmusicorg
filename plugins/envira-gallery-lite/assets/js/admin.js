import Swal from 'sweetalert2';

/**
 * Handles:
 * - Copy to Clipboard functionality
 * - Dismissable Notices
 *
 * @since 1.5.0
 */

(function ($, window, document, envira_gallery_admin) {
	let envira_notifications, envira_connect;
	window.envira_notifications = envira_notifications = {
		init() {
			var app = this;
			app.$drawer = $('#envira-notifications-drawer');
			app.find_elements();
			app.init_open();
			app.init_close();
			app.init_dismiss();
			app.init_view_switch();
			app.update_count(app.active_count);
		},

		should_init() {
			var app = this;
			return app.$drawer.length > 0;
		},
		find_elements() {
			var app = this;
			app.$open_button = $('#envira-notifications-button');
			app.$count = app.$drawer.find('#envira-notifications-count');
			app.$dismissed_count = app.$drawer.find('#envira-notifications-dismissed-count');
			app.active_count = app.$open_button.data('count') ? app.$open_button.data('count') : 0;
			app.dismissed_count = app.$open_button.data('dismissed');
			app.$body = $('body');
			app.$dismissed_button = $('#envira-notifications-show-dismissed');
			app.$active_button = $('#envira-notifications-show-active');
			app.$active_list = $('.envira-notifications-list .envira-notifications-active');
			app.$dismissed_list = $('.envira-notifications-list .envira-notifications-dismissed');
			app.$dismiss_all = $('#envira-dismiss-all');
		},
		update_count(count) {
			var app = this;
			app.$open_button.data('count', count).attr('data-count', count);
			if (0 === count) {
				app.$open_button.removeAttr('data-count');
			}
			app.$count.text(count);
			app.dismissed_count += Math.abs(count - app.active_count);
			app.active_count = count;

			app.$dismissed_count.text(app.dismissed_count);

			if (0 === app.active_count) {
				app.$dismiss_all.hide();
			}
		},
		init_open() {
			var app = this;
			app.$open_button.on('click', function (e) {
				e.preventDefault();
				app.$body.addClass('envira-notifications-open');
			});
		},
		init_close() {
			var app = this;
			app.$body.on(
				'click',
				'.envira-notifications-close, .envira-notifications-overlay',
				function (e) {
					e.preventDefault();
					app.$body.removeClass('envira-notifications-open');
				}
			);
		},
		init_dismiss() {
			var app = this;
			app.$drawer.on('click', '.envira-notification-dismiss', function (e) {
				e.preventDefault();
				const id = $(this).data('id');
				app.dismiss_notification(id);
				if ('all' === id) {
					app.move_to_dismissed(app.$active_list.find('li'));
					app.update_count(0);
					return;
				}
				app.move_to_dismissed($(this).closest('li'));
				app.update_count(app.active_count - 1);
			});
		},
		move_to_dismissed(element) {
			var app = this;
			element.slideUp(function () {
				$(this).prependTo(app.$dismissed_list).show();
			});
		},
		dismiss_notification(id) {
			var app = this;
			return $.post(ajaxurl, {
				action: 'envira_notification_dismiss',
				nonce: envira_gallery_admin.dismiss_notification_nonce,
				id: id
			});
		},
		init_view_switch() {
			var app = this;
			app.$dismissed_button.on('click', function (e) {
				e.preventDefault();
				app.$drawer.addClass('show-dismissed');
			});
			app.$active_button.on('click', function (e) {
				e.preventDefault();
				app.$drawer.removeClass('show-dismissed');
			});
		}
	};

	window.envira_connect = envira_connect = {
		init() {
			$(this.ready());
		},
		ready() {
			this.connectClicked();
		},
		connectClicked() {
			let app = this;
			$('#envira-gallery-settings-connect-btn').on('click', function (e) {
				e.preventDefault();
				app.gotoUpgradeUrl();
			});
		},
		gotoUpgradeUrl() {
			let app = this;
			let data = {
				action: 'envira_connect',
				key: $('#envira-settings-key').val(),
				_wpnonce: envira_gallery_admin.connect_nonce
			};

			$.post(ajaxurl, data)
				.done(function (res) {
					if (res.success) {
						if (res.data.reload) {
							app.proAlreadyInstalled(res);
							return;
						}
						window.location.href = res.data.url;
						return;
					}

					Swal.fire({
						title: envira_gallery_admin.oops,
						html: res.data.message,
						icon: 'warning',
						confirmButtonColor: '#3085d6',
						confirmButtonText: envira_gallery_admin.ok,
						customClass: {
							confirmButton: 'envira-button'
						}
					});
				})
				.fail(function (xhr) {
					app.failAlert(xhr);
				});
		},
		proAlreadyInstalled(res) {
			Swal.fire({
				title: envira_gallery_admin.almost_done,
				text: res.data.message,
				icon: 'success',
				confirmButtonColor: '#3085d6',
				confirmButtonText: envira_gallery_admin.plugin_activate_btn,
				customClass: {
					confirmButton: 'envira-button'
				}
			}).then(result => {
				if (result.isConfirmed) {
					window.location.reload();
				}
			});
		},
		failAlert() {
			Swal.fire({
				title: envira_gallery_admin.oops,
				html:
					envira_gallery_admin.server_error +
					'<br>' +
					xhr.status +
					' ' +
					xhr.statusText +
					' ' +
					xhr.responseText,
				icon: 'warning',
				confirmButtonColor: '#3085d6',
				confirmButtonText: envira_gallery_admin.ok,
				customClass: {
					confirmButton: 'envira-button'
				}
			});
		}
	};

	// DOM ready
	$(function () {
		envira_notifications.init();
		envira_connect.init();
		$('#screen-meta-links').prependTo('#envira-header-temp');
		$('#screen-meta').prependTo('#envira-header-temp');

		/**
		 * Hide empty tablenav divs
		 * Check if tablenav has no actual visible content (ignoring whitespace)
		 */
		$('div.tablenav').each(function () {
			const $tablenav = $(this);
			// Check if there's any visible text
			const textContent = $tablenav.text().trim();

			// If no text content and no visible children, hide it
			if (!textContent) {
				$tablenav.hide();
			}
		});

		/**
		 * Copy to Clipboard
		 */
		if (typeof ClipboardJS !== 'undefined') {
			$(document).on('click', '.envira-clipboard', function (e) {
				var envira_clipboard = new ClipboardJS('.envira-clipboard');
				e.preventDefault();
			});
		}

		/**
		 * Dismissable Notices
		 * - Sends an AJAX request to mark the notice as dismissed
		 */
		$('div.envira-notice').on('click', '.notice-dismiss', function (e) {
			e.preventDefault();

			$(this).closest('div.envira-notice').fadeOut();

			// If this is a dismissible notice, it means we need to send an AJAX request
			if ($(this).hasClass('is-dismissible')) {
				$.post(
					envira_gallery_admin.ajax,
					{
						action: 'envira_gallery_ajax_dismiss_notice',
						nonce: envira_gallery_admin.dismiss_notice_nonce,
						notice: $(this).parent().data('notice')
					},
					function (response) {},
					'json'
				);
			}
		});

		$('#envira-top-notification').on('click', '.envira-dismiss', function (e) {
			e.preventDefault();

			$(this).closest('div.envira-header-notification').fadeOut();
			$.post(
				envira_gallery_admin.ajax,
				{
					action: 'envira_gallery_ajax_dismiss_topbar',
					nonce: envira_gallery_admin.dismiss_topbar_nonce
				},
				function (response) {},
				'json'
			);
		});

		let svg =
			'<img class="envira-unlock-icon" src="' +
			envira_gallery_admin.unlock_icon +
			'" alt="Unlock" />';
		let colspan = $('.post-type-envira table > thead > tr:first > th').length + 1; // add for checkbox td?
		var $unlock =
			'<tr class="envira_tr"><td scope="col" colspan="' +
			colspan +
			'" width="100%"><div>' +
			svg +
			'<hgroup><h3>' +
			envira_gallery_admin.unlock_title +
			'</h3><h5>' +
			envira_gallery_admin.unlock_text +
			'</h5></hgroup><a href="' +
			envira_gallery_admin.unlock_url +
			'" class="button envira-button-blue envira-button-green" target="_blank">' +
			envira_gallery_admin.unlock_btn +
			'</a></div></td></tr>';
		$('.post-type-envira .wp-list-table tbody').append($unlock);

		/**
		 * Empty State Display
		 * Move empty state from admin_footer to wpbody-content
		 */
		const $emptyStateContainer = $('#envira-empty-state-container');
		if ($emptyStateContainer.length) {
			document.body.classList.add('envira-empty-state-active');

			const $emptyState = $emptyStateContainer.find('.envira-empty-state');
			if ($emptyState.length && $('#wpbody-content').length) {
				$('#wpbody-content').prepend($emptyState);
				$emptyStateContainer.remove();
			}
		}
	});
})(jQuery, window, document, envira_gallery_admin);
