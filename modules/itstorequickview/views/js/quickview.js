/**
 * IT Store — quick-view modal.
 * Opens a product page inside an isolated iframe modal from listing buttons.
 */
(function () {
  'use strict';

  function ready() {
    var modal = document.querySelector('[data-itstore-qv]');
    if (!modal) { return; }
    var frame = modal.querySelector('[data-qv-frame]');
    var loader = modal.querySelector('[data-qv-loader]');

    function open(url) {
      if (loader) { loader.hidden = false; }
      if (frame) {
        frame.style.opacity = '0';
        // Append a flag so the product page can hide chrome if it wants to.
        frame.src = url + (url.indexOf('?') === -1 ? '?' : '&') + 'itstore_qv=1';
      }
      modal.hidden = false;
      document.body.classList.add('itstore-qv-open');
    }

    function close() {
      modal.hidden = true;
      document.body.classList.remove('itstore-qv-open');
      if (frame) { frame.src = 'about:blank'; }
    }

    if (frame) {
      frame.addEventListener('load', function () {
        if (frame.src && frame.src !== 'about:blank') {
          if (loader) { loader.hidden = true; }
          frame.style.opacity = '1';
        }
      });
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest('.js-itstore-qv') : null;
      if (btn) {
        e.preventDefault();
        open(btn.getAttribute('data-qv-url'));
        return;
      }
      if (e.target.closest && e.target.closest('[data-qv-close]')) {
        close();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) { close(); }
    });
  }

  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
