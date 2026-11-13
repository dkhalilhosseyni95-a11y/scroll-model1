/**
 * MOON theme runtime: colour scheme, scroll-aware chrome and the fallback mobile
 * menu. Moon Core widgets register their own behaviour in window.MoonCore.
 */
(function () {
	'use strict';

	var root = document.documentElement;
	var STORAGE_KEY = 'moon-scheme';
	var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

	function readScheme() {
		try {
			return localStorage.getItem(STORAGE_KEY) || 'auto';
		} catch (e) {
			return 'auto';
		}
	}

	function applyScheme(scheme) {
		root.setAttribute('data-moon-scheme', scheme);
		try {
			localStorage.setItem(STORAGE_KEY, scheme);
		} catch (e) {
			/* storage disabled — the attribute still applies for this page view */
		}
		document.querySelectorAll('[data-moon-toggle-scheme]').forEach(function (button) {
			var isDark = scheme === 'dark' || (scheme === 'auto' && media && media.matches);
			button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
			var label = button.getAttribute(isDark ? 'data-label-light' : 'data-label-dark');
			if (label) {
				button.setAttribute('aria-label', label);
				var text = button.querySelector('.moon-scheme-toggle__label');
				if (text) {
					text.textContent = label;
				}
			}
			button.classList.toggle('is-dark', !!isDark);
		});
		document.dispatchEvent(new CustomEvent('moon:scheme', { detail: { scheme: scheme } }));
	}

	function toggleScheme() {
		var current = readScheme();
		var isDark = current === 'dark' || (current === 'auto' && media && media.matches);
		applyScheme(isDark ? 'light' : 'dark');
	}

	function initScheme() {
		applyScheme(readScheme());
		document.addEventListener('click', function (event) {
			var trigger = event.target.closest('[data-moon-toggle-scheme]');
			if (!trigger) {
				return;
			}
			event.preventDefault();
			toggleScheme();
		});
		if (media && media.addEventListener) {
			media.addEventListener('change', function () {
				if (readScheme() === 'auto') {
					applyScheme('auto');
				}
			});
		}
	}

	function initScrollState() {
		var headers = document.querySelectorAll('.moon-header');
		if (!headers.length) {
			return;
		}
		var last = window.scrollY;
		var ticking = false;

		function update() {
			var y = window.scrollY;
			headers.forEach(function (header) {
				header.classList.toggle('is-stuck', y > 24);
				var hideOnDown = header.getAttribute('data-hide-on-scroll') === 'yes';
				if (hideOnDown && y > 220) {
					header.classList.toggle('is-hidden', y > last);
				} else {
					header.classList.remove('is-hidden');
				}
			});
			root.style.setProperty('--moon-header-offset', document.body.classList.contains('admin-bar') ? '32px' : '0px');
			last = y;
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
		update();
	}

	function initFallbackMenu() {
		document.querySelectorAll('[data-moon-menu-toggle]').forEach(function (toggle) {
			toggle.addEventListener('click', function () {
				var target = document.querySelector(toggle.getAttribute('data-moon-menu-toggle'));
				if (!target) {
					return;
				}
				var open = target.classList.toggle('is-open');
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
				document.body.classList.toggle('moon-menu-open', open);
			});
		});
	}

	function boot() {
		initScheme();
		initScrollState();
		initFallbackMenu();
		document.body.classList.add('moon-js-ready');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
