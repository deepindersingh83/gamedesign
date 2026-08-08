/**
 * IT Store — Recently Compared (localStorage-backed).
 *
 * On the compare page the module renders a data-rc-capture payload of the
 * compared products; we merge those into a capped, de-duplicated localStorage
 * list. On every page we render the strip from that list (hidden when empty or
 * on the compare page itself).
 */
(function () {
  'use strict';

  var KEY = 'itstore_recent_compare';
  var MAX = 12;

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; }
  }
  function save(list) {
    try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, MAX))); } catch (e) { /* quota */ }
  }

  function merge(existing, incoming) {
    var byId = {};
    var out = [];
    // Newest (incoming) first, then previous entries.
    incoming.concat(existing).forEach(function (card) {
      if (!card || !card.id || byId[card.id]) { return; }
      byId[card.id] = true;
      out.push(card);
    });
    return out;
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function render(root, list) {
    var track = root.querySelector('.itstore-rc__track');
    if (!track) { return; }
    track.innerHTML = list.map(function (c) {
      return '<a class="itstore-rc__card" href="' + esc(c.url) + '">' +
        (c.image ? '<span class="itstore-rc__media"><img src="' + esc(c.image) + '" alt="' + esc(c.name) + '" loading="lazy" decoding="async"></span>' : '') +
        '<span class="itstore-rc__name">' + esc(c.name) + '</span>' +
        (c.price ? '<span class="itstore-rc__price">' + esc(c.price) + '</span>' : '') +
        '</a>';
    }).join('');
  }

  function init() {
    var root = document.querySelector('.itstore-rc');
    if (!root) { return; }

    var list = load();

    var raw = root.getAttribute('data-rc-capture');
    if (raw) {
      try {
        var incoming = JSON.parse(raw);
        if (incoming && incoming.length) {
          list = merge(list, incoming);
          save(list);
        }
      } catch (e) { /* ignore malformed payload */ }
    }

    // Don't show the strip on the compare page itself, or when empty.
    if (root.getAttribute('data-rc-hide') === '1' || !list.length) {
      root.hidden = true;
      return;
    }

    render(root, list);
    root.hidden = false;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
