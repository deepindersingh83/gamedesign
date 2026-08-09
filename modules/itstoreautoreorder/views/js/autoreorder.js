/** IT Store — Subscribe & Save (AJAX subscribe). */
(function () {
  'use strict';
  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('.js-itstore-ar-btn') : null;
    if (!btn) { return; }
    var box = btn.closest('[data-itstore-ar]');
    if (!box) { return; }
    var msg = box.querySelector('.itstore-ar__msg');
    btn.disabled = true;

    var body = new FormData();
    body.append('id_product', box.getAttribute('data-id-product'));
    body.append('token', box.getAttribute('data-token'));

    fetch(box.getAttribute('data-subscribe-url'), {
      method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (msg) { msg.hidden = false; msg.textContent = d.message || ''; msg.classList.toggle('is-ok', !!d.success); msg.classList.toggle('is-error', !d.success); }
        if (d.success) { btn.textContent = 'Subscribed'; }
      })
      .catch(function () { if (msg) { msg.hidden = false; msg.textContent = 'Something went wrong.'; } })
      .finally(function () { btn.disabled = false; });
  });
})();
