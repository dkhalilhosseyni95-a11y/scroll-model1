/* MOON — live search suggestions inside the search overlay. */
(function () {
  'use strict';

  var cfg = window.MOON || {};
  var input = document.getElementById('moon-search-field');

  if (!input || !cfg.ajaxUrl) {
    return;
  }

  var box = document.createElement('div');
  box.className = 'moon-ls';
  box.setAttribute('aria-live', 'polite');
  input.closest('form').insertAdjacentElement('afterend', box);

  var timer;
  var seq = 0;
  var cache = {};

  function show(data) {
    box.innerHTML = data.html || '';

    if (data.count && data.moreUrl) {
      var more = document.createElement('a');
      more.className = 'moon-ls__more';
      more.href = data.moreUrl;
      more.textContent = data.moreLabel;
      box.appendChild(more);
    }
  }

  function run(term) {
    if (cache[term]) {
      show(cache[term]);
      return;
    }

    var mine = ++seq;
    var body = new window.URLSearchParams();
    body.set('action', 'moon_live_search');
    body.set('nonce', cfg.nonce);
    body.set('s', term);

    window.fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        if (mine !== seq || !json || !json.success) { return; }
        cache[term] = json.data;
        show(json.data);
      })
      .catch(function () { /* the normal search form still works */ });
  }

  input.addEventListener('input', function () {
    var term = input.value.trim();

    window.clearTimeout(timer);

    if (term.length < 2) {
      seq += 1;
      box.innerHTML = '';
      return;
    }

    timer = window.setTimeout(function () { run(term); }, 220);
  });
})();
