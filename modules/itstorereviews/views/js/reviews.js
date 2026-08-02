/**
 * IT Store — review form: star rating + AJAX submit.
 */
(function () {
  'use strict';

  function initStars(form) {
    var stars = form.querySelectorAll('.js-itstore-star');
    var input = form.querySelector('.js-itstore-rating-value');
    function paint(val) {
      Array.prototype.forEach.call(stars, function (s) {
        s.classList.toggle('is-on', parseInt(s.getAttribute('data-value'), 10) <= val);
      });
    }
    Array.prototype.forEach.call(stars, function (s) {
      s.addEventListener('click', function () {
        var v = parseInt(s.getAttribute('data-value'), 10);
        if (input) { input.value = v; }
        paint(v);
      });
      s.addEventListener('mouseenter', function () { paint(parseInt(s.getAttribute('data-value'), 10)); });
    });
    form.querySelector('.js-itstore-rating').addEventListener('mouseleave', function () {
      paint(input ? parseInt(input.value, 10) : 5);
    });
    paint(input ? parseInt(input.value, 10) : 5);
  }

  function ready() {
    var form = document.querySelector('.js-itstore-review-form');
    if (!form) { return; }
    initStars(form);

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg = form.querySelector('.itstore-reviews__msg');
      var btn = form.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (msg) {
            msg.hidden = false;
            msg.textContent = data.message || '';
            msg.classList.toggle('is-ok', !!data.success);
            msg.classList.toggle('is-error', !data.success);
          }
          if (data.success) { form.reset(); }
        })
        .catch(function () {
          if (msg) { msg.hidden = false; msg.textContent = 'Something went wrong.'; msg.classList.add('is-error'); }
        })
        .finally(function () { if (btn) { btn.disabled = false; } });
    });
  }

  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
