/**
 * IT Store — compare tray (localStorage-backed).
 */
(function () {
  'use strict';
  var KEY = 'itstore_compare';
  var cfg = window.itstoreCompare || { max: 4, url: '' };

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; }
  }
  function save(list) { localStorage.setItem(KEY, JSON.stringify(list)); }

  function ready() {
    var tray = document.querySelector('[data-itstore-cmp-tray]');
    var itemsWrap = tray ? tray.querySelector('[data-cmp-items]') : null;
    var countEl = tray ? tray.querySelector('[data-cmp-count]') : null;

    function render() {
      var list = load();
      // Reflect state on buttons.
      document.querySelectorAll('.js-itstore-cmp').forEach(function (btn) {
        var id = btn.getAttribute('data-cmp-id');
        btn.classList.toggle('is-active', list.some(function (i) { return String(i.id) === String(id); }));
      });
      if (!tray) { return; }
      if (countEl) { countEl.textContent = list.length; }
      if (itemsWrap) {
        itemsWrap.innerHTML = '';
        list.forEach(function (i) {
          var chip = document.createElement('span');
          chip.className = 'itstore-cmp-tray__chip';
          chip.textContent = i.name;
          var x = document.createElement('button');
          x.type = 'button';
          x.setAttribute('aria-label', 'Remove');
          x.innerHTML = '&times;';
          x.addEventListener('click', function () { toggle(i.id, i.name); });
          chip.appendChild(x);
          itemsWrap.appendChild(chip);
        });
      }
      tray.hidden = list.length === 0;
    }

    function toggle(id, name) {
      var list = load();
      var idx = list.findIndex(function (i) { return String(i.id) === String(id); });
      if (idx >= 0) {
        list.splice(idx, 1);
      } else {
        if (list.length >= cfg.max) { return; }
        list.push({ id: id, name: name });
      }
      save(list);
      render();
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest('.js-itstore-cmp') : null;
      if (btn) {
        e.preventDefault();
        toggle(btn.getAttribute('data-cmp-id'), btn.getAttribute('data-cmp-name'));
      }
    });

    if (tray) {
      var go = tray.querySelector('[data-cmp-go]');
      var clr = tray.querySelector('[data-cmp-clear]');
      if (go) {
        go.addEventListener('click', function () {
          var ids = load().map(function (i) { return i.id; });
          if (ids.length < 2) { return; }
          var sep = cfg.url.indexOf('?') === -1 ? '?' : '&';
          window.location.href = cfg.url + sep + 'ids=' + encodeURIComponent(ids.join(','));
        });
      }
      if (clr) { clr.addEventListener('click', function () { save([]); render(); }); }
    }

    render();
  }

  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
