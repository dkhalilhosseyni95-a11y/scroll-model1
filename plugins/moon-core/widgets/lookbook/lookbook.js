/**
 * MOON Lookbook — accessible image lightbox.
 *
 * The tile is a button, the overlay traps Escape, focus returns to the tile.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-lookbook="lightbox"]:not([data-moon-ready])').forEach(function (section) {
			section.setAttribute('data-moon-ready', '1');

			var overlay = document.createElement('div');
			overlay.className = 'moon-look__lightbox';
			overlay.setAttribute('role', 'dialog');
			overlay.setAttribute('aria-modal', 'true');
			overlay.setAttribute('aria-hidden', 'true');
			overlay.innerHTML =
				'<figure><img alt=""><figcaption></figcaption></figure>' +
				'<button type="button" class="moon-icon-btn" data-moon-look-close aria-label="Close">&#10005;</button>';
			document.body.appendChild(overlay);

			var image = overlay.querySelector('img');
			var caption = overlay.querySelector('figcaption');
			var trigger = null;

			function close() {
				overlay.classList.remove('is-open');
				overlay.setAttribute('aria-hidden', 'true');
				document.body.classList.remove('moon-overlay-open');
				if (trigger) {
					trigger.focus({ preventScroll: true });
				}
			}

			section.querySelectorAll('[data-moon-look-trigger]').forEach(function (button) {
				button.addEventListener('click', function () {
					var src = button.getAttribute('data-src');
					if (!src) {
						return;
					}
					trigger = button;
					image.src = src;
					image.alt = button.getAttribute('data-caption') || '';
					caption.textContent = button.getAttribute('data-caption') || '';
					overlay.classList.add('is-open');
					overlay.setAttribute('aria-hidden', 'false');
					document.body.classList.add('moon-overlay-open');
					overlay.querySelector('[data-moon-look-close]').focus({ preventScroll: true });
				});
			});

			overlay.addEventListener('click', function (event) {
				if (event.target === overlay || event.target.closest('[data-moon-look-close]')) {
					close();
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
					close();
				}
			});
		});
	});
})();
