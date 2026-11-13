/**
 * MOON — site chrome: theme switch, header, overlays, wishlist, quick view,
 * reveal animations, magnetic buttons and the newsletter form.
 *
 * No dependencies. Loaded on every page.
 */
(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var MOON = window.MOON || {};
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasHover = window.matchMedia('(hover: hover)').matches;

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
  }

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
   * Magnetic buttons
   * -------------------------------------------------------------------- */
  if (hasHover && !reduceMotion) {
    doc.querySelectorAll('.moon-magnetic').forEach(function (element) {
      element.addEventListener('mousemove', function (event) {
        var rect = element.getBoundingClientRect();
        var x = (event.clientX - rect.left - rect.width / 2) * 0.25;
        var y = (event.clientY - rect.top - rect.height / 2) * 0.35;
        element.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0)';
      });

      element.addEventListener('mouseleave', function () {
        element.style.transform = '';
      });
    });
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
