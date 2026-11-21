/* MOON mega menu builder — plain jQuery, state lives in one object and is serialised on submit. */
(function ($) {
  'use strict';

  var cfg = window.MOON_MEGA || { data: {}, tree: [] };
  var state = $.extend({ enabled: 0, label: '', items: [] }, cfg.data);
  var current = 0;
  var $rail = $('#moon-mega-rail');
  var $editor = $('#moon-mega-editor');

  function blankItem(title) {
    return { title: title || 'دسته جدید', url: '', image: 0, imageUrl: '', cols: [], banner: { image: 0, imageUrl: '', url: '', title: '' } };
  }

  function pickImage(done) {
    var frame = window.wp.media({ title: 'انتخاب تصویر', multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      var u = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
      done(a.id, u, a.url);
    });
    frame.open();
  }

  function fromTerm(term) {
    var item = blankItem(term.name);
    item.url = term.url;
    item.image = term.image || 0;
    item.imageUrl = term.imageUrl || '';

    if (term.children.length) {
      term.children.forEach(function (child) {
        item.cols.push({
          title: child.name,
          url: child.url,
          links: child.children.map(function (g) { return { title: g.name, url: g.url, image: g.image || 0, imageUrl: g.imageUrl || '' }; })
        });
      });
    }

    return item;
  }

  function el(tag, attrs, html) {
    var $n = $('<' + tag + '>');
    if (attrs) { $n.attr(attrs); }
    if (html !== undefined) { $n.text(html); }
    return $n;
  }

  function bind($input, obj, key) {
    $input.val(obj[key] || '').on('input', function () { obj[key] = $input.val(); });
    return $input;
  }

  function thumb(obj) {
    var $t = el('img', { 'class': 'moon-mega-admin__thumb', alt: '', title: 'انتخاب تصویر' });
    if (obj.imageUrl) { $t.attr('src', obj.imageUrl); }
    $t.on('click', function () {
      pickImage(function (id, url) { obj.image = id; obj.imageUrl = url; $t.attr('src', url); renderRail(); });
    });
    return $t;
  }

  function renderRail() {
    $rail.empty();
    state.items.forEach(function (item, i) {
      var $li = el('li', { 'data-i': i, 'class': i === current ? 'is-active' : '' });
      $li.append(el('span', { 'class': 'handle dashicons dashicons-menu' }), el('span', null, item.title || '—'));
      $li.on('click', function () { current = i; renderRail(); renderEditor(); });
      $rail.append($li);
    });

    if ($rail.data('ui-sortable')) { $rail.sortable('destroy'); }
    $rail.sortable({
      handle: '.handle',
      update: function () {
        var order = $rail.children().map(function () { return parseInt($(this).attr('data-i'), 10); }).get();
        var active = state.items[current];
        state.items = order.map(function (i) { return state.items[i]; });
        current = Math.max(0, state.items.indexOf(active));
        renderRail();
      }
    });
  }

  function renderLinks($wrap, col) {
    $wrap.empty();
    col.links.forEach(function (link, li) {
      var $row = el('div', { 'class': 'moon-mega-admin__link' });
      $row.append(
        thumb(link),
        bind(el('input', { type: 'text', placeholder: 'عنوان لینک' }), link, 'title'),
        bind(el('input', { type: 'url', placeholder: 'https://' }), link, 'url'),
        el('button', { type: 'button', 'class': 'button-link danger' }, 'حذف').on('click', function () { col.links.splice(li, 1); renderLinks($wrap, col); })
      );
      $wrap.append($row);
    });
  }

  function renderEditor() {
    $editor.empty();
    var item = state.items[current];

    if (!item) {
      $editor.append(el('p', null, 'هنوز دسته‌ای ساخته نشده است. با «دسته اصلی جدید» یا «ساخت خودکار» شروع کنید.'));
      return;
    }

    var $head = el('div', { 'class': 'moon-mega-admin__row' });
    $head.append(
      thumb(item),
      bind(el('input', { type: 'text', placeholder: 'عنوان دسته اصلی' }), item, 'title').on('input', renderRail),
      bind(el('input', { type: 'url', placeholder: 'لینک دسته (https://…)' }), item, 'url')
    );

    var $sel = el('select');
    $sel.append(el('option', { value: '' }, 'پر کردن از دسته ووکامرس…'));
    cfg.tree.forEach(function (t) { $sel.append(el('option', { value: t.id }, t.name)); });
    $sel.on('change', function () {
      var term = cfg.tree.filter(function (t) { return String(t.id) === $sel.val(); })[0];
      if (term && window.confirm('ستون‌های فعلی این دسته جایگزین می‌شوند. ادامه می‌دهید؟')) {
        state.items[current] = $.extend(fromTerm(term), { banner: item.banner });
        renderRail(); renderEditor();
      }
    });
    $head.append($sel, el('button', { type: 'button', 'class': 'button-link danger' }, 'حذف این دسته').on('click', function () {
      if (window.confirm('این دسته اصلی حذف شود؟')) { state.items.splice(current, 1); current = 0; renderRail(); renderEditor(); }
    }));
    $editor.append($head);

    item.cols.forEach(function (col, ci) {
      var $col = el('div', { 'class': 'moon-mega-admin__col' });
      var $r = el('div', { 'class': 'moon-mega-admin__row' });
      var $links = el('div');
      $r.append(
        bind(el('input', { type: 'text', placeholder: 'عنوان ستون' }), col, 'title'),
        bind(el('input', { type: 'url', placeholder: 'لینک ستون (اختیاری)' }), col, 'url'),
        el('button', { type: 'button', 'class': 'button-link' }, '↑').on('click', function () { if (ci > 0) { item.cols.splice(ci - 1, 0, item.cols.splice(ci, 1)[0]); renderEditor(); } }),
        el('button', { type: 'button', 'class': 'button-link' }, '↓').on('click', function () { if (ci < item.cols.length - 1) { item.cols.splice(ci + 1, 0, item.cols.splice(ci, 1)[0]); renderEditor(); } }),
        el('button', { type: 'button', 'class': 'button-link danger' }, 'حذف ستون').on('click', function () { item.cols.splice(ci, 1); renderEditor(); })
      );
      renderLinks($links, col);
      $col.append($r, $links, el('button', { type: 'button', 'class': 'button' }, '+ لینک').on('click', function () {
        col.links.push({ title: '', url: '', image: 0, imageUrl: '' }); renderLinks($links, col);
      }));
      $editor.append($col);
    });

    $editor.append(el('button', { type: 'button', 'class': 'button' }, '+ ستون جدید').on('click', function () {
      item.cols.push({ title: 'ستون جدید', url: '', links: [] }); renderEditor();
    }));

    var b = item.banner;
    var $b = el('div', { 'class': 'moon-mega-admin__banner' });
    var $bimg = el('img', { alt: '' });
    if (b.imageUrl) { $bimg.attr('src', b.imageUrl); }
    $b.append(
      el('strong', null, 'بنر تبلیغاتی کنار منو (اختیاری)'),
      el('div', { 'class': 'moon-mega-admin__row' }).append(
        el('button', { type: 'button', 'class': 'button' }, 'انتخاب تصویر بنر').on('click', function () {
          pickImage(function (id, thumbUrl, full) { b.image = id; b.imageUrl = full; $bimg.attr('src', full); });
        }),
        bind(el('input', { type: 'text', placeholder: 'متن روی بنر' }), b, 'title'),
        bind(el('input', { type: 'url', placeholder: 'لینک بنر' }), b, 'url'),
        el('button', { type: 'button', 'class': 'button-link danger' }, 'حذف بنر').on('click', function () { b.image = 0; b.imageUrl = ''; $bimg.removeAttr('src'); })
      ),
      $bimg
    );
    $editor.append($b);
  }

  $('#moon-mega-enabled').prop('checked', !!parseInt(state.enabled, 10)).on('change', function () { state.enabled = this.checked ? 1 : 0; });
  $('#moon-mega-label').val(state.label).on('input', function () { state.label = this.value; });

  $('#moon-mega-add').on('click', function () { state.items.push(blankItem()); current = state.items.length - 1; renderRail(); renderEditor(); });
  $('#moon-mega-fill-all').on('click', function () {
    if (state.items.length && !window.confirm('تمام دسته‌های فعلی با ساختار ووکامرس جایگزین می‌شوند. ادامه می‌دهید؟')) { return; }
    state.items = cfg.tree.map(fromTerm); current = 0; renderRail(); renderEditor();
  });

  $('#moon-mega-form').on('submit', function () { $('#moon-mega-json').val(JSON.stringify(state)); });

  renderRail();
  renderEditor();
})(window.jQuery);
