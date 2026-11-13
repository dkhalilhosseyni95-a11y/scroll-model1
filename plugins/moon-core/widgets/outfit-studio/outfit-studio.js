/**
 * MOON Outfit Studio — synchronised outfit selection.
 *
 * One model, prepared frames. Selecting an outfit crossfades (or wipes) to the
 * matching frame, updates the copy and preloads the neighbouring images.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-studio]:not([data-moon-ready])').forEach(function (studio) {
			studio.setAttribute('data-moon-ready', '1');

			var frames = Array.prototype.slice.call(studio.querySelectorAll('[data-moon-studio-frame]'));
			var thumbs = Array.prototype.slice.call(studio.querySelectorAll('[data-moon-studio-thumb]'));
			var dots = studio.querySelector('[data-moon-studio-dots]');
			var titleEl = studio.querySelector('[data-moon-studio-title]');
			var colorEl = studio.querySelector('[data-moon-studio-color]');
			var priceEl = studio.querySelector('[data-moon-studio-price]');
			var noteEl = studio.querySelector('[data-moon-studio-note]');
			var linkEl = studio.querySelector('[data-moon-studio-link]');
			var currentEl = studio.querySelector('[data-moon-studio-current]');
			var total = frames.length;
			var index = 0;
			var preloaded = {};

			if (!total) {
				return;
			}

			function pad(value) {
				return String(value + 1).padStart(2, '0');
			}

			function preload(target) {
				var frame = frames[target];
				if (!frame || preloaded[target]) {
					return;
				}
				var img = frame.querySelector('img');
				if (!img) {
					return;
				}
				var loader = new Image();
				loader.src = img.currentSrc || img.src;
				preloaded[target] = true;
			}

			function preloadNeighbours() {
				preload((index + 1) % total);
				preload((index - 1 + total) % total);
			}

			function dotButtons() {
				if (!dots) {
					return [];
				}
				if (dots.children.length) {
					return Array.prototype.slice.call(dots.children);
				}
				for (var i = 0; i < total; i++) {
					var button = document.createElement('button');
					button.type = 'button';
					button.setAttribute('role', 'tab');
					button.setAttribute('aria-label', String(i + 1));
					button.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
					button.setAttribute('data-moon-studio-dot', String(i));
					button.addEventListener('click', function (event) {
						go(parseInt(event.currentTarget.getAttribute('data-moon-studio-dot'), 10));
					});
					dots.appendChild(button);
				}
				return Array.prototype.slice.call(dots.children);
			}

			var dotList = dotButtons();

			function go(next) {
				if (next === index || next < 0 || next >= total) {
					return;
				}

				var previous = index;
				index = next;

				frames.forEach(function (frame, i) {
					frame.classList.toggle('is-active', i === index);
					frame.classList.toggle('is-leaving', i === previous && previous !== index);
					frame.setAttribute('aria-hidden', i === index ? 'false' : 'true');
				});

				window.setTimeout(function () {
					frames.forEach(function (frame) {
						frame.classList.remove('is-leaving');
					});
				}, 900);

				var thumb = thumbs[index];
				if (thumb) {
					var data = thumb.dataset;
					if (titleEl && data.title) {
						titleEl.textContent = data.title;
					}
					if (colorEl && data.color) {
						colorEl.textContent = data.color;
					}
					if (priceEl && data.price) {
						priceEl.textContent = data.price;
					}
					if (noteEl && data.note) {
						noteEl.textContent = data.note;
					}
					if (linkEl && data.link) {
						linkEl.setAttribute('href', data.link);
					}
				}

				thumbs.forEach(function (button, i) {
					button.classList.toggle('is-active', i === index);
					button.setAttribute('aria-selected', i === index ? 'true' : 'false');
				});

				dotList.forEach(function (button, i) {
					button.setAttribute('aria-selected', i === index ? 'true' : 'false');
				});

				if (currentEl) {
					currentEl.textContent = pad(index);
				}

				preloadNeighbours();
			}

			function step(direction) {
				var next = index + direction;
				if (next < 0) {
					next = total - 1;
				}
				if (next >= total) {
					next = 0;
				}
				go(next);
			}

			studio.querySelectorAll('[data-moon-studio-next]').forEach(function (button) {
				button.addEventListener('click', function () {
					step(1);
				});
			});

			studio.querySelectorAll('[data-moon-studio-prev]').forEach(function (button) {
				button.addEventListener('click', function () {
					step(-1);
				});
			});

			thumbs.forEach(function (button, i) {
				button.addEventListener('click', function () {
					go(i);
				});
			});

			studio.addEventListener('keydown', function (event) {
				if (event.key === 'ArrowRight') {
					event.preventDefault();
					step(1);
				}
				if (event.key === 'ArrowLeft') {
					event.preventDefault();
					step(-1);
				}
			});

			// Vertical swipe on touch devices.
			var startY = null;
			var stage = studio.querySelector('[data-moon-studio-stage]');
			if (stage) {
				stage.addEventListener(
					'touchstart',
					function (event) {
						startY = event.touches[0].clientY;
					},
					{ passive: true }
				);
				stage.addEventListener(
					'touchend',
					function (event) {
						if (startY === null) {
							return;
						}
						var delta = event.changedTouches[0].clientY - startY;
						if (Math.abs(delta) > 40) {
							step(delta < 0 ? 1 : -1);
						}
						startY = null;
					},
					{ passive: true }
				);
			}

			preloadNeighbours();
		});
	});
})();
