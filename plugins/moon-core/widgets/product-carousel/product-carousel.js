/**
 * MOON Product Carousel — scroll-snap track driven by arrows and progress.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-carousel]:not([data-moon-ready])').forEach(function (carousel) {
			carousel.setAttribute('data-moon-ready', '1');

			var track = carousel.querySelector('[data-moon-carousel-track]');
			var bar = carousel.querySelector('[data-moon-carousel-progress]');

			if (!track) {
				return;
			}

			function step() {
				var slide = track.querySelector('.moon-carousel__slide');
				if (!slide) {
					return 300;
				}
				var styles = window.getComputedStyle(track);
				var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
				return slide.getBoundingClientRect().width + gap;
			}

			function scrollBy(direction) {
				track.scrollBy({ left: direction * step(), behavior: MoonCore.reducedMotion ? 'auto' : 'smooth' });
			}

			carousel.querySelectorAll('[data-moon-carousel-next]').forEach(function (button) {
				button.addEventListener('click', function () {
					scrollBy(1);
				});
			});

			carousel.querySelectorAll('[data-moon-carousel-prev]').forEach(function (button) {
				button.addEventListener('click', function () {
					scrollBy(-1);
				});
			});

			var updateProgress = function () {
				if (!bar) {
					return;
				}
				var max = track.scrollWidth - track.clientWidth;
				var ratio = max > 0 ? track.scrollLeft / max : 0;
				var width = Math.max(18, (track.clientWidth / track.scrollWidth) * 100);
				bar.style.width = width + '%';
				bar.style.marginInlineStart = Math.min(100 - width, ratio * (100 - width)) + '%';
			};

			track.addEventListener('scroll', MoonCore.debounce(updateProgress, 80), { passive: true });
			window.addEventListener('resize', updateProgress, { passive: true });
			updateProgress();
		});
	});
})();
