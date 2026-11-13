/**
 * MOON Image Reveal — subtle parallax inside the frame.
 *
 * Only runs when the visitor allows motion, and only transforms are animated so
 * the browser can keep it on the compositor.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-image-reveal="parallax"]:not([data-moon-ready])').forEach(function (figure) {
			figure.setAttribute('data-moon-ready', '1');

			if (MoonCore.reducedMotion) {
				return;
			}

			var image = figure.querySelector('.moon-reveal-img__img');
			if (!image) {
				return;
			}

			var ticking = false;
			var max = 34;

			function update() {
				var rect = figure.getBoundingClientRect();
				var viewport = window.innerHeight || 800;
				if (rect.bottom < -200 || rect.top > viewport + 200) {
					ticking = false;
					return;
				}
				var progress = (rect.top + rect.height / 2 - viewport / 2) / viewport;
				var shift = Math.max(-1, Math.min(1, progress)) * -max;
				image.style.transform = 'scale(1.06) translate3d(0,' + shift.toFixed(1) + 'px,0)';
				ticking = false;
			}

			window.addEventListener(
				'scroll',
				function () {
					if (!ticking) {
						ticking = true;
						window.requestAnimationFrame(update);
					}
				},
				{ passive: true }
			);

			window.addEventListener('resize', update, { passive: true });
			update();
		});
	});
})();
