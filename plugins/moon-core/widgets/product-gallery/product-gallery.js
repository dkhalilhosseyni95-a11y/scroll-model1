/**
 * MOON Product Gallery — thumbnail switching on the product's own images.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-product-gallery]:not([data-moon-ready])').forEach(function (gallery) {
			gallery.setAttribute('data-moon-ready', '1');

			var main = gallery.querySelector('[data-moon-pg-main]');
			var slides = Array.prototype.slice.call(gallery.querySelectorAll('[data-moon-pg-slide]'));
			var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-moon-pg-thumb]'));

			if (!main || !thumbs.length) {
				return;
			}

			var current = 0;

			thumbs.forEach(function (thumb, index) {
				thumb.addEventListener('click', function () {
					if (index === current) {
						return;
					}

					current = index;
					thumbs.forEach(function (button, i) {
						button.classList.toggle('is-active', i === index);
						button.setAttribute('aria-selected', i === index ? 'true' : 'false');
					});

					if (index === 0) {
						main.classList.remove('is-hidden');
						slides.forEach(function (slide) {
							slide.classList.remove('is-active');
							slide.setAttribute('aria-hidden', 'true');
						});
						return;
					}

					var target = slides[index - 1];
					slides.forEach(function (slide) {
						var active = slide === target;
						slide.classList.toggle('is-active', active);
						slide.setAttribute('aria-hidden', active ? 'false' : 'true');
					});
				});
			});
		});
	});
})();
