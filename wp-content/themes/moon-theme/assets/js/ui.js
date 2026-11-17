/**
 * MOON — interface behaviour added by the builders and shop options.
 *
 * Header hide-on-scroll, image sliders, offer countdowns, the sticky buy bar,
 * product comparison, the grid/list switch and "load more". Each block exits
 * immediately when its markup is absent, so a disabled feature costs nothing
 * beyond this one deferred request.
 */
(function () {
  'use strict';

  var doc = document;
  var MOON = window.MOON || {};
  var i18n = MOON.i18n || {};
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function faDigits(value) {
    return String(value).replace(/\d/g, function (digit) {
      return '۰۱۲۳۴۵۶۷۸۹'.charAt(Number(digit));
    });
  }

  function toast(message) {
    var node = doc.querySelector('[data-moon-toast]');

    if (!node || !message) {
      return;
    }

    node.textContent = message;
    node.classList.add('is-open');
    window.clearTimeout(toast.timer);
    toast.timer = window.setTimeout(function () {
      node.classList.remove('is-open');
    }, 3200);
  }

  /* ----------------------------------------------------------------------
   * Header: hide on scroll down, reveal on scroll up
   * -------------------------------------------------------------------- */
  var header = doc.querySelector('[data-moon-header]');

  if (header && doc.body.classList.contains('moon-header-hide-on-scroll')) {
    var lastY = window.scrollY;

    window.addEventListener(
      'scroll',
      function () {
        var y = window.scrollY;

        if (y > 160 && y > lastY) {
          header.classList.add('is-hidden');
        } else if (y < lastY - 4) {
          header.classList.remove('is-hidden');
        }

        lastY = y;
      },
      { passive: true }
    );
  }

  /* ----------------------------------------------------------------------
   * Slider
   * -------------------------------------------------------------------- */
  doc.querySelectorAll('[data-moon-slider]').forEach(function (slider) {
    var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-moon-slide]'));
    var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-moon-slide-to]'));

    if (slides.length < 2) {
      return;
    }

    var current = 0;
    var timer = null;

    function show(index) {
      current = (index + slides.length) % slides.length;

      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === current);
      });

      dots.forEach(function (dot, i) {
        dot.classList.toggle('is-active', i === current);
      });
    }

    function start() {
      if (reduce) {
        return;
      }

      stop();
      timer = window.setInterval(function () {
        show(current + 1);
      }, 6000);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        show(i);
        start();
      });
    });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);

    start();
  });

  /* ----------------------------------------------------------------------
   * Countdowns
   * -------------------------------------------------------------------- */
  doc.querySelectorAll('[data-moon-countdown]').forEach(function (node) {
    var end = Number(node.getAttribute('data-moon-countdown')) * 1000;

    if (!end) {
      return;
    }

    var fields = {
      days: node.querySelector('[data-moon-cd="days"]'),
      hours: node.querySelector('[data-moon-cd="hours"]'),
      minutes: node.querySelector('[data-moon-cd="minutes"]'),
      seconds: node.querySelector('[data-moon-cd="seconds"]')
    };

    function tick() {
      var left = end - Date.now();

      if (left <= 0) {
        node.innerHTML = '<span class="moon-countdown__label">' + (i18n.offerEnded || 'پیشنهاد به پایان رسید') + '</span>';
        return false;
      }

      var seconds = Math.floor(left / 1000);
      var values = {
        days: Math.floor(seconds / 86400),
        hours: Math.floor((seconds % 86400) / 3600),
        minutes: Math.floor((seconds % 3600) / 60),
        seconds: seconds % 60
      };

      Object.keys(fields).forEach(function (key) {
        if (fields[key]) {
          fields[key].textContent = faDigits(values[key]);
        }
      });

      return true;
    }

    if (tick()) {
      window.setInterval(tick, 1000);
    }
  });

  /* ----------------------------------------------------------------------
   * Sticky buy bar
   * -------------------------------------------------------------------- */
  var sticky = doc.querySelector('[data-moon-sticky-buy]');

  if (sticky) {
    var anchor = doc.querySelector('.moon-summary, .summary, #moon-buy');

    var onScroll = function () {
      var beyond = anchor ? anchor.getBoundingClientRect().bottom < 0 : window.scrollY > 600;
      var nearFooter = false;
      var footer = doc.querySelector('.moon-footer');

      if (footer) {
        nearFooter = footer.getBoundingClientRect().top < window.innerHeight - 40;
      }

      sticky.hidden = !(beyond && !nearFooter);
      sticky.classList.toggle('is-visible', beyond && !nearFooter);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var focusLink = sticky.querySelector('[data-moon-sticky-focus]');

    if (focusLink) {
      focusLink.addEventListener('click', function (event) {
        event.preventDefault();

        var target = doc.querySelector('.variations_form, .summary, #moon-buy');

        if (target) {
          target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
          var first = target.querySelector('select, input, button');

          if (first) {
            first.focus({ preventScroll: true });
          }
        }
      });
    }
  }

  /* ----------------------------------------------------------------------
   * Product comparison
   * -------------------------------------------------------------------- */
  var COMPARE_KEY = MOON.compareKey || 'moon-compare';
  var COMPARE_MAX = MOON.compareMax || 4;

  function compareList() {
    try {
      var raw = window.localStorage.getItem(COMPARE_KEY);
      var list = raw ? JSON.parse(raw) : [];

      return Array.isArray(list) ? list.map(String) : [];
    } catch (error) {
      return [];
    }
  }

  function compareSave(list) {
    try {
      window.localStorage.setItem(COMPARE_KEY, JSON.stringify(list));
    } catch (error) {
      return;
    }

    comparePaint();

    if (doc.body.classList.contains('logged-in')) {
      var body = new window.URLSearchParams();
      body.set('action', 'moon_compare_sync');
      body.set('nonce', MOON.nonce || '');
      body.set('ids', list.join(','));

      window.fetch(MOON.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).catch(function () {
        /* Mirroring is best effort; the local list stays authoritative. */
      });
    }
  }

  function comparePaint() {
    var list = compareList();

    doc.querySelectorAll('[data-moon-compare-add]').forEach(function (button) {
      var id = String(button.getAttribute('data-moon-compare-add'));
      button.setAttribute('aria-pressed', list.indexOf(id) !== -1 ? 'true' : 'false');
    });

    doc.querySelectorAll('[data-moon-compare-count]').forEach(function (badge) {
      badge.textContent = list.length ? faDigits(list.length) : '';
      badge.hidden = list.length === 0;
    });
  }

  doc.addEventListener('click', function (event) {
    var button = event.target.closest('[data-moon-compare-add]');

    if (!button) {
      return;
    }

    event.preventDefault();

    var id = String(button.getAttribute('data-moon-compare-add'));
    var list = compareList();
    var index = list.indexOf(id);

    if (index !== -1) {
      list.splice(index, 1);
      compareSave(list);
      toast(i18n.compareRemoved || 'از مقایسه حذف شد.');
      return;
    }

    if (list.length >= COMPARE_MAX) {
      toast(i18n.compareFull || 'حداکثر ۴ محصول را می‌توانید مقایسه کنید.');
      return;
    }

    list.push(id);
    compareSave(list);
    toast(i18n.compareAdded || 'به مقایسه اضافه شد.');
  });

  /* Comparison page ------------------------------------------------------- */
  var comparePage = doc.querySelector('[data-moon-compare-page]');

  if (comparePage) {
    var output = comparePage.querySelector('[data-moon-compare-output]');
    var hint = comparePage.querySelector('[data-moon-compare-hint]');
    var saved = (comparePage.getAttribute('data-saved') || '').split(',').filter(Boolean);
    var merged = compareList();

    saved.forEach(function (id) {
      if (merged.indexOf(String(id)) === -1 && merged.length < COMPARE_MAX) {
        merged.push(String(id));
      }
    });

    if (merged.join(',') !== compareList().join(',')) {
      compareSave(merged);
    }

    function renderCompare() {
      var list = compareList();

      if (!list.length) {
        if (output) {
          output.innerHTML = '';
        }

        if (hint) {
          hint.hidden = false;
        }

        return;
      }

      var body = new window.URLSearchParams();
      body.set('action', 'moon_compare_render');
      body.set('nonce', MOON.nonce || '');
      body.set('ids', list.join(','));

      window.fetch(MOON.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (payload) {
          if (!payload || !payload.success || !output) {
            return;
          }

          output.innerHTML = payload.data.html;

          if (hint) {
            hint.hidden = payload.data.count > 0;
          }
        })
        .catch(function () {
          /* The server-rendered table stays as the fallback. */
        });
    }

    renderCompare();

    comparePage.addEventListener('click', function (event) {
      var remove = event.target.closest('[data-moon-compare-remove]');

      if (!remove) {
        return;
      }

      event.preventDefault();

      var list = compareList().filter(function (id) {
        return id !== String(remove.getAttribute('data-moon-compare-remove'));
      });

      compareSave(list);
      renderCompare();
    });
  }

  comparePaint();

  /* ----------------------------------------------------------------------
   * Grid / list switch
   * -------------------------------------------------------------------- */
  var shop = doc.querySelector('.moon-shop__layout');

  if (shop) {
    var shopRoot = shop.closest('.moon-shop') || doc.body;
    var viewKey = 'moon-shop-view';
    var buttons = doc.querySelectorAll('[data-moon-view]');

    function applyView(view) {
      shopRoot.classList.toggle('moon-shop--list', view === 'list');
      shopRoot.classList.toggle('moon-shop--grid', view !== 'list');

      buttons.forEach(function (button) {
        button.setAttribute('aria-pressed', button.getAttribute('data-moon-view') === view ? 'true' : 'false');
      });
    }

    if (buttons.length) {
      var stored = window.localStorage.getItem(viewKey);
      applyView(stored === 'list' || stored === 'grid' ? stored : MOON.shopView === 'list' ? 'list' : 'grid');

      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          var view = button.getAttribute('data-moon-view');

          window.localStorage.setItem(viewKey, view);
          applyView(view);
        });
      });
    }
  }

  /* ----------------------------------------------------------------------
   * Load more
   * -------------------------------------------------------------------- */
  var loadMore = doc.querySelector('[data-moon-loadmore]');

  if (loadMore && shop) {
    loadMore.addEventListener('click', function () {
      var next = loadMore.getAttribute('data-next');

      if (!next) {
        return;
      }

      var grid = shop.querySelector('[data-moon-grid]');

      loadMore.disabled = true;

      window.fetch(next, { credentials: 'same-origin' })
        .then(function (response) {
          return response.text();
        })
        .then(function (html) {
          var parsed = new window.DOMParser().parseFromString(html, 'text/html');
          var cards = parsed.querySelectorAll('[data-moon-grid] > li, [data-moon-grid] > .product');
          var nextLink = parsed.querySelector('[data-moon-loadmore]');

          cards.forEach(function (card) {
            grid.appendChild(card);
          });

          if (nextLink && nextLink.getAttribute('data-next') && nextLink.getAttribute('data-next') !== next) {
            loadMore.setAttribute('data-next', nextLink.getAttribute('data-next'));
            loadMore.disabled = false;
          } else {
            loadMore.remove();
          }

          comparePaint();
        })
        .catch(function () {
          loadMore.disabled = false;
        });
    });
  }
})();
