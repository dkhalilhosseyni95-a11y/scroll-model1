/**
 * MOON — account: phone (OTP) login and phone-verified registration.
 *
 * Registration is a two-step flow: the number must be confirmed with a code
 * first, and the server only accepts the details once it has issued the
 * single-use token that step two sends back. Without that token the
 * registration endpoint refuses the request, so an account can never be
 * created for a number nobody controls.
 *
 * Loaded on the account screens only.
 */
(function () {
  'use strict';

  var auth = document.querySelector('[data-moon-auth]');

  if (!auth) {
    return;
  }

  var MOON = window.MOON || {};
  var ajaxUrl = MOON.ajaxUrl || '/wp-admin/admin-ajax.php';

  var otpForm = auth.querySelector('[data-moon-otp-form]');
  var otpNote = auth.querySelector('[data-moon-otp-note]');
  var sendButton = auth.querySelector('[data-moon-otp-send]');
  var registerForm = auth.querySelector('[data-moon-register-form]');
  var registerNote = auth.querySelector('[data-moon-register-note]');

  function note(node, message, isError) {
    if (!node) {
      return;
    }

    node.textContent = message || '';
    node.classList.toggle('is-error', !!isError);
  }

  function post(data) {
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

  function currentRedirect() {
    return (new window.URLSearchParams(window.location.search)).get('redirect') || '';
  }

  /* Start the cooldown on any "send code" button. */
  function startCooldown(button, seconds) {
    if (!button) {
      return;
    }

    var left = seconds || 60;
    var original = 'ارسال کد';

    button.disabled = true;

    var timer = window.setInterval(function () {
      left--;

      if (left <= 0) {
        window.clearInterval(timer);
        button.disabled = false;
        button.textContent = 'ارسال دوباره';
        return;
      }

      button.textContent = 'ارسال دوباره تا ' + left + ' ثانیه';
    }, 1000);

    button.textContent = 'ارسال دوباره تا ' + left + ' ثانیه';
    return original;
  }

  function sendCode(phoneInput, button, noteNode, onDone) {
    if (!phoneInput || !phoneInput.value) {
      note(noteNode, 'شماره موبایل را وارد کنید.', true);
      return;
    }

    button.disabled = true;
    button.textContent = 'در حال ارسال…';

    post({ action: 'moon_otp_send', phone: phoneInput.value })
      .then(function (response) {
        if (response && response.success) {
          note(noteNode, response.data.message, false);
          startCooldown(button, response.data.cooldown || 60);

          if (onDone) {
            onDone(response.data);
          }

          return;
        }

        note(noteNode, (response && response.data && response.data.message) || 'ارسال کد انجام نشد.', true);
        button.disabled = false;
        button.textContent = 'ارسال کد';
      })
      .catch(function () {
        note(noteNode, 'ارسال کد انجام نشد.', true);
        button.disabled = false;
        button.textContent = 'ارسال کد';
      });
  }

  /* ----------------------------------------------------------------------
   * Login with a code
   * -------------------------------------------------------------------- */
  if (sendButton && otpForm) {
    sendButton.addEventListener('click', function () {
      sendCode(otpForm.querySelector('[name="phone"]'), sendButton, otpNote);
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

      post({
        action: 'moon_otp_verify',
        phone: phone ? phone.value : '',
        code: code ? code.value : '',
        redirect: currentRedirect()
      })
        .then(function (response) {
          if (response && response.success) {
            note(otpNote, response.data.message, false);

            window.setTimeout(function () {
              window.location.href = response.data.redirect;
            }, 500);

            return;
          }

          note(otpNote, (response && response.data && response.data.message) || 'کد درست نیست.', true);

          /* A number with no account gets a verified token: move to step two. */
          if (response && response.data && response.data.register_token && registerForm) {
            showRegisterDetails(response.data);
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

  /* ----------------------------------------------------------------------
   * Verified registration
   * -------------------------------------------------------------------- */
  function registerStep(name) {
    if (!registerForm) {
      return;
    }

    registerForm.querySelectorAll('[data-moon-register-step]').forEach(function (step) {
      step.hidden = step.getAttribute('data-moon-register-step') !== name;
    });
  }

  function showRegisterDetails(payload) {
    var token = registerForm.querySelector('[data-moon-register-token]');
    var phone = registerForm.querySelector('[name="phone"]');

    if (token) {
      token.value = payload.register_token || '';
    }

    if (phone && payload.phone) {
      phone.value = payload.phone;
      phone.setAttribute('readonly', 'readonly');
    }

    registerStep('details');
    note(registerNote, payload.message || 'شماره شما تأیید شد.', false);

    var first = registerForm.querySelector('[name="first_name"]');

    if (first) {
      first.focus();
    }
  }

  if (registerForm) {
    var registerSend = registerForm.querySelector('[data-moon-register-send]');
    var registerVerify = registerForm.querySelector('[data-moon-register-verify]');

    if (registerSend) {
      registerSend.addEventListener('click', function () {
        sendCode(registerForm.querySelector('[name="phone"]'), registerSend, registerNote);
      });
    }

    if (registerVerify) {
      registerVerify.addEventListener('click', function () {
        var phone = registerForm.querySelector('[name="phone"]');
        var code = registerForm.querySelector('[name="code"]');

        registerVerify.disabled = true;

        post({
          action: 'moon_otp_verify',
          phone: phone ? phone.value : '',
          code: code ? code.value : '',
          redirect: currentRedirect()
        })
          .then(function (response) {
            if (response && response.success && response.data.register_token) {
              showRegisterDetails(response.data);
              return;
            }

            if (response && response.success) {
              /* The number already has an account: the visitor is signed in. */
              window.location.href = response.data.redirect;
              return;
            }

            note(registerNote, (response && response.data && response.data.message) || 'تأیید شماره انجام نشد.', true);
          })
          .catch(function () {
            note(registerNote, 'تأیید شماره انجام نشد؛ دوباره تلاش کنید.', true);
          })
          .then(function () {
            registerVerify.disabled = false;
          });
      });
    }

    registerForm.addEventListener('submit', function (event) {
      event.preventDefault();

      var data = new window.FormData(registerForm);
      var submit = registerForm.querySelector('button[type="submit"]');

      if (!data.get('token')) {
        registerStep('verify');
        note(registerNote, 'ابتدا شماره موبایل خود را با کد تأیید کنید.', true);
        return;
      }

      if (submit) {
        submit.disabled = true;
      }

      post({
        action: 'moon_phone_register',
        token: data.get('token') || '',
        first_name: data.get('first_name') || '',
        last_name: data.get('last_name') || '',
        phone: data.get('phone') || '',
        email: data.get('email') || '',
        password: data.get('password') || ''
      })
        .then(function (response) {
          if (response && response.success) {
            note(registerNote, response.data.message, false);

            window.setTimeout(function () {
              window.location.href = response.data.redirect;
            }, 500);

            return;
          }

          note(registerNote, (response && response.data && response.data.message) || 'ثبت‌نام انجام نشد.', true);

          /* An expired token sends the visitor back to the code step. */
          if (response && response.data && /تأیید/.test(String(response.data.message || ''))) {
            var token = registerForm.querySelector('[data-moon-register-token]');

            if (token) {
              token.value = '';
            }

            registerStep('verify');
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

  /* ----------------------------------------------------------------------
   * Tabs
   * -------------------------------------------------------------------- */
  auth.querySelectorAll('[data-moon-auth-tab]').forEach(function (tab) {
    tab.addEventListener('click', function () {
      var name = tab.getAttribute('data-moon-auth-tab');

      auth.querySelectorAll('[data-moon-auth-panel]').forEach(function (panel) {
        panel.classList.toggle('is-active', panel.getAttribute('data-moon-auth-panel') === name);
      });

      auth.querySelectorAll('[data-moon-auth-tab]').forEach(function (item) {
        var active = item.getAttribute('data-moon-auth-tab') === name;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-selected', active ? 'true' : 'false');
      });
    });
  });
})();
