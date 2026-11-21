/**
 * MOON — mega menu: header panel toggle, tab switching with hover intent.
 */
(function () {
  'use strict';

  var doc = document;
  var toggle = doc.querySelector('[data-moon-mega-toggle]');
  var panel = doc.getElementById('moon-mega');
  var hoverTimer = null;

  function activate(root, index) {
    root.querySelectorAll('[data-moon-mega-tab]').forEach(function (tab) {
      var on = tab.getAttribute('data-moon-mega-tab') === String(index);
      tab.classList.toggle('is-active', on);
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    root.querySelectorAll('[data-moon-mega-pane]').forEach(function (pane) {
      pane.classList.toggle('is-active', pane.getAttribute('data-moon-mega-pane') === String(index));
    });
  }

  doc.querySelectorAll('[data-moon-mega]').forEach(function (root) {
    root.querySelectorAll('[data-moon-mega-tab]').forEach(function (tab) {
      var index = tab.getAttribute('data-moon-mega-tab');

      tab.addEventListener('mouseenter', function () {
        window.clearTimeout(hoverTimer);
        hoverTimer = window.setTimeout(function () { activate(root, index); }, 70);
      });
      tab.addEventListener('focus', function () { activate(root, index); });
      tab.addEventListener('click', function (event) {
        // Touch: first tap opens the pane, a second tap follows the link.
        if (window.matchMedia('(hover: none)').matches && !tab.classList.contains('is-active')) {
          event.preventDefault();
          activate(root, index);
        }
      });
    });
  });

  if (!toggle || !panel) {
    return;
  }

  function place() {
    var header = doc.getElementById('moon-header');
    var rect = header ? header.getBoundingClientRect() : { bottom: 80 };
    panel.style.setProperty('--moon-mega-top', Math.max(rect.bottom + 8, 0) + 'px');
  }

  function open() {
    place();
    panel.hidden = false;
    window.requestAnimationFrame(function () { panel.classList.add('is-open'); });
    toggle.setAttribute('aria-expanded', 'true');
  }

  function close(returnFocus) {
    panel.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    window.setTimeout(function () {
      if (!panel.classList.contains('is-open')) { panel.hidden = true; }
    }, 220);

    if (returnFocus) { toggle.focus(); }
  }

  toggle.addEventListener('click', function () {
    if (toggle.getAttribute('aria-expanded') === 'true') { close(false); } else { open(); }
  });

  var closeTimer = null;
  [toggle, panel].forEach(function (node) {
    node.addEventListener('mouseenter', function () {
      window.clearTimeout(closeTimer);
      if (window.matchMedia('(hover: hover)').matches && toggle.getAttribute('aria-expanded') !== 'true') { open(); }
    });
    node.addEventListener('mouseleave', function () {
      if (!window.matchMedia('(hover: hover)').matches) { return; }
      closeTimer = window.setTimeout(function () { close(false); }, 220);
    });
  });

  doc.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { close(true); }
  });

  doc.addEventListener('click', function (event) {
    if (toggle.getAttribute('aria-expanded') === 'true' && !panel.contains(event.target) && !toggle.contains(event.target)) {
      close(false);
    }
  });

  window.addEventListener('resize', function () { if (!panel.hidden) { place(); } });
})();
