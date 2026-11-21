/**
 * MOON — shop interactions.
 *
 * Quick view (AJAX), AJAX cart, AJAX filters, attribute swatches, card colour
 * switching, quantity steppers, the size guide dialog and "add the whole look".
 *
 * Loaded after chrome.js, which exposes the cart helpers on window.Moon.
 */
(function () {
  'use strict';

  var doc = document;
  var MOON = window.MOON || {};
  var api = window.Moon || {};
  var ajaxUrl = MOON.ajaxUrl || '/wp-admin/admin-ajax.php';
  var nonce = MOON.nonce || '';
  var i18n = MOON.i18n || {};

  function post(data) {
    var body = new window.URLSearchParams();

    Object.keys(data).forEach(function (key) {
      var value = data[key];

      if (Array.isArray(value)) {
        var listKey = key.slice(-2) === '[]' ? key : key + '[]';

        value.forEach(function (item) {
          body.append(listKey, item);
        });
      } else {
        body.append(key, value);
      }
    });

    if (nonce) {
      body.append('nonce', nonce);
    }

    return window.fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    });
  }

  /* ----------------------------------------------------------------------
   * Toast
   * -------------------------------------------------------------------- */
  var toastNode = doc.querySelector('[data-moon-toast]');
  var toastTimer = null;

  function toast(message) {
    if (!toastNode || !message) {
      return;
    }

    toastNode.textContent = message;
    toastNode.classList.add('is-open');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () {
      toastNode.classList.remove('is-open');
    }, 3200);
  }

  function openDrawer() {
    if (api.openPanel) {
      api.openPanel('cart', doc.querySelector('[data-moon-open="cart"]'));
    }
  }

  function fragments(payload) {
    if (!payload) {
      return;
    }

    if (api.applyFragments) {
      api.applyFragments(payload.fragments);
    }

    if (MOON.cartUrl && payload.cart_hash === undefined && api.refreshCart) {
      /* fragments already applied */
    }
  }

  /* ----------------------------------------------------------------------
   * Quick view
   * -------------------------------------------------------------------- */
  var quickView = doc.querySelector('#moon-quick-view');
  var quickDialog = quickView ? quickView.querySelector('[data-moon-qv-dialog]') : null;
  var quickOpener = null;

  function initQuickView(scope) {
    // Product gallery inside the dialog.
    var images = Array.prototype.slice.call(scope.querySelectorAll('[data-moon-qv-img]'));

    scope.querySelectorAll('[data-moon-qv-thumb]').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var index = thumb.getAttribute('data-moon-qv-thumb');

        images.forEach(function (image) {
          image.classList.toggle('is-active', image.getAttribute('data-moon-qv-img') === index);
        });

        scope.querySelectorAll('[data-moon-qv-thumb]').forEach(function (item) {
          item.classList.toggle('is-active', item === thumb);
        });
      });
    });

    // WooCommerce needs to attach its variations script to the new form.
    if (window.jQuery) {
      var $ = window.jQuery;
      var form = $('.variations_form', scope);

      if (form.length && typeof form.wc_variation_form === 'function') {
        form.each(function () {
          $(this).wc_variation_form();
        });
      }

      form.on('found_variation', function (event, variation) {
        if (variation && variation.image && variation.image.src && images.length) {
          var main = scope.querySelector('[data-moon-qv-img].is-active') || images[0];

          if (main) {
            main.src = variation.image.src;
          }
        }
      });

      form.on('show_variation reset_data hide_variation', function () {
        syncSwatches(scope);
      });

      $(document.body).trigger('wc_variation_form');
    }
  }

  function openQuickView(productId, opener) {
    if (!quickView || !quickDialog) {
      return;
    }

    quickOpener = opener || doc.activeElement;
    quickView.classList.add('is-loading');
    quickView.setAttribute('aria-hidden', 'false');
    quickView.classList.add('is-open');
    doc.body.classList.add('moon-locked');
    quickDialog.innerHTML = '<div class="moon-qv__loader" aria-hidden="true"></div>';

    post({ action: 'moon_quick_view', product_id: productId })
      .then(function (response) {
        quickView.classList.remove('is-loading');

        if (!response || !response.success || !response.data || !response.data.html) {
          quickDialog.innerHTML = '<p class="moon-qv__error">' + (i18n.qvError || 'محصول در دسترس نیست.') + '</p>';
          return;
        }

        quickDialog.innerHTML = response.data.html;
        initQuickView(quickDialog);

        var close = quickDialog.querySelector('[data-moon-close]');

        if (close) {
          close.focus();
        }
      })
      .catch(function () {
        quickView.classList.remove('is-loading');
        quickDialog.innerHTML = '<p class="moon-qv__error">' + (i18n.qvError || 'محصول در دسترس نیست.') + '</p>';
      });
  }

  function closeQuickView() {
    if (!quickView) {
      return;
    }

    quickView.classList.remove('is-open');
    quickView.setAttribute('aria-hidden', 'true');
    doc.body.classList.remove('moon-locked');

    if (quickOpener && typeof quickOpener.focus === 'function') {
      quickOpener.focus();
    }
  }

  doc.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-moon-quick]');

    if (!trigger) {
      return;
    }

    event.preventDefault();
    openQuickView(trigger.getAttribute('data-moon-quick'), trigger);
  });

  if (quickView) {
    quickView.addEventListener('click', function (event) {
      if (event.target === quickView || event.target.closest('[data-moon-close]')) {
        closeQuickView();
      }
    });
  }

  doc.addEventListener('moon:close-quickview', closeQuickView);

  doc.addEventListener('keydown', function (event) {
    if ('Escape' === event.key) {
      closeQuickView();
    }
  });

  /* ----------------------------------------------------------------------
   * Attribute swatches (colour and size select replacement)
   * -------------------------------------------------------------------- */
  function syncSwatches(scope) {
    scope.querySelectorAll('[data-moon-swatches]').forEach(function (group) {
      var wrapper = group.parentNode;
      var select = wrapper ? wrapper.querySelector('select') : null;

      if (!select) {
        return;
      }

      var disabled = Array.prototype.map.call(select.options, function (option) {
        return option.disabled ? option.value : null;
      });

      group.querySelectorAll('.moon-swatch').forEach(function (swatch) {
        var value = swatch.getAttribute('data-value');
        var active = value === select.value;
        var off = disabled.indexOf(value) !== -1;

        swatch.classList.toggle('is-active', active && !off);
        swatch.classList.toggle('is-disabled', off);
        swatch.setAttribute('aria-checked', active ? 'true' : 'false');
        swatch.disabled = off;
      });
    });
  }

  doc.addEventListener('click', function (event) {
    var swatch = event.target.closest('.moon-swatch');

    if (!swatch || swatch.disabled) {
      return;
    }

    var group = swatch.closest('[data-moon-swatches]');
    var select = group ? group.parentNode.querySelector('select') : null;

    if (!group || !select) {
      return;
    }

    select.value = swatch.getAttribute('data-value');
    select.dispatchEvent(new Event('change', { bubbles: true }));
    select.dispatchEvent(new Event('input', { bubbles: true }));

    if (window.jQuery) {
      window.jQuery(select).trigger('change');
    }

    syncSwatches(group.closest('form') || doc);
  });

  doc.addEventListener('change', function (event) {
    var select = event.target;

    if (!select.matches || !select.matches('.moon-swatches__native select')) {
      return;
    }

    syncSwatches(select.closest('form') || doc);
  });

  if (window.jQuery) {
    window.jQuery(doc.body).on('woocommerce_update_variation_values', function (event) {
      syncSwatches(event.target);
    });
  }

  syncSwatches(doc);

  /* ----------------------------------------------------------------------
   * AJAX add to cart (single product, quick view, variation forms)
   * -------------------------------------------------------------------- */
  doc.addEventListener('submit', function (event) {
    var form = event.target;

    if (!form.matches || !form.matches('form.cart')) {
      return;
    }

    if (form.hasAttribute('data-moon-native')) {
      return;
    }

    var variationForm = form.querySelector('.variations_form, .variations');
    var variationId = form.querySelector('input[name="variation_id"]');

    if (variationForm && variationId && (!variationId.value || '0' === variationId.value)) {
      /* Let WooCommerce show "please select options". */
      return;
    }

    var productId = form.querySelector('input[name="add-to-cart"], input[name="product_id"], button[name="add-to-cart"]');

    if (!productId) {
      return;
    }

    event.preventDefault();

    var button = form.querySelector('button[type="submit"], .single_add_to_cart_button');
    var label = button ? button.innerHTML : '';

    if (button) {
      button.disabled = true;
      button.classList.add('is-loading');
      button.innerHTML = i18n.adding || 'در حال افزودن…';
    }

    var data = new window.URLSearchParams(new window.FormData(form));
    // WooCommerce 10.6+ resolves the variation from product_id alone, so a
    // chosen variation must be sent there instead of the parent product.
    var chosen = variationId && variationId.value && '0' !== variationId.value ? variationId.value : '';
    data.set('product_id', chosen || productId.value || variationId.value);

    window.fetch((MOON.wcAjax || '/') + '?wc-ajax=add_to_cart', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: data.toString()
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (result) {
        if (!result || result.error || !result.fragments) {
          throw new Error('add-to-cart');
        }

        if (api.applyFragments) {
          api.applyFragments(result.fragments);
        }

        toast(i18n.added || 'به سبد خرید اضافه شد.');
        closeQuickView();
        openDrawer();
      })
      .catch(function () {
        toast(i18n.addError || 'افزودن به سبد انجام نشد.');
      })
      .then(function () {
        if (button) {
          button.disabled = false;
          button.classList.remove('is-loading');
          button.innerHTML = label;
        }

        syncSwatches(doc);
      });
  });

  /* ----------------------------------------------------------------------
   * Shop filters
   * -------------------------------------------------------------------- */
  var filterForm = doc.querySelector('[data-moon-filters]');
  var grid = doc.querySelector('[data-moon-grid]');
  var countNode = doc.querySelector('[data-moon-count]');
  var pagerNode = doc.querySelector('[data-moon-pager]');
  var sortSelect = doc.querySelector('[data-moon-sort]');
  var currentPage = 1;

  function activeColors() {
    var input = filterForm ? filterForm.querySelector('[data-moon-colors-input]') : null;

    return input && input.value ? input.value.split(',').filter(Boolean) : [];
  }

  function filterPayload(page) {
    var payload = {
      action: 'moon_filter_products',
      paged: page || 1
    };

    if (filterForm) {
      var data = new window.FormData(filterForm);

      ['moon_cat[]', 'size[]', 'brand[]'].forEach(function (key) {
        if (data.has(key)) {
          payload[key] = data.getAll(key).filter(Boolean);
        }
      });

      payload.min_price = data.get('min_price') || '';
      payload.max_price = data.get('max_price') || '';
      payload.in_stock = data.get('in_stock') || '';
      payload.sale = data.get('sale') || '';
      payload.featured = data.get('featured') || '';
      payload.color = activeColors().join(',');
      payload.archive = filterForm.getAttribute('data-archive') || 'shop';
    }

    if (sortSelect) {
      payload.orderby = sortSelect.value;
    }

    return payload;
  }

  function updateUrl(page) {
    if (!window.history || !window.history.replaceState) {
      return;
    }

    var params = new window.URLSearchParams();
    var colors = activeColors();

    if (colors.length) {
      params.set('color', colors.join(','));
    }

    if (filterForm) {
      var data = new window.FormData(filterForm);

      data.getAll('moon_cat[]').filter(Boolean).forEach(function (value) {
        params.append('moon_cat[]', value);
      });

      data.getAll('size[]').filter(Boolean).forEach(function (value) {
        params.append('size[]', value);
      });

      data.getAll('brand[]').filter(Boolean).forEach(function (value) {
        params.append('brand[]', value);
      });

      ['in_stock', 'sale', 'featured'].forEach(function (key) {
        if (data.get(key)) {
          params.set(key, '1');
        }
      });

      ['min_price', 'max_price'].forEach(function (key) {
        var value = data.get(key);

        if (value) {
          params.set(key, value);
        }
      });
    }

    if (sortSelect && sortSelect.value && 'menu_order' !== sortSelect.value) {
      params.set('orderby', sortSelect.value);
    }

    if (page > 1) {
      params.set('paged', page);
    }

    var query = params.toString();

    window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : ''));
  }

  function applyFilters(page) {
    if (!grid) {
      return;
    }

    currentPage = page || 1;
    grid.classList.add('is-loading');

    post(filterPayload(currentPage))
      .then(function (response) {
        grid.classList.remove('is-loading');

        if (!response || !response.success) {
          return;
        }

        grid.innerHTML = response.data.html;

        if (countNode) {
          countNode.textContent = response.data.count;
        }

        if (pagerNode) {
          pagerNode.innerHTML = response.data.pager || '';
        }

        updateUrl(currentPage);

        if (filterForm && doc.querySelector('[data-moon-filters-open]') && window.innerWidth < 1024) {
          closeFilters();
        }
      })
      .catch(function () {
        grid.classList.remove('is-loading');
      });
  }

  if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
      event.preventDefault();
      applyFilters(1);
    });

    filterForm.querySelectorAll('input[type="checkbox"], input[type="number"]').forEach(function (input) {
      input.addEventListener('change', function () {
        input.closest('.moon-size-chip') && input.closest('.moon-size-chip').classList.toggle('is-active', input.checked);
        applyFilters(1);
      });
    });

    filterForm.querySelectorAll('[data-moon-color]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var value = chip.getAttribute('data-moon-color');
        var colors = activeColors();
        var index = colors.indexOf(value);

        if (index === -1) {
          colors.push(value);
        } else {
          colors.splice(index, 1);
        }

        var input = filterForm.querySelector('[data-moon-colors-input]');

        if (input) {
          input.value = colors.join(',');
        }

        chip.classList.toggle('is-active', index === -1);
        chip.setAttribute('aria-pressed', index === -1 ? 'true' : 'false');
        applyFilters(1);
      });
    });
  }

  doc.addEventListener('click', function (event) {
    var reset = event.target.closest('[data-moon-filter-reset]');

    if (reset && filterForm) {
      event.preventDefault();
      filterForm.reset();
      filterForm.querySelectorAll('.moon-color-chip').forEach(function (chip) {
        chip.classList.remove('is-active');
        chip.setAttribute('aria-pressed', 'false');
      });
      filterForm.querySelectorAll('.moon-size-chip').forEach(function (chip) {
        chip.classList.remove('is-active');
      });
      var input = filterForm.querySelector('[data-moon-colors-input]');

      if (input) {
        input.value = '';
      }

      applyFilters(1);
      return;
    }

    var pageButton = event.target.closest('[data-moon-page]');

    if (pageButton) {
      event.preventDefault();
      applyFilters(parseInt(pageButton.getAttribute('data-moon-page'), 10) || 1);
      if (grid) {
        grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      return;
    }

    var filtersOpen = event.target.closest('[data-moon-filters-open]');

    if (filtersOpen && filterForm) {
      filterForm.classList.add('is-open');
      var backdrop = doc.querySelector('.moon-shop__backdrop');

      if (backdrop) {
        backdrop.hidden = false;
      }

      doc.body.classList.add('moon-locked');
      return;
    }

    var filtersClose = event.target.closest('[data-moon-filters-close], [data-moon-filters] .moon-filters__close');

    if (filtersClose) {
      closeFilters();
    }
  });

  function closeFilters() {
    if (!filterForm) {
      return;
    }

    filterForm.classList.remove('is-open');
    doc.body.classList.remove('moon-locked');
    var backdrop = doc.querySelector('.moon-shop__backdrop');

    if (backdrop) {
      backdrop.hidden = true;
    }
  }

  if (sortSelect) {
    sortSelect.addEventListener('change', function () {
      applyFilters(1);
    });
  }

  /* ----------------------------------------------------------------------
   * Card colour switching
   * -------------------------------------------------------------------- */
  doc.addEventListener('click', function (event) {
    var chip = event.target.closest('[data-moon-card-color]');

    if (!chip) {
      return;
    }

    event.preventDefault();
    var card = chip.closest('[data-moon-card]');
    var image = card ? card.querySelector('[data-moon-card-img]') : null;
    var swap = image && chip.getAttribute('data-image');

    if (image && swap) {
      image.src = swap;
      var hover = card.querySelector('.moon-card__hover');

      if (hover) {
        hover.style.display = 'none';
      }
    }

    var group = chip.closest('.moon-card__colors');

    if (group) {
      group.querySelectorAll('[data-moon-card-color]').forEach(function (item) {
        item.classList.toggle('is-active', item === chip);
      });
    }
  });

  /* ----------------------------------------------------------------------
   * Quantity steppers
   * -------------------------------------------------------------------- */
  function initQtySteppers() {
  doc.querySelectorAll('.quantity').forEach(function (wrap) {
    var input = wrap.querySelector('input.qty');

    if (!input || wrap.querySelector('.moon-qty__btn')) {
      return;
    }

    var minus = doc.createElement('button');
    minus.type = 'button';
    minus.className = 'moon-qty__btn';
    minus.setAttribute('aria-label', 'کاهش تعداد');
    minus.textContent = '−';

    var plus = doc.createElement('button');
    plus.type = 'button';
    plus.className = 'moon-qty__btn';
    plus.setAttribute('aria-label', 'افزایش تعداد');
    plus.textContent = '+';

    wrap.insertBefore(minus, input);
    wrap.appendChild(plus);

    function step(direction) {
      var value = parseInt(input.value, 10) || 1;
      var min = parseInt(input.getAttribute('min'), 10) || 1;
      var max = parseInt(input.getAttribute('max'), 10) || 999;
      input.value = Math.min(max, Math.max(min, value + direction));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    minus.addEventListener('click', function () {
      step(-1);
    });

    plus.addEventListener('click', function () {
      step(1);
    });
  });
  }

  initQtySteppers();

  /* Cart page: quantity changes save themselves (no "update cart" click). */
  if (window.jQuery && doc.querySelector('.woocommerce-cart-form')) {
    var cartTimer;

    doc.body.classList.add('moon-cart-auto');

    window.jQuery(doc.body).on('change', '.woocommerce-cart-form input.qty', function () {
      window.clearTimeout(cartTimer);
      cartTimer = window.setTimeout(function () {
        window.jQuery('.woocommerce-cart-form [name="update_cart"]').prop('disabled', false).attr('aria-disabled', 'false').trigger('click');
      }, 500);
    });

    window.jQuery(doc.body).on('updated_wc_div updated_cart_totals', initQtySteppers);
  }

  /* ----------------------------------------------------------------------
   * Size guide dialog
   * -------------------------------------------------------------------- */
  var sizeGuide = doc.querySelector('[data-moon-sizeguide]');
  var sizeGuideModal = sizeGuide ? sizeGuide.closest('.moon-sizeguide-modal') : null;

  function openSizeGuide() {
    if (!sizeGuideModal) {
      return;
    }

    sizeGuideModal.classList.add('is-open');
    sizeGuideModal.setAttribute('aria-hidden', 'false');
    doc.body.classList.add('moon-locked');
  }

  function closeSizeGuide() {
    if (!sizeGuideModal) {
      return;
    }

    sizeGuideModal.classList.remove('is-open');
    sizeGuideModal.setAttribute('aria-hidden', 'true');
    doc.body.classList.remove('moon-locked');
  }

  doc.addEventListener('click', function (event) {
    if (event.target.closest('[data-moon-sizeguide-open]') && sizeGuide) {
      event.preventDefault();
      openSizeGuide();
      return;
    }

    var tab = event.target.closest('[data-moon-sizeguide-tab]');

    if (tab && sizeGuide) {
      var key = tab.getAttribute('data-moon-sizeguide-tab');

      sizeGuide.querySelectorAll('[data-moon-sizeguide-tab]').forEach(function (item) {
        item.classList.toggle('is-active', item === tab);
        item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
      });

      sizeGuide.querySelectorAll('[data-moon-sizeguide-panel]').forEach(function (panel) {
        panel.classList.toggle('is-active', panel.getAttribute('data-moon-sizeguide-panel') === key);
      });
      return;
    }

    if (sizeGuideModal && (event.target === sizeGuideModal || event.target.closest('.moon-sizeguide-modal [data-moon-close]'))) {
      closeSizeGuide();
    }
  });

  /* ----------------------------------------------------------------------
   * Whole look add to cart
   * -------------------------------------------------------------------- */
  var bundleData = doc.querySelector('[data-look-bundle-data]');
  var bundleButton = doc.querySelector('[data-look-bundle]');

  if (bundleData && bundleButton) {
    try {
      bundleButton.setAttribute('data-moon-look-add', JSON.stringify(JSON.parse(bundleData.textContent)));
    } catch (error) {
      /* malformed data */
    }
  }

  doc.addEventListener('click', function (event) {
    var button = event.target.closest('[data-moon-look-add]');

    if (!button) {
      return;
    }

    event.preventDefault();
    button.disabled = true;

    post({
      action: 'moon_look_add',
      items: button.getAttribute('data-moon-look-add')
    })
      .then(function (response) {
        if (response && response.fragments) {
          if (api.applyFragments) {
            api.applyFragments(response.fragments);
          }
          toast(i18n.added || 'استایل به سبد خرید اضافه شد.');
          openDrawer();
        } else if (response && response.data && response.data.message) {
          toast(response.data.message);
        }
      })
      .catch(function () {
        toast(i18n.addError || 'افزودن به سبد انجام نشد.');
      })
      .then(function () {
        button.disabled = false;
      });
  });

  /* ----------------------------------------------------------------------
   * Account: phone (OTP) login and registration
   * -------------------------------------------------------------------- */
  var auth = doc.querySelector('[data-moon-auth]');

  if (auth) {
    var otpForm = auth.querySelector('[data-moon-otp-form]');
    var otpNote = auth.querySelector('[data-moon-otp-note]');
    var sendButton = auth.querySelector('[data-moon-otp-send]');
    var registerForm = auth.querySelector('[data-moon-register-form]');
    var registerNote = auth.querySelector('[data-moon-register-note]');

    function showPanel(name) {
      auth.querySelectorAll('[data-moon-auth-panel]').forEach(function (panel) {
        panel.classList.toggle('is-active', panel.getAttribute('data-moon-auth-panel') === name);
      });

      auth.querySelectorAll('[data-moon-auth-tab]').forEach(function (tab) {
        var active = tab.getAttribute('data-moon-auth-tab') === name;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    }

    auth.querySelectorAll('[data-moon-auth-tab]').forEach(function (tab) {
      tab.addEventListener('click', function () {
        showPanel(tab.getAttribute('data-moon-auth-tab'));
      });
    });

    function note(node, message, isError) {
      if (!node) {
        return;
      }

      node.textContent = message || '';
      node.classList.toggle('is-error', !!isError);
    }

    function loginPost(data) {
      var body = new window.URLSearchParams(data);
      body.set('nonce', MOON.loginNonce || '');

      return window.fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).then(function (response) {
        return response.json();
      });
    }

    if (sendButton && otpForm) {
      sendButton.addEventListener('click', function () {
        var phone = otpForm.querySelector('[name="phone"]');

        if (!phone || !phone.value) {
          note(otpNote, 'شماره موبایل را وارد کنید.', true);
          return;
        }

        sendButton.disabled = true;
        sendButton.textContent = 'در حال ارسال…';

        loginPost({ action: 'moon_otp_send', phone: phone.value })
          .then(function (response) {
            if (response && response.success) {
              note(otpNote, response.data.message, false);

              var left = response.data.cooldown || 60;
              var timer = window.setInterval(function () {
                left--;

                if (left <= 0) {
                  window.clearInterval(timer);
                  sendButton.disabled = false;
                  sendButton.textContent = 'ارسال دوباره';
                  return;
                }

                sendButton.textContent = 'ارسال دوباره تا ' + left + ' ثانیه';
              }, 1000);
            } else {
              note(otpNote, (response && response.data && response.data.message) || 'ارسال کد انجام نشد.', true);
              sendButton.disabled = false;
              sendButton.textContent = 'ارسال کد';
            }
          })
          .catch(function () {
            note(otpNote, 'ارسال کد انجام نشد.', true);
            sendButton.disabled = false;
            sendButton.textContent = 'ارسال کد';
          });
      });
    }

    if (otpForm) {
      otpForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var phone = otpForm.querySelector('[name="phone"]');
        var code = otpForm.querySelector('[name="code"]');
        var submit = otpForm.querySelector('button[type="submit"]');

        if (submit) {
          submit.disabled = true;
        }

        loginPost({
          action: 'moon_otp_verify',
          phone: phone ? phone.value : '',
          code: code ? code.value : '',
          redirect: (new window.URLSearchParams(window.location.search)).get('redirect') || ''
        })
          .then(function (response) {
            if (response && response.success) {
              note(otpNote, response.data.message, false);
              window.setTimeout(function () {
                window.location.href = response.data.redirect;
              }, 500);
            } else {
              note(otpNote, (response && response.data && response.data.message) || 'کد درست نیست.', true);
            }
          })
          .catch(function () {
            note(otpNote, 'ورود انجام نشد؛ دوباره تلاش کنید.', true);
          })
          .then(function () {
            if (submit) {
              submit.disabled = false;
            }
          });
      });
    }

    var registerSend = auth.querySelector('[data-moon-register-send]');

    if (registerSend && registerForm) {
      registerSend.addEventListener('click', function () {
        var phone = registerForm.querySelector('[name="phone"]');

        if (!phone || !phone.value) {
          note(registerNote, 'شماره موبایل را وارد کنید.', true);
          return;
        }

        registerSend.disabled = true;

        loginPost({ action: 'moon_otp_send', phone: phone.value })
          .then(function (response) {
            if (response && response.success) {
              note(registerNote, response.data.message, false);

              var left = response.data.cooldown || 60;
              var timer = window.setInterval(function () {
                left--;

                if (left <= 0) {
                  window.clearInterval(timer);
                  registerSend.disabled = false;
                  registerSend.textContent = 'ارسال دوباره';
                  return;
                }

                registerSend.textContent = 'ارسال دوباره تا ' + left + ' ثانیه';
              }, 1000);
            } else {
              note(registerNote, (response && response.data && response.data.message) || 'ارسال کد انجام نشد.', true);
              registerSend.disabled = false;
            }
          })
          .catch(function () {
            note(registerNote, 'ارسال کد انجام نشد.', true);
            registerSend.disabled = false;
          });
      });
    }

    if (registerForm) {
      registerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var data = new window.FormData(registerForm);
        var submit = registerForm.querySelector('button[type="submit"]');

        if (submit) {
          submit.disabled = true;
        }

        loginPost({
          action: 'moon_phone_register',
          first_name: data.get('first_name') || '',
          last_name: data.get('last_name') || '',
          phone: data.get('phone') || '',
          email: data.get('email') || '',
          password: data.get('password') || '',
          code: data.get('code') || ''
        })
          .then(function (response) {
            if (response && response.success) {
              note(registerNote, response.data.message, false);
              window.setTimeout(function () {
                window.location.href = response.data.redirect;
              }, 500);
            } else {
              note(registerNote, (response && response.data && response.data.message) || 'ثبت‌نام انجام نشد.', true);
            }
          })
          .catch(function () {
            note(registerNote, 'ثبت‌نام انجام نشد؛ دوباره تلاش کنید.', true);
          })
          .then(function () {
            if (submit) {
              submit.disabled = false;
            }
          });
      });
    }
  }

  /* ----------------------------------------------------------------------
   * Look page: colourway switching per piece
   * -------------------------------------------------------------------- */
  doc.addEventListener('click', function (event) {
    var chip = event.target.closest('[data-moon-look-color]');

    if (!chip) {
      return;
    }

    event.preventDefault();
    var piece = chip.closest('[data-moon-look-piece]');

    if (!piece) {
      return;
    }

    var image = piece.querySelector('[data-moon-look-img]');
    var src = chip.getAttribute('data-image');

    if (image && src) {
      image.src = src;
    }

    piece.querySelectorAll('[data-moon-look-color]').forEach(function (item) {
      item.classList.toggle('is-active', item === chip);
    });

    var add = piece.querySelector('[data-moon-look-piece-add]');

    if (add) {
      add.setAttribute('data-variation', chip.getAttribute('data-variation') || '');
    }
  });

  doc.addEventListener('click', function (event) {
    var add = event.target.closest('[data-moon-look-piece-add]');

    if (!add) {
      return;
    }

    var productId = add.getAttribute('data-product');
    var variationId = add.getAttribute('data-variation') || '';
    var data = new window.URLSearchParams();
    // WooCommerce 10.6+ resolves the variation from product_id alone.
    data.set('product_id', variationId || productId);
    data.set('quantity', 1);

    if (variationId) {
      data.set('variation_id', variationId);
    }

    add.disabled = true;

    window.fetch((MOON.wcAjax || '/') + '?wc-ajax=add_to_cart', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: data.toString()
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (result) {
        if (!result || !result.fragments) {
          throw new Error('add');
        }

        if (api.applyFragments) {
          api.applyFragments(result.fragments);
        }

        toast(i18n.added || 'به سبد خرید اضافه شد.');
        openDrawer();
      })
      .catch(function () {
        toast(i18n.addError || 'افزودن به سبد انجام نشد.');
      })
      .then(function () {
        add.disabled = false;
      });
  });
})();
