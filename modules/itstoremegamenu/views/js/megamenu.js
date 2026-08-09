/**
 * IT Store — mega-menu (mobile/touch toggling; desktop uses CSS hover).
 */
(function () {
  'use strict';
  function ready() {
    var toggles = document.querySelectorAll('.js-itstore-mm-toggle');
    Array.prototype.forEach.call(toggles, function (link) {
      link.addEventListener('click', function (e) {
        // Only intercept when a panel exists and we're in touch/narrow mode.
        var item = link.parentNode;
        var panel = item.querySelector('.itstore-mm__panel');
        if (!panel) { return; }
        if (window.matchMedia('(max-width: 900px)').matches || window.matchMedia('(hover: none)').matches) {
          if (!panel.classList.contains('is-open')) {
            e.preventDefault();
            closeAll();
            panel.classList.add('is-open');
            link.setAttribute('aria-expanded', 'true');
          }
        }
      });
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest || !e.target.closest('.itstore-mm')) { closeAll(); }
    });

    function closeAll() {
      document.querySelectorAll('.itstore-mm__panel.is-open').forEach(function (p) {
        p.classList.remove('is-open');
      });
      document.querySelectorAll('.js-itstore-mm-toggle[aria-expanded="true"]').forEach(function (l) {
        l.setAttribute('aria-expanded', 'false');
      });
    }
  }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
