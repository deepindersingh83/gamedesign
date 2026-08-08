/**
 * IT Store — cookie consent (dependency-free).
 *
 * Reveals the banner only when no choice is stored, persists the visitor's
 * choice in a first-party cookie, and dispatches a `itstore:consent` event
 * (detail: { consent: 'accepted' | 'declined' }) so other scripts can gate
 * non-essential trackers. A stored choice also re-fires the event on load so
 * late-loading scripts can read it.
 */
(function () {
  'use strict';

  var COOKIE = 'itstore_cookie_consent';

  function readCookie(name) {
    var m = document.cookie.match('(?:^|; )' + name.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]*)');
    return m ? decodeURIComponent(m[1]) : null;
  }

  function writeCookie(name, value, days) {
    var expires = '';
    if (days) {
      var d = new Date();
      d.setTime(d.getTime() + days * 864e5);
      expires = '; expires=' + d.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires +
      '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
  }

  function emit(consent) {
    try {
      document.dispatchEvent(new CustomEvent('itstore:consent', { detail: { consent: consent } }));
    } catch (e) {
      // Older browsers: no-op.
    }
  }

  function init() {
    var el = document.getElementById('itstore-ck');
    if (!el) { return; }

    var prior = readCookie(COOKIE);
    if (prior === 'accepted' || prior === 'declined') {
      emit(prior);
      return;
    }

    var days = parseInt(el.getAttribute('data-days'), 10) || 180;
    el.hidden = false;
    // Force a reflow before adding the visible class so the entrance animates.
    void el.offsetWidth;
    el.classList.add('is-visible');

    el.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-ck-choice]');
      if (!btn) { return; }
      var choice = btn.getAttribute('data-ck-choice');
      writeCookie(COOKIE, choice, days);
      emit(choice);
      el.classList.remove('is-visible');
      window.setTimeout(function () { el.hidden = true; }, 260);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
