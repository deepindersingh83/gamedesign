/** IT Store — Ask a Question AJAX submit. */
(function () {
  'use strict';
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.classList || !form.classList.contains('js-itstore-aq-form')) { return; }
    e.preventDefault();
    var msg = form.querySelector('.itstore-aq__msg');
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
      .catch(function () { if (msg) { msg.hidden = false; msg.textContent = 'Something went wrong.'; msg.classList.add('is-error'); } })
      .finally(function () { if (btn) { btn.disabled = false; } });
  });
})();
