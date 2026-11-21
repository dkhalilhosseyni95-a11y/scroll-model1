/**
 * MOON Demo Importer — runs the import step by step through AJAX so a large
 * demo never hits the PHP time limit, and reports each step as it completes.
 */
(function () {
  'use strict';

  var config = window.MOON_DEMO || {};
  var form = document.getElementById('moon-demo-form');

  if (!form || !config.ajaxUrl) {
    return;
  }

  var button = form.querySelector('[data-moon-demo-start]');
  var status = form.querySelector('[data-moon-demo-status]');
  var progress = document.querySelector('[data-moon-demo-progress]');
  var log = document.querySelector('[data-moon-demo-log]');

  var order = ['media', 'pages', 'terms', 'store', 'products', 'rack', 'options', 'menu'];

  function post(data) {
    var body = new window.URLSearchParams(data);
    body.set('nonce', config.nonce || '');

    return window.fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    });
  }

  function line(text, type) {
    var item = document.createElement('li');
    item.textContent = text;

    if (type) {
      item.className = 'is-' + type;
    }

    log.appendChild(item);
  }

  function append(payload) {
    if (!payload) {
      return;
    }

    (payload.messages || []).forEach(function (message) {
      line(message);
    });

    (payload.skipped || []).forEach(function (message) {
      line(message, 'skip');
    });

    (payload.errors || []).forEach(function (message) {
      line(message, 'error');
    });
  }

  if (!button) {
    return;
  }

  button.addEventListener('click', function () {
    var selected = Array.prototype.slice
      .call(form.querySelectorAll('input[name="steps[]"]:checked'))
      .map(function (input) {
        return input.value;
      });

    if (!selected.length) {
      status.textContent = config.i18n.none;
      return;
    }

    progress.hidden = false;
    log.innerHTML = '';
    button.disabled = true;
    status.textContent = config.i18n.working;

    var queue = order.filter(function (step) {
      return selected.indexOf(step) !== -1;
    });

    var options = {};

    selected.forEach(function (step) {
      options['options[' + step + ']'] = '1';
    });

    // Snapshot first, then one request per selected step.
    post(Object.assign({ action: 'moon_demo_step', step: 'first' }, options))
      .then(function (response) {
        append(response && response.data);
      })
      .then(function () {
        return queue.reduce(function (chain, step) {
          return chain.then(function () {
            status.textContent = step + '…';

            var payload = Object.assign({ action: 'moon_demo_step', step: step }, options);

            return post(payload).then(function (response) {
              append(response && response.data);

              if (response && response.data && response.data.done) {
                return;
              }
            });
          });
        }, Promise.resolve());
      })
      .then(function () {
        status.textContent = config.i18n.done + ' ' + config.i18n.reload;
        button.disabled = false;
      })
      .catch(function () {
        status.textContent = config.i18n.failed;
        button.disabled = false;
      });
  });

  var rollback = document.querySelector('[data-moon-demo-rollback]');

  if (rollback) {
    rollback.addEventListener('click', function (event) {
      if (!window.confirm(rollback.getAttribute('data-moon-confirm') || 'بازگردانی انجام شود؟')) {
        return;
      }

      event.preventDefault();
      rollback.disabled = true;

      post({ action: 'moon_demo_rollback' })
        .then(function (response) {
          progress.hidden = false;
          log.innerHTML = '';
          append(response && response.data);
          status.textContent = config.i18n.done + ' ' + config.i18n.reload;
        })
        .catch(function () {
          status.textContent = config.i18n.failed;
        })
        .then(function () {
          rollback.disabled = false;
        });
    });
  }
})();
