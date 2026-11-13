/**
 * MOON Clothing Rack — selection, lift and return.
 *
 * Buttons only: keyboard and screen-reader users get the same interaction as
 * pointer users. The detail panels are pre-rendered, so no request is needed.
 */
(function () {
	'use strict';

	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	MoonCore.register(function (root) {
		root.querySelectorAll('[data-moon-rack]:not([data-moon-ready])').forEach(function (rack) {
			rack.setAttribute('data-moon-ready', '1');

			var triggers = Array.prototype.slice.call(rack.querySelectorAll('[data-moon-rack-trigger]'));
			var details = Array.prototype.slice.call(rack.querySelectorAll('[data-moon-rack-detail]'));
			var items = Array.prototype.slice.call(rack.querySelectorAll('[data-moon-rack-item]'));

			function show(index, trigger) {
				items.forEach(function (item, i) {
					item.classList.toggle('is-selected', i === index);
					item.classList.toggle('is-dimmed', index !== null && i !== index);
				});

				triggers.forEach(function (button, i) {
					button.setAttribute('aria-pressed', i === index ? 'true' : 'false');
				});

				details.forEach(function (panel, i) {
					var active = i === index;
					panel.classList.toggle('is-active', active);
					if (active) {
						panel.removeAttribute('aria-hidden');
					} else {
						panel.setAttribute('aria-hidden', 'true');
					}
				});

				if (trigger && index !== null) {
					trigger.setAttribute('aria-live', 'polite');
				}
			}

			function reset() {
				items.forEach(function (item) {
					item.classList.remove('is-selected', 'is-dimmed');
					item.classList.add('is-returning');
					window.setTimeout(function () {
						item.classList.remove('is-returning');
					}, 620);
				});

				triggers.forEach(function (button) {
					button.setAttribute('aria-pressed', 'false');
				});

				details.forEach(function (panel, i) {
					panel.classList.toggle('is-active', i === 0);
					if (i === 0) {
						panel.removeAttribute('aria-hidden');
					} else {
						panel.setAttribute('aria-hidden', 'true');
					}
				});
			}

			triggers.forEach(function (trigger, index) {
				trigger.addEventListener('click', function () {
					var isSelected = trigger.getAttribute('aria-pressed') === 'true';
					if (isSelected) {
						reset();
						return;
					}
					show(index, trigger);
				});
			});

			rack.querySelectorAll('[data-moon-rack-return]').forEach(function (button) {
				button.addEventListener('click', function () {
					reset();
					var firstTrigger = triggers[0];
					if (firstTrigger) {
						firstTrigger.focus({ preventScroll: true });
					}
				});
			});

			// Arrow keys move between garments once the rail has focus.
			rack.addEventListener('keydown', function (event) {
				if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
					return;
				}

				var current = triggers.indexOf(document.activeElement);
				if (current === -1) {
					return;
				}

				event.preventDefault();
				var step = event.key === 'ArrowRight' ? 1 : -1;
				var next = (current + step + triggers.length) % triggers.length;
				triggers[next].focus({ preventScroll: true });
			});
		});
	});
})();
