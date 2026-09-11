/**
 * AI Image Assistant — Lite upsell.
 *
 * Injects the same per-image purple sparkle trigger Pro uses onto every gallery
 * image, and turns those triggers + the "Bulk Image Assistant" button into an
 * upgrade modal. No AI calls — this is purely a Pro upsell.
 */
(function () {
	'use strict';

	const cfg = window.enviraAIUpsell || {};
	const i18n = cfg.i18n || {};
	const GALLERY = '#envira-gallery-output';

	/* ─── Tiny DOM helper ─────────────────────────────────────────────── */
	function el(tag, attrs, children) {
		const node = document.createElement(tag);
		attrs = attrs || {};
		Object.keys(attrs).forEach(function (k) {
			if (k === 'className') {
				node.className = attrs[k];
			} else if (k === 'html') {
				node.innerHTML = attrs[k];
			} else if (k === 'text') {
				node.textContent = attrs[k];
			} else {
				node.setAttribute(k, attrs[k]);
			}
		});
		(children || []).forEach(function (c) {
			if (c) {
				node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
			}
		});
		return node;
	}

	/* ─── Per-image purple sparkle triggers ───────────────────────────── */
	function injectTriggers(root) {
		root = root || document;
		const lis = root.querySelectorAll(GALLERY + ' li[data-envira-gallery-image]');
		Array.prototype.forEach.call(lis, function (li) {
			if (li.querySelector('.envira-ia-image-trigger')) {
				return;
			}
			const btn = el(
				'button',
				{
					type: 'button',
					className: 'envira-ia-image-trigger envira-ia-upsell-trigger',
					title: i18n.trigger || 'Image Assistant'
				},
				[el('span', {className: 'envira-ia-sparkle'})]
			);
			li.insertBefore(btn, li.firstChild);
		});
	}

	/* ─── Upsell modal ────────────────────────────────────────────────── */
	let overlay = null;

	function closeModal() {
		if (overlay && overlay.parentNode) {
			overlay.parentNode.removeChild(overlay);
		}
		overlay = null;
		document.removeEventListener('keydown', onKeydown);
	}

	function onKeydown(evt) {
		if (evt.key === 'Escape') {
			closeModal();
		}
	}

	function openModal() {
		if (overlay) {
			return; // already open
		}

		const lockIcon = el('span', {
			className: 'envira-ia-upsell-lock',
			html: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.5A4.5 4.5 0 0 0 7.5 6v3H6.75A1.75 1.75 0 0 0 5 10.75v9.5C5 21.216 5.784 22 6.75 22h10.5A1.75 1.75 0 0 0 19 20.25v-9.5A1.75 1.75 0 0 0 17.25 9H16.5V6A4.5 4.5 0 0 0 12 1.5zM9.5 6a2.5 2.5 0 0 1 5 0v3h-5V6z"/></svg>'
		});

		const arrow = el('span', {className: 'envira-ia-upsell-arrow', 'aria-hidden': 'true'});

		const upgradeBtn = el('a', {
			className: 'envira-ia-upsell-cta-btn',
			href: cfg.upgradeUrl || 'https://enviragallery.com/lite/',
			target: '_blank',
			rel: 'noopener noreferrer',
			html:
				(i18n.upgrade || 'Upgrade to Pro') +
				' <span class="envira-ia-upsell-cta-arrow" aria-hidden="true">&rarr;</span>'
		});

		const discount = el('div', {className: 'envira-ia-upsell-discount'}, [
			el('span', {className: 'envira-ia-upsell-badge', html: '%'}),
			el('p', {className: 'envira-ia-upsell-discount-text', html: i18n.discount || ''})
		]);

		const card = el(
			'div',
			{className: 'envira-ia-upsell-card', role: 'dialog', 'aria-modal': 'true'},
			[
				el('button', {
					type: 'button',
					className: 'envira-ia-upsell-close',
					'aria-label': i18n.close || 'Close',
					html: '&times;'
				}),
				el('div', {className: 'envira-ia-upsell-body'}, [
					lockIcon,
					el('h2', {
						className: 'envira-ia-upsell-title',
						text: i18n.title || 'AI Image Assistant is a PRO Feature'
					}),
					el('p', {className: 'envira-ia-upsell-desc', text: i18n.desc || ''})
				]),
				el('div', {className: 'envira-ia-upsell-cta'}, [arrow, upgradeBtn, discount])
			]
		);

		overlay = el('div', {className: 'envira-ia-upsell-overlay'}, [card]);

		// Close on the × button, on a backdrop click, and on Escape.
		overlay.addEventListener('click', function (evt) {
			if (
				evt.target === overlay ||
				(evt.target.closest && evt.target.closest('.envira-ia-upsell-close'))
			) {
				closeModal();
			}
		});
		document.addEventListener('keydown', onKeydown);

		document.body.appendChild(overlay);
	}

	/* ─── Bulk button placement ───────────────────────────────────────── */
	// Lift the server-rendered toolbar to the far right of the "Currently in
	// your Gallery" title row (Pro's placement). The <p> can't host a <div>, so
	// wrap both the title and the toolbar in a relative row and pin the toolbar.
	function positionBulkToolbar() {
		const toolbar = document.querySelector('.envira-ia-toolbar-lite');
		if (!toolbar || toolbar.closest('.envira-ia-intro-row')) {
			return; // missing, or already lifted
		}
		// The gallery title is the .envira-intro rendered immediately before the
		// toolbar — anchor on THAT one, not document's first .envira-intro (lite
		// has several, many hidden, which would move the button off-screen).
		let intro = toolbar.previousElementSibling;
		while (intro && !(intro.classList && intro.classList.contains('envira-intro'))) {
			intro = intro.previousElementSibling;
		}
		if (!intro || !intro.parentNode) {
			return;
		}
		const row = el('div', {className: 'envira-ia-intro-row'});
		intro.parentNode.insertBefore(row, intro);
		row.appendChild(intro);
		row.appendChild(toolbar);
	}

	/* ─── Wiring ──────────────────────────────────────────────────────── */
	function init() {
		positionBulkToolbar();
		injectTriggers(document);

		// Re-inject when Envira re-renders the gallery output (upload / insert
		// replace or append <li>s, and those clones don't carry our button).
		const output = document.querySelector(GALLERY);
		if (output && window.MutationObserver) {
			new MutationObserver(function () {
				injectTriggers(document);
			}).observe(output, {childList: true, subtree: true});
		}

		// Delegated: any sparkle trigger or the bulk button opens the upsell.
		document.addEventListener(
			'click',
			function (evt) {
				const t =
					evt.target.closest &&
					evt.target.closest(
						'.envira-ia-upsell-trigger, .envira-ia-image-trigger, .envira-ia-bulk'
					);
				if (!t) {
					return;
				}
				evt.preventDefault();
				evt.stopPropagation();
				openModal();
			},
			true
		); // capture phase so the gallery's own select/click handlers don't fire first.
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
