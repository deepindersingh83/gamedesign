/**
 * IT Store — Recently Viewed (localStorage).
 * Captures the current product on the product page and renders the strip
 * wherever a [data-itstore-recent] container exists.
 */
(function () {
  'use strict';
  var KEY = 'itstore_recent';
  var MAX = 8;

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; }
  }
  function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {} }

  function capture() {
    var el = document.querySelector('[data-recent-capture]');
    if (!el) { return; }
    var p;
    try { p = JSON.parse(el.textContent); } catch (e) { return; }
    if (!p || !p.id) { return; }
    var list = load().filter(function (i) { return String(i.id) !== String(p.id); });
    list.unshift(p);
    save(list.slice(0, MAX));
  }

  function render() {
    var currentId = null;
    var cap = document.querySelector('[data-recent-capture]');
    if (cap) { try { currentId = JSON.parse(cap.textContent).id; } catch (e) {} }

    document.querySelectorAll('[data-itstore-recent]').forEach(function (section) {
      var grid = section.querySelector('[data-recent-grid]');
      if (!grid) { return; }
      var items = load().filter(function (i) { return String(i.id) !== String(currentId); });
      if (!items.length) { return; }
      grid.innerHTML = '';
      items.forEach(function (i) {
        var a = document.createElement('a');
        a.className = 'itstore-rec__card';
        a.href = i.url;
        a.innerHTML = '<span class="itstore-rec__media"><img src="' + i.image + '" alt="" loading="lazy"></span>'
          + '<span class="itstore-rec__name"></span><span class="itstore-rec__price"></span>';
        a.querySelector('.itstore-rec__name').textContent = i.name || '';
        a.querySelector('.itstore-rec__price').textContent = i.price || '';
        grid.appendChild(a);
      });
      section.hidden = false;
    });
  }

  function ready() { capture(); render(); }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
