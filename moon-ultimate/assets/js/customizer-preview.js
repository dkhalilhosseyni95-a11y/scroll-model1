/**
 * Live preview of the wordmark in the Customizer.
 * Only mirrors the site title when no custom brand name is set in the MOON panel
 * (the server renders the panel value; postMessage only updates the fallback).
 */
(function (api) {
  'use strict';

  if (!api) {
    return;
  }

  api('blogname', function (value) {
    value.bind(function (to) {
      var nodes = document.querySelectorAll('.moon-brand__text, .moon-footer__brand');

      for (var i = 0; i < nodes.length; i++) {
        nodes[i].textContent = to;
      }
    });
  });
})(window.wp && window.wp.customize);
