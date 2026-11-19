/**
 * NETELEC front-end interactions.
 * Vanilla JS, no dependencies (WooCommerce's jQuery is used only for its events).
 */
(function () {
	'use strict';

	var data = window.netelecData || {};
	var i18n = data.i18n || {};

	var $ = function (selector, context) {
		return (context || document).querySelector(selector);
	};
	var $$ = function (selector, context) {
		return Array.prototype.slice.call((context || document).querySelectorAll(selector));
	};

	/* ---------------------------------------------------------------------
	 * Toast
	 * ------------------------------------------------------------------- */
	var toastEl = $('.nc-toast');
	var toastTimer;

	function toast(message, type) {
		if (!toastEl) {
			return;
		}
		toastEl.textContent = message;
		toastEl.className = 'nc-toast' + (type ? ' is-' + type : '');
		toastEl.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toastEl.hidden = true;
		}, 3200);
	}

	/* ---------------------------------------------------------------------
	 * AJAX helper
	 * ------------------------------------------------------------------- */
	function post(action, payload) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', data.nonce || '');
		Object.keys(payload || {}).forEach(function (key) {
			body.append(key, payload[key]);
		});

		return fetch(data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (response) {
				return response.json().then(function (json) {
					if (!json || !json.success) {
						var message = json && json.data && json.data.message ? json.data.message : i18n.error;
						throw new Error(message);
					}
					return json.data;
				});
			});
	}

	/* ---------------------------------------------------------------------
	 * Mobile drawer + overlay
	 * ------------------------------------------------------------------- */
	var drawer = $('#mobile-drawer');
	var overlay = $('.nc-overlay');

	function openDrawer() {
		if (!drawer) {
			return;
		}
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		document.body.classList.add('nc-no-scroll');
		if (overlay) {
			overlay.hidden = false;
			requestAnimationFrame(function () {
				overlay.classList.add('is-visible');
			});
		}
		var toggle = $('.nav-toggle');
		if (toggle) {
			toggle.setAttribute('aria-expanded', 'true');
		}
	}

	function closeDrawer() {
		if (!drawer) {
			return;
		}
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('nc-no-scroll');
		if (overlay) {
			overlay.classList.remove('is-visible');
			setTimeout(function () {
				overlay.hidden = true;
			}, 250);
		}
		var toggle = $('.nav-toggle');
		if (toggle) {
			toggle.setAttribute('aria-expanded', 'false');
		}
		var filters = $('#shop-filters');
		if (filters) {
			filters.classList.remove('is-open');
		}
	}

	var navToggle = $('.nav-toggle');
	if (navToggle) {
		navToggle.addEventListener('click', function () {
			if (drawer && drawer.classList.contains('is-open')) {
				closeDrawer();
			} else {
				openDrawer();
			}
		});
	}

	$$('.mobile-drawer__close').forEach(function (button) {
		button.addEventListener('click', closeDrawer);
	});

	if (overlay) {
		overlay.addEventListener('click', closeDrawer);
	}

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeDrawer();
			closeQuickView();
		}
	});

	/* ---------------------------------------------------------------------
	 * Sticky header shadow + back to top
	 * ------------------------------------------------------------------- */
	var header = $('#site-header');
	var topButton = $('.floating-btn--top');

	function onScroll() {
		var scrolled = window.scrollY > 8;
		if (header) {
			header.classList.toggle('is-scrolled', scrolled);
		}
		if (topButton) {
			topButton.hidden = window.scrollY < 420;
		}
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	if (topButton) {
		topButton.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	/* ---------------------------------------------------------------------
	 * Category dropdown
	 * ------------------------------------------------------------------- */
	var catNav = $('.cat-nav');
	if (catNav) {
		var catToggle = $('.cat-nav__toggle', catNav);
		catToggle.addEventListener('click', function (event) {
			event.stopPropagation();
			var open = catNav.classList.toggle('is-open');
			catToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		document.addEventListener('click', function (event) {
			if (!catNav.contains(event.target)) {
				catNav.classList.remove('is-open');
				catToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Shop filters drawer (mobile)
	 * ------------------------------------------------------------------- */
	var filterToggle = $('.shop-filter-toggle');
	var shopSidebar = $('#shop-filters');

	if (filterToggle && shopSidebar) {
		filterToggle.addEventListener('click', function () {
			shopSidebar.classList.add('is-open');
			document.body.classList.add('nc-no-scroll');
			if (overlay) {
				overlay.hidden = false;
				requestAnimationFrame(function () {
					overlay.classList.add('is-visible');
				});
			}
			filterToggle.setAttribute('aria-expanded', 'true');
		});

		$$('.shop-sidebar__close').forEach(function (button) {
			button.addEventListener('click', closeDrawer);
		});
	}

	/* ---------------------------------------------------------------------
	 * Live search
	 * ------------------------------------------------------------------- */
	var searchWrap = $('[data-live-search]');
	if (searchWrap && data.liveSearch) {
		var searchInput = $('input[type="search"]', searchWrap);
		var resultsBox = $('.live-search', searchWrap);
		var searchTimer = null;
		var searchController = null;

		var renderResults = function (payload, term) {
			if (!payload.items.length) {
				resultsBox.innerHTML = '<div class="live-search__empty">' + (i18n.noResults || 'نتیجه‌ای پیدا نشد') + '</div>';
			} else {
				var html = '<ul class="live-search__list">';
				payload.items.forEach(function (item) {
					html +=
						'<li class="live-search__item"><a href="' + item.url + '">' +
						'<img class="live-search__thumb" src="' + item.thumb + '" alt="" loading="lazy" width="48" height="48">' +
						'<span><span class="live-search__title">' + item.title + '</span>' +
						'<span class="live-search__price">' + item.price + '</span></span>' +
						'</a></li>';
				});
				html += '</ul>';
				html += '<a class="live-search__all" href="' + payload.more_url + '">مشاهده همه نتایج</a>';
				resultsBox.innerHTML = html;
			}
			resultsBox.hidden = false;
			searchInput.setAttribute('aria-expanded', 'true');
		};

		var runSearch = function (term) {
			if (searchController) {
				searchController.abort();
			}
			searchController = new AbortController();

			resultsBox.hidden = false;
			resultsBox.innerHTML = '<div class="live-search__empty">' + (i18n.searching || '…') + '</div>';

			var body = new URLSearchParams();
			body.append('action', 'netelec_live_search');
			body.append('nonce', data.nonce || '');
			body.append('term', term);

			fetch(data.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString(),
				signal: searchController.signal
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (json) {
					if (json && json.success) {
						renderResults(json.data, term);
					}
				})
				.catch(function (error) {
					if (error.name !== 'AbortError') {
						resultsBox.hidden = true;
					}
				});
		};

		searchInput.addEventListener('input', function () {
			var term = searchInput.value.trim();
			clearTimeout(searchTimer);
			if (term.length < 2) {
				resultsBox.hidden = true;
				return;
			}
			searchTimer = setTimeout(function () {
				runSearch(term);
			}, 280);
		});

		searchInput.addEventListener('focus', function () {
			if (resultsBox.innerHTML.trim() && searchInput.value.trim().length >= 2) {
				resultsBox.hidden = false;
			}
		});

		document.addEventListener('click', function (event) {
			if (!searchWrap.contains(event.target)) {
				resultsBox.hidden = true;
				searchInput.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Wishlist + compare toggles
	 * ------------------------------------------------------------------- */
	function updateCounters(selector, count) {
		$$(selector).forEach(function (element) {
			element.textContent = count;
		});
	}

	function syncButtons(attribute, ids) {
		$$('[' + attribute + ']').forEach(function (button) {
			var id = parseInt(button.getAttribute(attribute), 10);
			button.classList.toggle('is-active', ids.indexOf(id) !== -1);
		});
	}

	document.addEventListener('click', function (event) {
		var wishlistButton = event.target.closest('[data-wishlist-toggle]');
		if (wishlistButton) {
			event.preventDefault();
			var wishlistId = wishlistButton.getAttribute('data-wishlist-toggle');
			wishlistButton.disabled = true;
			post('netelec_wishlist_toggle', { product_id: wishlistId })
				.then(function (result) {
					updateCounters('[data-wishlist-count]', result.count);
					syncButtons('data-wishlist-toggle', result.ids);
					toast(result.in_list ? i18n.wishlistAdd : i18n.wishlistDel);
					if (document.body.classList.contains('wishlist-page') || $('.wishlist-page')) {
						window.location.reload();
					}
				})
				.catch(function (error) {
					toast(error.message, 'error');
				})
				.finally(function () {
					wishlistButton.disabled = false;
				});
			return;
		}

		var compareButton = event.target.closest('[data-compare-toggle]');
		if (compareButton) {
			event.preventDefault();
			var compareId = compareButton.getAttribute('data-compare-toggle');
			compareButton.disabled = true;
			post('netelec_compare_toggle', { product_id: compareId })
				.then(function (result) {
					updateCounters('[data-compare-count]', result.count);
					syncButtons('data-compare-toggle', result.ids);
					toast(result.in_list ? i18n.compareAdd : i18n.compareDel);
					if ($('.compare-page')) {
						window.location.reload();
					}
				})
				.catch(function (error) {
					toast(error.message, 'error');
				})
				.finally(function () {
					compareButton.disabled = false;
				});
		}
	});

	/* ---------------------------------------------------------------------
	 * Quick view
	 * ------------------------------------------------------------------- */
	var quickView = $('#quick-view');
	var quickViewBody = quickView ? $('.quick-view-modal__body', quickView) : null;

	function closeQuickView() {
		if (!quickView || quickView.hidden) {
			return;
		}
		quickView.hidden = true;
		document.body.classList.remove('nc-no-scroll');
		if (quickViewBody) {
			quickViewBody.innerHTML = '';
		}
	}

	if (quickView) {
		$$('.quick-view-modal__close', quickView).forEach(function (button) {
			button.addEventListener('click', closeQuickView);
		});
		quickView.addEventListener('click', function (event) {
			if (event.target === quickView) {
				closeQuickView();
			}
		});
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-quick-view]');
		if (!button || !quickView || !quickViewBody) {
			return;
		}
		event.preventDefault();

		quickView.hidden = false;
		document.body.classList.add('nc-no-scroll');
		quickViewBody.innerHTML = '<div class="quick-view-loading">در حال بارگذاری…</div>';

		post('netelec_quick_view', { product_id: button.getAttribute('data-quick-view') })
			.then(function (result) {
				quickViewBody.innerHTML = result.html;
			})
			.catch(function (error) {
				quickViewBody.innerHTML = '<div class="quick-view-loading">' + error.message + '</div>';
			});
	});

	/* ---------------------------------------------------------------------
	 * WooCommerce: add-to-cart feedback
	 * ------------------------------------------------------------------- */
	if (window.jQuery) {
		window.jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, button) {
			toast(i18n.added || 'به سبد خرید اضافه شد');
			if (button && button.addClass) {
				button.addClass('added');
				setTimeout(function () {
					button.removeClass('added');
				}, 2400);
			}
		});

		window.jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
			// Keep the header counters in sync when WooCommerce refreshes fragments.
			var count = $('.cart-count');
			if (count) {
				count.textContent = count.textContent.replace(/[۰-۹0-9]/g, function (digit) {
					return digit;
				});
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * OTP login
	 * ------------------------------------------------------------------- */
	var otpForm = $('#netelec-otp-form');
	if (otpForm) {
		var phoneStep = $('[data-step="phone"]', otpForm);
		var codeStep = $('[data-step="code"]', otpForm);
		var phoneInput = $('#otp-phone', otpForm);
		var codeInput = $('#otp-code', otpForm);
		var messageEl = $('.otp-message', otpForm);
		var resendButton = $('.otp-resend', otpForm);
		var changeButton = $('.otp-change', otpForm);
		var countdownTimer = null;

		var setMessage = function (text, type) {
			messageEl.textContent = text;
			messageEl.className = 'otp-message' + (type ? ' is-' + type : '');
		};

		var startCountdown = function (seconds) {
			clearInterval(countdownTimer);
			var remaining = seconds;
			resendButton.disabled = true;
			resendButton.textContent = (i18n.resendIn || 'ارسال مجدد تا %s ثانیه').replace('%s', remaining);
			countdownTimer = setInterval(function () {
				remaining -= 1;
				if (remaining <= 0) {
					clearInterval(countdownTimer);
					resendButton.disabled = false;
					resendButton.textContent = 'ارسال مجدد کد';
					return;
				}
				resendButton.textContent = (i18n.resendIn || 'ارسال مجدد تا %s ثانیه').replace('%s', remaining);
			}, 1000);
		};

		var sendCode = function () {
			setMessage('در حال ارسال…');
			post('netelec_otp_send', { phone: phoneInput.value })
				.then(function (result) {
					setMessage(result.message || i18n.otpSent, 'success');
					phoneStep.hidden = true;
					codeStep.hidden = false;
					codeInput.focus();
					startCountdown(result.resend || 60);
				})
				.catch(function (error) {
					setMessage(error.message, 'error');
				});
		};

		otpForm.addEventListener('submit', function (event) {
			event.preventDefault();
			if (!codeStep.hidden) {
				setMessage('در حال بررسی…');
				post('netelec_otp_verify', { phone: phoneInput.value, code: codeInput.value })
					.then(function (result) {
						setMessage(result.message, 'success');
						window.location.href = result.redirect;
					})
					.catch(function (error) {
						setMessage(error.message, 'error');
					});
				return;
			}
			sendCode();
		});

		if (resendButton) {
			resendButton.addEventListener('click', sendCode);
		}

		if (changeButton) {
			changeButton.addEventListener('click', function () {
				codeStep.hidden = true;
				phoneStep.hidden = false;
				codeInput.value = '';
				setMessage('');
				phoneInput.focus();
			});
		}
	}

	/* ---------------------------------------------------------------------
	 * Contact form
	 * ------------------------------------------------------------------- */
	var contactForm = $('#netelec-contact-form');
	if (contactForm) {
		var contactMessage = $('.form-message', contactForm);

		contactForm.addEventListener('submit', function (event) {
			event.preventDefault();

			var formData = new FormData(contactForm);
			var payload = {};
			formData.forEach(function (value, key) {
				payload[key] = value;
			});

			var submitButton = $('button[type="submit"]', contactForm);
			submitButton.disabled = true;
			contactMessage.textContent = 'در حال ارسال…';
			contactMessage.className = 'form-message';

			post('netelec_contact', payload)
				.then(function (result) {
					contactMessage.textContent = result.message;
					contactMessage.className = 'form-message is-success';
					contactForm.reset();
				})
				.catch(function (error) {
					contactMessage.textContent = error.message;
					contactMessage.className = 'form-message is-error';
				})
				.finally(function () {
					submitButton.disabled = false;
				});
		});
	}

	/* ---------------------------------------------------------------------
	 * Sticky buy bar link
	 * ------------------------------------------------------------------- */
	var stickyBar = $('[data-pdp-sticky]');
	if (stickyBar) {
		var stickyLink = $('a', stickyBar);
		if (stickyLink) {
			stickyLink.addEventListener('click', function (event) {
				var target = $('#pdp-buy');
				if (target) {
					event.preventDefault();
					target.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
			});
		}
	}
})();
