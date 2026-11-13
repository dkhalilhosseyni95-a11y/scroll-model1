/**
 * Moon Core — capsule header behaviour: search overlay with live results, the
 * mini cart drawer, product quick view and the newsletter form.
 *
 * Depends on window.MoonCore (base.js) for init hooks and the AJAX config.
 */
(function () {
	'use strict';

	var config = window.moonCore || {};
	var MoonCore = window.MoonCore;

	if (!MoonCore) {
		return;
	}

	var scrim = null;
	var activeOverlay = null;
	var lastTrigger = null;

	function bodyLock(locked) {
		document.body.classList.toggle('moon-overlay-open', locked);
	}

	function getScrim() {
		if (!scrim) {
			scrim = document.querySelector('[data-moon-scrim]');
		}
		return scrim;
	}

	function openOverlay(el, trigger) {
		if (!el) {
			return;
		}
		if (activeOverlay && activeOverlay !== el) {
			closeOverlay(activeOverlay, true);
		}
		activeOverlay = el;
		lastTrigger = trigger || document.activeElement;
		el.classList.add('is-open');
		el.setAttribute('aria-hidden', 'false');
		if (trigger) {
			trigger.setAttribute('aria-expanded', 'true');
		}
		var lock = getScrim();
		if (lock) {
			lock.hidden = false;
			window.requestAnimationFrame(function () {
				lock.classList.add('is-visible');
			});
		}
		bodyLock(true);
		var focusTarget = el.querySelector('input, button, a');
		if (focusTarget) {
			window.setTimeout(function () {
				focusTarget.focus({ preventScroll: true });
			}, 60);
		}
	}

	function closeOverlay(el, skipFocus) {
		if (!el) {
			return;
		}
		el.classList.remove('is-open');
		el.setAttribute('aria-hidden', 'true');
		document.querySelectorAll('[data-moon-open-search], [data-moon-open-cart]').forEach(function (button) {
			button.setAttribute('aria-expanded', 'false');
		});
		var lock = getScrim();
		if (lock) {
			lock.classList.remove('is-visible');
			window.setTimeout(function () {
				if (!activeOverlay) {
					lock.hidden = true;
				}
			}, 350);
		}
		activeOverlay = null;
		bodyLock(false);
		if (!skipFocus && lastTrigger && typeof lastTrigger.focus === 'function') {
			lastTrigger.focus({ preventScroll: true });
		}
	}

	function isSearchOpen() {
		var el = document.getElementById('moon-search-overlay');
		return el && el.getAttribute('aria-hidden') === 'false';
	}

	function closeAll() {
		if (activeOverlay) {
			closeOverlay(activeOverlay);
		}
		var search = document.getElementById('moon-search-overlay');
		if (search) {
			search.setAttribute('aria-hidden', 'true');
		}
	}

	/* Search -------------------------------------------------------------- */

	function initSearch(root) {
		var overlay = root.querySelector('#moon-search-overlay');
		if (!overlay) {
			return;
		}

		var input = overlay.querySelector('[data-moon-live-search]');
		var results = overlay.querySelector('[data-moon-search-results]');
		var request = 0;

		function render(html) {
			if (!results) {
				return;
			}
			results.innerHTML = html;
		}

		function fetchResults() {
			if (!input || !results || !config.ajaxUrl) {
				return;
			}
			var term = input.value.trim();
			if (term.length < 2) {
				render('');
				return;
			}
			var id = ++request;
			results.innerHTML = '<p class="moon-search-results__empty">' + (config.i18n ? config.i18n.loading : '') + '</p>';

			var form = new FormData();
			form.append('action', 'moon_core_search');
			form.append('nonce', config.nonces.search);
			form.append('term', term);

			fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: form })
				.then(function (response) {
					return response.json();
				})
				.then(function (payload) {
					if (id !== request) {
						return;
					}
					render(payload && payload.success && payload.data ? payload.data.html : '');
				})
				.catch(function () {
					if (id === request) {
						render('');
					}
				});
		}

		if (input) {
			input.addEventListener('input', MoonCore.debounce(fetchResults, 280));
		}

		overlay.addEventListener('click', function (event) {
			if (event.target === overlay) {
				closeOverlay(overlay);
			}
		});

		overlay.querySelectorAll('[data-moon-close-search]').forEach(function (button) {
			button.addEventListener('click', function () {
				closeOverlay(overlay);
			});
		});
	}

	/* Cart drawer --------------------------------------------------------- */

	function initCart(root) {
		var drawer = root.querySelector('#moon-cart-drawer');
		if (!drawer) {
			return;
		}

		drawer.querySelectorAll('[data-moon-close-cart]').forEach(function (button) {
			button.addEventListener('click', function () {
				closeOverlay(drawer);
			});
		});

		drawer.addEventListener('click', function (event) {
			if (event.target === drawer) {
				closeOverlay(drawer);
			}
		});
	}

	/* Quick view ---------------------------------------------------------- */

	var quickView = { el: null, body: null, title: null };

	function ensureQuickView() {
		if (quickView.el) {
			return quickView.el;
		}

		var modal = document.createElement('div');
		modal.className = 'moon-modal moon-quick-view';
		modal.setAttribute('role', 'dialog');
		modal.setAttribute('aria-modal', 'true');
		modal.setAttribute('aria-hidden', 'true');
		modal.innerHTML =
			'<div class="moon-modal__panel">' +
			'<button type="button" class="moon-icon-btn moon-modal__close" data-moon-close-quick-view aria-label="' +
			(config.i18n ? config.i18n.close : 'Close') +
			'">&#10005;</button>' +
			'<div class="moon-modal__body" data-moon-quick-view-body></div>' +
			'</div>';

		document.body.appendChild(modal);

		quickView.el = modal;
		quickView.body = modal.querySelector('[data-moon-quick-view-body]');

		modal.addEventListener('click', function (event) {
			if (event.target === modal || event.target.closest('[data-moon-close-quick-view]')) {
				closeQuickView();
			}
		});

		return modal;
	}

	function closeQuickView() {
		if (!quickView.el) {
			return;
		}
		quickView.el.classList.remove('is-open');
		quickView.el.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('moon-overlay-open');
		window.setTimeout(function () {
			if (quickView.body) {
				quickView.body.innerHTML = '';
			}
		}, 300);
		if (lastTrigger && typeof lastTrigger.focus === 'function') {
			lastTrigger.focus({ preventScroll: true });
		}
	}

	function openQuickView(productId, trigger) {
		if (!config.quickView || !config.ajaxUrl) {
			return;
		}

		var modal = ensureQuickView();
		lastTrigger = trigger || document.activeElement;
		quickView.body.innerHTML = '<p class="moon-quick-view__loading">' + (config.i18n ? config.i18n.loading : '') + '</p>';
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');

		var form = new FormData();
		form.append('action', 'moon_core_quick_view');
		form.append('nonce', config.nonces.quickView);
		form.append('product_id', productId);

		fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: form })
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (payload && payload.success && payload.data) {
					quickView.body.innerHTML = payload.data.html;
					if (window.jQuery && window.jQuery.fn.wc_variation_form) {
						window.jQuery('.moon-quick-view__cart .variations_form').each(function () {
							window.jQuery(this).wc_variation_form();
						});
					}
					document.body.classList.add('moon-overlay-open');
					var first = quickView.body.querySelector('button, a, select');
					if (first) {
						first.focus({ preventScroll: true });
					}
				} else {
					quickView.body.innerHTML =
						'<p class="moon-quick-view__loading">' +
						(payload && payload.data && payload.data.message ? payload.data.message : config.i18n.error) +
						'</p>';
				}
			})
			.catch(function () {
				quickView.body.innerHTML = '<p class="moon-quick-view__loading">' + (config.i18n ? config.i18n.error : '') + '</p>';
			});
	}

	/* Newsletter ---------------------------------------------------------- */

	function initNewsletter(root) {
		root.querySelectorAll('[data-moon-newsletter]:not([data-moon-ready])').forEach(function (form) {
			form.setAttribute('data-moon-ready', '1');
			var status = form.querySelector('[data-moon-newsletter-status]');

			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var emailField = form.querySelector('input[name="email"]');
				var consent = form.querySelector('input[name="consent"]');
				var honeypot = form.querySelector('input[name="moon_company"]');

				if (!emailField || !emailField.value || !config.ajaxUrl) {
					return;
				}

				if (consent && !consent.checked) {
					if (status) {
						status.textContent = form.getAttribute('data-consent-message') || '';
						status.className = 'moon-newsletter-form__status is-error';
					}
					return;
				}

				if (status) {
					status.textContent = config.i18n.loading;
					status.className = 'moon-newsletter-form__status';
				}

				var payload = new FormData();
				payload.append('action', 'moon_core_newsletter');
				payload.append('nonce', config.nonces.newsletter);
				payload.append('email', emailField.value);
				payload.append('moon_company', honeypot ? honeypot.value : '');
				if (consent) {
					payload.append('consent', consent.checked ? '1' : '');
				}

				fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: payload })
					.then(function (response) {
						return response.json();
					})
					.then(function (result) {
						if (!status) {
							return;
						}
						var ok = result && result.success;
						status.textContent = result && result.data && result.data.message ? result.data.message : config.i18n.error;
						status.className = 'moon-newsletter-form__status ' + (ok ? 'is-success' : 'is-error');
						if (ok) {
							form.querySelectorAll('input').forEach(function (input) {
								if (input.type === 'email') {
									input.value = '';
								}
							});
						}
					})
					.catch(function () {
						if (status) {
							status.textContent = config.i18n.error;
							status.className = 'moon-newsletter-form__status is-error';
						}
					});
			});
		});
	}

	/* Global bindings ----------------------------------------------------- */

	MoonCore.register(function (root) {
		initSearch(root);
		initCart(root);
		initNewsletter(root);
	});

	MoonCore.ready(function () {
		document.addEventListener('click', function (event) {
			var searchTrigger = event.target.closest('[data-moon-open-search]');
			if (searchTrigger) {
				event.preventDefault();
				openOverlay(document.getElementById('moon-search-overlay'), searchTrigger);
				return;
			}

			var cartTrigger = event.target.closest('[data-moon-open-cart]');
			if (cartTrigger) {
				event.preventDefault();
				openOverlay(document.getElementById('moon-cart-drawer'), cartTrigger);
				return;
			}

			var quickTrigger = event.target.closest('[data-moon-quick-view]');
			if (quickTrigger) {
				event.preventDefault();
				openQuickView(quickTrigger.getAttribute('data-moon-quick-view'), quickTrigger);
				return;
			}

			if (event.target.closest('[data-moon-scrim]')) {
				closeAll();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key !== 'Escape') {
				return;
			}
			if (quickView.el && quickView.el.classList.contains('is-open')) {
				closeQuickView();
				document.body.classList.remove('moon-overlay-open');
				return;
			}
			closeAll();
		});

		// WooCommerce tells us when an AJAX add to cart completed.
		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', function () {
				var drawer = document.getElementById('moon-cart-drawer');
				if (drawer && config.cartUrl) {
					openOverlay(drawer, document.querySelector('[data-moon-open-cart]'));
				}
			});
		}
	});
})();
