/* MOON — merge the visitor's local wishlist into their signed-in account. */
(function () {
  'use strict';

  var cfg = window.moonAccountSync || {};
  var KEY = cfg.key || 'moon-wishlist';
  var queue = Promise.resolve();

  if (!cfg.ajaxUrl || !cfg.nonce) {
    return;
  }

  function read() {
    try {
      var value = JSON.parse(window.localStorage.getItem(KEY) || '[]');
      return Array.isArray(value) ? value : [];
    } catch (error) {
      return [];
    }
  }

  function ids(items) {
    return items.map(function (item) {
      return parseInt(item && item.id, 10) || 0;
    }).filter(Boolean).slice(0, 200);
  }

  function request(items, mode) {
    var body = new window.FormData();
    body.append('action', 'moon_sync_account_state');
    body.append('nonce', cfg.nonce);
    body.append('mode', mode || 'merge');
    body.append('wishlist_ids', JSON.stringify(ids(items || [])));

    queue = queue.catch(function () {}).then(function () {
      return window.fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body });
    }).then(function (response) {
      if (!response.ok) { throw new Error('sync'); }
      return response.json();
    }).then(function (result) {
      if (!result || !result.success || !result.data || !Array.isArray(result.data.wishlist)) { return; }

      try { window.localStorage.setItem(KEY, JSON.stringify(result.data.wishlist)); } catch (error) { /* ignore */ }
      window.dispatchEvent(new window.CustomEvent('moon:wishlist-synced', { detail: result.data }));
    }).catch(function () {
      /* keep the browser copy; retry on the next visit */
    });

    return queue;
  }

  /* Called by the theme after every like/unlike so the account follows. */
  window.moonSyncWishlist = function (items) { request(items, 'replace'); };

  function start() { request(read(), 'merge'); }

  if ('loading' === document.readyState) {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();
