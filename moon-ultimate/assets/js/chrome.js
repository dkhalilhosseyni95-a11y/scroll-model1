/**
 * MOON — site chrome: theme switch, header, overlays, wishlist, quick view,
 * reveal animations, image protection and the newsletter form.
 *
 * No dependencies. Loaded on every page.
 */
/**
 * Retry AJAX calls once with a fresh nonce when a cached page held an expired one.
 */
(function () {
  'use strict';

  var MOON = window.MOON || {};
  var nativeFetch = window.fetch;

  if (!nativeFetch || !MOON.ajaxUrl) {
    return;
  }

  var nonceMap = {
    moon_otp_send: 'moon_login',
    moon_otp_verify: 'moon_login',
    moon_phone_register: 'moon_login',
    moon_subscribe: 'moon_subscribe',
    moon_sync_account_state: 'moon_account_sync'
  };
  var pending = null;

  function freshNonces() {
    if (!pending) {
      pending = nativeFetch(MOON.ajaxUrl + '?action=moon_fresh_nonce&_=' + Date.now(), { credentials: 'same-origin', cache: 'no-store' })
        .then(function (r) { return r.json(); })
        .then(function (r) { pending = null; return r && r.success ? r.data : null; })
        .catch(function () { pending = null; return null; });
    }

    return pending;
  }

  function isMoonAjax(url) {
    return typeof url === 'string' && (url.indexOf('admin-ajax.php') > -1 || url.indexOf('wc-ajax=moon_') > -1);
  }

  window.fetch = function (input, init) {
    var url = typeof input === 'string' ? input : (input && input.url) || '';

    if (!isMoonAjax(url) || !init || String(init.method || 'GET').toUpperCase() !== 'POST') {
      return nativeFetch.apply(this, arguments);
    }

    var self = this;
    var args = arguments;

    return nativeFetch.apply(self, args).then(function (response) {
      if (response.status !== 403) {
        return response;
      }

      return freshNonces().then(function (nonces) {
        if (!nonces || !init.body) {
          return response;
        }

        var params = new window.URLSearchParams(typeof init.body === 'string' ? init.body : init.body.toString());
        var key = nonceMap[params.get('action')] || 'moon_shop';

        if (!params.has('nonce') || !nonces[key]) {
          return response;
        }

        params.set('nonce', nonces[key]);

        if (key === 'moon_shop') {
          MOON.nonce = nonces[key];
        }

        var retry = {};

        Object.keys(init).forEach(function (name) { retry[name] = init[name]; });
        retry.body = params.toString();

        return nativeFetch.call(self, input, retry);
      });
    });
  };
})();

(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var MOON = window.MOON || {};
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ----------------------------------------------------------------------
   * Theme (dark by default, matching the source design)
   * -------------------------------------------------------------------- */
  var THEME_KEY = 'moon-theme';

  try {
    if (window.localStorage.getItem(THEME_KEY) === 'light') {
      root.classList.remove('moon-dark');
      root.classList.add('moon-light');
    }
  } catch (error) {
    /* storage unavailable */
  }

  var themeSwitch = doc.querySelector('[data-moon-theme]');

  if (themeSwitch) {
    themeSwitch.setAttribute('aria-pressed', root.classList.contains('moon-light') ? 'true' : 'false');
    themeSwitch.addEventListener('click', function () {
      var light = !root.classList.contains('moon-light');
      root.classList.toggle('moon-light', light);
      root.classList.toggle('moon-dark', !light);
      themeSwitch.setAttribute('aria-pressed', light ? 'true' : 'false');

      try {
        window.localStorage.setItem(THEME_KEY, light ? 'light' : 'dark');
      } catch (error) {
        /* ignore */
      }
    });
  }

  /* ----------------------------------------------------------------------
   * Header shrink on scroll
   * -------------------------------------------------------------------- */
  var header = doc.querySelector('[data-moon-header]');

  if (header) {
    var headerState = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 40);
    };

    headerState();
    window.addEventListener('scroll', headerState, { passive: true });
  }

  /* ----------------------------------------------------------------------
   * Overlay panels (mobile menu, search, wishlist)
   * -------------------------------------------------------------------- */
  var panels = Array.prototype.slice.call(doc.querySelectorAll('[data-moon-panel]'));
  var lastFocus = null;

  function closePanels() {
    panels.forEach(function (panel) {
      panel.classList.remove('is-open');
      panel.setAttribute('aria-hidden', 'true');
    });

    doc.body.classList.remove('moon-locked');

    doc.querySelectorAll('[data-moon-open]').forEach(function (button) {
      button.setAttribute('aria-expanded', 'false');
    });

    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
      lastFocus = null;
    }
  }

  function openPanel(name, opener) {
    var panel = doc.querySelector('[data-moon-panel="' + name + '"]');

    if (!panel) {
      return;
    }

    lastFocus = opener || doc.activeElement;
    panel.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    doc.body.classList.add('moon-locked');

    if (opener) {
      opener.setAttribute('aria-expanded', 'true');
    }

    var focusTarget = panel.querySelector('input, button, a');

    if (focusTarget) {
      window.setTimeout(function () {
        focusTarget.focus();
      }, 120);
    }

    if (name === 'wishlist') {
      renderWishlist();
    }

    if (name === 'cart') {
      refreshCart();
    }
  }

  /* ----------------------------------------------------------------------
   * Quick cart drawer
   * -------------------------------------------------------------------- */
  var wcEndpoint = MOON.wcAjax || MOON.homeUrl || '/';

  function applyFragments(fragments) {
    if (!fragments) {
      return;
    }

    Object.keys(fragments).forEach(function (selector) {
      var node = doc.querySelector(selector);

      if (node && typeof fragments[selector] === 'string') {
        node.outerHTML = fragments[selector];
      }
    });
  }

  function refreshCart() {
    window.fetch(wcEndpoint + '?wc-ajax=get_refreshed_fragments', {
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (result) {
        if (result && result.fragments) {
          applyFragments(result.fragments);
        }
      })
      .catch(function () {
        /* keep the server-rendered contents */
      });
  }

  function addToCart(productId) {
    var body = new window.URLSearchParams();
    body.set('product_id', productId);
    body.set('quantity', 1);

    return window.fetch(wcEndpoint + '?wc-ajax=add_to_cart', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    });
  }

  doc.addEventListener('click', function (event) {
    var add = event.target.closest('[data-moon-add]');

    if (!add || add.disabled) {
      return;
    }

    event.preventDefault();
    add.disabled = true;
    add.textContent = 'در حال افزودن…';

    addToCart(add.getAttribute('data-moon-add'))
      .then(function (result) {
        if (!result || result.error) {
          throw new Error('add-to-cart failed');
        }

        applyFragments(result.fragments);
        doc.dispatchEvent(new CustomEvent('moon:close-quickview'));
        openPanel('cart');
      })
      .catch(function () {
        add.disabled = false;
        add.textContent = 'افزودن به سبد';
      });
  });

  /* ----------------------------------------------------------------------
   * Instant cart: optimistic quantity / remove, reconciled in the background
   * -------------------------------------------------------------------- */
  var cartTimers = {};

  function persistLine(key, qty) {
    window.clearTimeout(cartTimers[key]);
    cartTimers[key] = window.setTimeout(function () {
      var body = new window.URLSearchParams();
      body.set('key', key);
      body.set('qty', qty);
      body.set('nonce', MOON.nonce);

      window.fetch(wcEndpoint + '?wc-ajax=moon_cart_qty', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      })
        .then(function (response) { return response.json(); })
        .then(function (result) {
          if (result && result.success && result.data) {
            applyFragments(result.data.fragments);
          } else {
            refreshCart();
          }
        })
        .catch(refreshCart);
    }, 180);
  }

  function bumpBadge(delta) {
    doc.querySelectorAll('.moon-cart-count').forEach(function (badge) {
      var n = Math.max(0, (parseInt(badge.getAttribute('data-count'), 10) || 0) + delta);
      badge.setAttribute('data-count', n);
      badge.textContent = n ? n.toLocaleString('fa-IR') : '';
      badge.classList.toggle('has-count', n > 0);
    });
  }

  doc.addEventListener('click', function (event) {
    var step = event.target.closest('.moon-cart-drawer [data-moon-qty]');
    var remove = event.target.closest('.moon-cart-drawer .remove_from_cart_button');
    var line;
    var key;
    var qty;

    if (step) {
      event.preventDefault();
      line = step.closest('[data-moon-line]');
      key = line.getAttribute('data-moon-line');
      qty = Math.max(0, (parseInt(line.getAttribute('data-qty'), 10) || 1) + parseInt(step.getAttribute('data-moon-qty'), 10));
    } else if (remove) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var row = remove.closest('.woocommerce-mini-cart-item');
      line = row ? row.querySelector('[data-moon-line]') : null;
      key = line ? line.getAttribute('data-moon-line') : remove.getAttribute('data-cart_item_key');
      qty = 0;
    } else {
      return;
    }

    if (!key) {
      return;
    }

    var item = (line || step || remove).closest('.woocommerce-mini-cart-item');
    var before = line ? parseInt(line.getAttribute('data-qty'), 10) || 1 : 1;

    if (0 === qty) {
      if (item) { item.style.display = 'none'; }
    } else if (line) {
      line.setAttribute('data-qty', qty);
      line.querySelector('.moon-mq__n').textContent = qty.toLocaleString('fa-IR');
    }

    bumpBadge(qty - before);
    persistLine(key, qty);
  }, true);

  /* WooCommerce's own add-to-cart script (product page, archive) fires this. */
  function bindWooEvents() {
    if (!window.jQuery) {
      return;
    }

    window.jQuery(doc.body).on('added_to_cart removed_from_cart', function (event, fragments) {
      applyFragments(fragments);

      if ('added_to_cart' === event.type && fragments) {
        openPanel('cart');
      }
    });
  }

  if ('complete' === doc.readyState) {
    bindWooEvents();
  } else {
    window.addEventListener('load', bindWooEvents);
  }

  doc.querySelectorAll('[data-moon-open]').forEach(function (button) {
    button.addEventListener('click', function (event) {
      /* The cart trigger is a real link so it still works without JS. */
      if ('A' === button.tagName) {
        event.preventDefault();
      }

      openPanel(button.getAttribute('data-moon-open'), button);
    });
  });

  doc.querySelectorAll('[data-moon-close]').forEach(function (button) {
    button.addEventListener('click', closePanels);
  });

  panels.forEach(function (panel) {
    panel.addEventListener('click', function (event) {
      if (event.target === panel) {
        closePanels();
      }
    });
  });

  doc.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closePanels();
    }
  });

  /* ----------------------------------------------------------------------
   * Wishlist (stored in the visitor's browser)
   * -------------------------------------------------------------------- */
  var WISH_KEY = MOON.wishlistKey || 'moon-wishlist';
  var wishCount = doc.querySelector('[data-moon-wish-count]');

  function readWishlist() {
    try {
      var raw = window.localStorage.getItem(WISH_KEY);
      var list = raw ? JSON.parse(raw) : [];
      return Array.isArray(list) ? list : [];
    } catch (error) {
      return [];
    }
  }

  function writeWishlist(list) {
    try {
      window.localStorage.setItem(WISH_KEY, JSON.stringify(list));
    } catch (error) {
      /* ignore */
    }

    syncWishlist();

    if (window.moonSyncWishlist) {
      window.moonSyncWishlist(list);
    }
  }

  window.addEventListener('moon:wishlist-synced', function () {
    syncWishlist();
  });

  function syncWishlist() {
    var list = readWishlist();

    if (wishCount) {
      wishCount.textContent = list.length ? String(list.length) : '';
      wishCount.classList.toggle('has-count', list.length > 0);
      wishCount.hidden = list.length === 0;
    }

    doc.querySelectorAll('[data-moon-like]').forEach(function (button) {
      var saved = list.some(function (item) {
        return String(item.id) === button.getAttribute('data-id');
      });

      button.classList.toggle('is-liked', saved);
      button.setAttribute('aria-pressed', saved ? 'true' : 'false');
    });

    renderWishlist();
  }

  function renderWishlist() {
    var container = doc.querySelector('[data-moon-wishlist-list]');

    if (!container) {
      return;
    }

    var list = readWishlist();
    container.innerHTML = '';

    if (!list.length) {
      var empty = doc.createElement('li');
      empty.className = 'moon-wishlist__empty';
      empty.textContent = (MOON.i18n && MOON.i18n.empty) || 'هنوز محصولی را نشان نکرده‌اید.';
      container.appendChild(empty);
      return;
    }

    list.forEach(function (item) {
      var row = doc.createElement('li');
      row.className = 'moon-wishlist__item';

      var image = doc.createElement('img');
      image.src = item.image || '';
      image.alt = '';
      image.width = 64;
      image.height = 80;

      var link = doc.createElement('a');
      link.href = item.url || '#';
      link.className = 'moon-wishlist__name';
      link.textContent = item.name || '';

      row.appendChild(image);
      row.appendChild(link);
      container.appendChild(row);
    });
  }

  doc.addEventListener('click', function (event) {
    var button = event.target.closest('[data-moon-like]');

    if (!button) {
      return;
    }

    event.preventDefault();

    var id = button.getAttribute('data-id');
    var list = readWishlist();
    var index = list.findIndex(function (item) {
      return String(item.id) === id;
    });

    if (index > -1) {
      list.splice(index, 1);
    } else {
      list.push({
        id: id,
        name: button.getAttribute('data-name') || '',
        url: button.getAttribute('data-url') || '',
        image: button.getAttribute('data-image') || ''
      });
    }

    writeWishlist(list);
  });

  syncWishlist();


  /* ----------------------------------------------------------------------
   * Shared helpers for page scripts
   * -------------------------------------------------------------------- */
  window.Moon = {
    openPanel: openPanel,
    closePanels: closePanels,
    applyFragments: applyFragments,
    refreshCart: refreshCart,
    addToCart: addToCart,
    endpoint: wcEndpoint
  };

  /* ----------------------------------------------------------------------
   * Scroll reveal
   * -------------------------------------------------------------------- */
  var revealItems = Array.prototype.slice.call(doc.querySelectorAll('.moon-reveal'));

  if (!revealItems.length) {
    /* nothing to do */
  } else if (reduceMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach(function (item) {
      item.classList.add('is-visible');
    });
  } else {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { rootMargin: '-80px 0px' });

    revealItems.forEach(function (item) {
      revealObserver.observe(item);
    });
  }

  /* ----------------------------------------------------------------------
   * Image protection
   *
   * Discourages casual saving, dragging and long-press downloads. A browser
   * must receive the image bytes to display it, so this is a deterrent, not
   * a guarantee against a determined visitor.
   * -------------------------------------------------------------------- */
  var protectImage = function (image) {
    if (!image || image.getAttribute('data-moon-guarded') === '1') {
      return;
    }

    image.setAttribute('data-moon-guarded', '1');
    image.setAttribute('draggable', 'false');
    image.addEventListener('dragstart', function (event) {
      event.preventDefault();
    });
  };

  var protectImages = function (scope) {
    Array.prototype.forEach.call((scope || doc).querySelectorAll('img'), protectImage);
  };

  protectImages();

  doc.addEventListener('contextmenu', function (event) {
    var target = event.target;

    if (target && target.closest && target.closest('img')) {
      event.preventDefault();
    }
  });

  // Images inserted later by AJAX (quick view, cart drawer, load more).
  if ('MutationObserver' in window) {
    new MutationObserver(function (records) {
      records.forEach(function (record) {
        Array.prototype.forEach.call(record.addedNodes, function (node) {
          if (!node || node.nodeType !== 1) {
            return;
          }

          if (node.tagName === 'IMG') {
            protectImage(node);
          } else if (node.querySelectorAll) {
            protectImages(node);
          }
        });
      });
    }).observe(doc.body, { childList: true, subtree: true });
  }

  /* ----------------------------------------------------------------------
   * Newsletter
   * -------------------------------------------------------------------- */
  var newsletter = doc.querySelector('[data-moon-newsletter]');

  if (newsletter) {
    newsletter.addEventListener('submit', function (event) {
      event.preventDefault();

      var emailField = newsletter.querySelector('input[type="email"]');
      var nonceField = newsletter.querySelector('input[name="nonce"]');
      var note = doc.querySelector('[data-moon-newsletter-note]');
      var submit = newsletter.querySelector('button[type="submit"]');

      if (!emailField || !emailField.value) {
        return;
      }

      if (submit) {
        submit.disabled = true;
      }

      var body = new window.URLSearchParams();
      body.set('action', 'moon_subscribe');
      body.set('email', emailField.value);
      body.set('nonce', nonceField ? nonceField.value : '');

      window.fetch(MOON.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          if (note) {
            note.hidden = false;
            note.textContent = (result && result.data && result.data.message) || '';
          }

          if (result && result.success) {
            emailField.value = '';
          }
        })
        .catch(function () {
          if (note) {
            note.hidden = false;
            note.textContent = 'ارسال نشد؛ دوباره تلاش کنید.';
          }
        })
        .then(function () {
          if (submit) {
            submit.disabled = false;
          }
        });
    });
  }
})();
