/**
 * Moon Core runtime.
 *
 * Shared by every MOON widget: a tiny init registry (works with Elementor's
 * frontend re-render events) and the scroll-reveal engine. No library, no
 * globals beyond window.MoonCore.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore || {};
	var inits = [];
	var started = false;
	var reduceQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

	MoonCore.reducedMotion = !!(reduceQuery && reduceQuery.matches);

	function run(init, root) {
		try {
			init(root || document);
		} catch (error) {
			if (window.console && console.error) {
				console.error('[MoonCore]', error);
			}
		}
	}

	function runAll(root) {
		inits.forEach(function (init) {
			run(init, root);
		});
	}

	/**
	 * Register a widget initialiser. It runs once at boot and again whenever
	 * Elementor renders a widget (editor / dynamic content), so every function
	 * must be idempotent (mark initialised nodes).
	 */
	MoonCore.register = function (init) {
		if (typeof init !== 'function') {
			return;
		}
		inits.push(init);
		if (started) {
			run(init, document);
		}
	};

	MoonCore.ready = function (fn) {
		if (started) {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn, { once: true });
		}
	};

	MoonCore.debounce = function (fn, wait) {
		var timer = null;
		return function () {
			var args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(null, args);
			}, wait || 250);
		};
	};

	/* Scroll reveal ------------------------------------------------------- */

	function initReveal(root) {
		// A dedicated marker: widget initialisers keep using [data-moon-ready] on
		// the very same element without the reveal engine making them look ready.
		var nodes = root.querySelectorAll('[data-moon-reveal]:not([data-moon-reveal-init])');

		nodes.forEach(function (el) {
			el.setAttribute('data-moon-reveal-init', '1');

			if (MoonCore.reducedMotion || !('IntersectionObserver' in window)) {
				el.classList.add('moon-reveal', 'is-revealed');
				return;
			}

			var delay = parseInt(el.getAttribute('data-moon-reveal-delay') || '0', 10) || 0;
			var stagger = parseInt(el.getAttribute('data-moon-reveal-stagger') || '0', 10) || 0;

			el.style.setProperty('--moon-reveal-delay', delay + 'ms');

			el.querySelectorAll('[data-moon-reveal-item]').forEach(function (item, index) {
				item.style.setProperty('--moon-item-delay', delay + index * stagger + 'ms');
			});

			el.classList.add('moon-reveal');

			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (!entry.isIntersecting) {
							return;
						}
						el.classList.add('is-revealed');
						observer.disconnect();
					});
				},
				{ rootMargin: '0px 0px -6% 0px', threshold: 0.06 }
			);

			observer.observe(el);

			// Anything above the fold should not wait for a scroll event.
			var rect = el.getBoundingClientRect();
			if (rect.top < window.innerHeight * 0.9 && rect.bottom > 0) {
				window.requestAnimationFrame(function () {
					el.classList.add('is-revealed');
				});
			}
		});
	}

	MoonCore.initReveal = initReveal;

	/* Boot ---------------------------------------------------------------- */

	function boot() {
		started = true;
		initReveal(document);
		runAll(document);

		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
				var scope = $scope && $scope[0] ? $scope[0] : document;
				initReveal(scope);
				runAll(scope);
			});
		}

		document.addEventListener('moon:scheme', function () {
			document.dispatchEvent(new CustomEvent('moon:refresh'));
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot, { once: true });
	} else {
		boot();
	}

	window.MoonCore = MoonCore;
})();
