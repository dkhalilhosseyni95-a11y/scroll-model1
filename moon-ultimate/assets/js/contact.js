/* MOON — contact form: validates, then hands the message to WordPress. */
(function () {
  'use strict';

  var form = document.querySelector('[data-contact-form]');
  var cfg = window.MOON || {};

  if (!form || !cfg.ajaxUrl) {
    return;
  }

  var status = form.querySelector('[data-contact-status]');
  var submit = form.querySelector('[type="submit"]');
  var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function value(name) {
    var node = form.elements.namedItem(name);
    return node && 'value' in node ? node.value.trim() : '';
  }

  function say(message, state) {
    if (status) {
      status.textContent = message || '';
      status.setAttribute('data-state', state || '');
    }
  }

  function invalid(name, message) {
    var node = form.elements.namedItem(name);
    say(message, 'error');

    if (node) {
      node.setAttribute('aria-invalid', 'true');
      node.focus();
    }
  }

  form.addEventListener('input', function (event) {
    if (event.target.removeAttribute) {
      event.target.removeAttribute('aria-invalid');
    }
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    var email = value('email');
    var phone = value('phone');

    if (!value('name')) { return invalid('name', 'نام را وارد کنید.'); }
    if (!value('message')) { return invalid('message', 'متن پیام را بنویسید.'); }
    if (!email && !phone) { return invalid('phone', 'ایمیل یا شماره تماس را وارد کنید.'); }
    if (email && !emailPattern.test(email)) { return invalid('email', 'ایمیل معتبر نیست.'); }

    var body = new window.FormData(form);
    body.append('action', 'moon_contact');
    body.append('nonce', cfg.nonce || '');

    submit.disabled = true;
    say('در حال ارسال…', '');

    window.fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        if (json && json.success) {
          form.reset();
          say((json.data && json.data.message) || 'پیام شما ثبت شد.', 'ok');
        } else {
          say((json && json.data && json.data.message) || 'ارسال انجام نشد؛ دوباره تلاش کنید.', 'error');
        }
      })
      .catch(function () { say('ارسال انجام نشد؛ دوباره تلاش کنید.', 'error'); })
      .then(function () { submit.disabled = false; });
  });
})();
