/** IT Store — hero audience toggle. */
(function () {
  'use strict';
  function ready() {
    var hero = document.querySelector('[data-itstore-hero]');
    if (!hero) { return; }
    var tabs = hero.querySelectorAll('.itstore-hero2__tab');
    Array.prototype.forEach.call(tabs, function (tab) {
      tab.addEventListener('click', function () {
        var view = tab.getAttribute('data-hero-view');
        tabs.forEach(function (t) {
          var on = t === tab;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        hero.querySelectorAll('[data-hero-panel]').forEach(function (p) {
          p.classList.toggle('is-active', p.getAttribute('data-hero-panel') === view);
        });
        hero.querySelectorAll('[data-hero-img]').forEach(function (img) {
          img.classList.toggle('is-active', img.getAttribute('data-hero-img') === view);
        });
      });
    });
  }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
