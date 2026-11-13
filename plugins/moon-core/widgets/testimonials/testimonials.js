/**
 * MOON Testimonials slider — native scroll-snap track with arrows, dots and
 * optional auto-advance that never fights reduced-motion preferences.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-testi]:not([data-moon-ready])').forEach(function (viewport) {
			viewport.setAttribute('data-moon-ready', '1');

			var track = viewport.querySelector('[data-moon-testi-track]');
			if (!track) {
				return;
			}

			var cards = Array.prototype.slice.call(track.children);
			var prev = viewport.querySelector('[data-moon-testi-prev]');
			var next = viewport.querySelector('[data-moon-testi-next]');
			var dots = viewport.querySelector('[data-moon-testi-dots]');
			var autoplay = viewport.getAttribute('data-autoplay') === '1' && !MoonCore.reducedMotion;
			var timer = null;

			function step() {
				var card = cards[0];
				return card ? card.getBoundingClientRect().width + 20 : 300;
			}

			function scrollBy(dir) {
				track.scrollBy({ left: dir * step(), behavior: MoonCore.reducedMotion ? 'auto' : 'smooth' });
			}

			if (prev) {
				prev.addEventListener('click', function () {
					scrollBy(-1);
				});
			}

			if (next) {
				next.addEventListener('click', function () {
					scrollBy(1);
				});
			}

			if (dots && cards.length) {
				cards.forEach(function (card, index) {
					var button = document.createElement('button');
					button.type = 'button';
					button.setAttribute('role', 'tab');
					button.setAttribute('aria-label', String(index + 1));
					button.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
					button.addEventListener('click', function () {
						track.scrollTo({ left: index * step(), behavior: MoonCore.reducedMotion ? 'auto' : 'smooth' });
					});
					dots.appendChild(button);
				});

				var updateDots = function () {
					var width = step();
					var current = Math.round(track.scrollLeft / width);
					Array.prototype.forEach.call(dots.children, function (dot, index) {
						dot.setAttribute('aria-selected', index === current ? 'true' : 'false');
					});
				};

				track.addEventListener('scroll', MoonCore.debounce(updateDots, 120), { passive: true });
			}

			viewport.addEventListener('keydown', function (event) {
				if (event.key === 'ArrowRight') {
					scrollBy(1);
				}
				if (event.key === 'ArrowLeft') {
					scrollBy(-1);
				}
			});

			if (autoplay) {
				var start = function () {
					timer = window.setInterval(function () {
						var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 8;
						if (atEnd) {
							track.scrollTo({ left: 0, behavior: 'smooth' });
						} else {
							scrollBy(1);
						}
					}, 6000);
				};

				start();

				viewport.addEventListener('mouseenter', function () {
					window.clearInterval(timer);
				});
				viewport.addEventListener('mouseleave', start);
				viewport.addEventListener('focusin', function () {
					window.clearInterval(timer);
				});
			}
		});
	});
})();
