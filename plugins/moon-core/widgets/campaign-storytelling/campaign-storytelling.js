/**
 * MOON Campaign Storytelling — scroll spy for the sticky media column.
 *
 * Uses IntersectionObserver; falls back to the first chapter when unsupported.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-campaign]:not([data-moon-ready])').forEach(function (section) {
			section.setAttribute('data-moon-ready', '1');

			var chapters = Array.prototype.slice.call(section.querySelectorAll('[data-moon-campaign-chapter]'));
			var frames = Array.prototype.slice.call(section.querySelectorAll('[data-moon-campaign-frame]'));
			var bar = section.querySelector('[data-moon-campaign-bar]');

			if (!chapters.length || !frames.length) {
				return;
			}

			function activate(index) {
				chapters.forEach(function (chapter, i) {
					chapter.classList.toggle('is-active', i === index);
				});
				frames.forEach(function (frame, i) {
					frame.classList.toggle('is-active', i === index);
				});
				if (bar) {
					bar.style.width = Math.round(((index + 1) / chapters.length) * 100) + '%';
				}
			}

			activate(0);

			if (!('IntersectionObserver' in window)) {
				return;
			}

			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (!entry.isIntersecting) {
							return;
						}
						var index = chapters.indexOf(entry.target);
						if (index !== -1) {
							activate(index);
						}
					});
				},
				{ rootMargin: '-35% 0px -45% 0px', threshold: 0 }
			);

			chapters.forEach(function (chapter) {
				observer.observe(chapter);
			});
		});
	});
})();
