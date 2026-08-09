/**
 * IT Store — PC builder: live total + build summary.
 */
(function () {
  'use strict';
  function ready() {
    var form = document.querySelector('.js-itstore-pb');
    if (!form) { return; }
    var selects = form.querySelectorAll('.js-itstore-pb-select');
    var list = form.querySelector('.js-itstore-pb-list');
    var totalEl = form.querySelector('.js-itstore-pb-total');
    var warning = form.querySelector('.js-itstore-pb-warning');
    var sign = (totalEl && totalEl.textContent.replace(/[0-9.,\s]/g, '')) || '';

    function compatOf(slot) {
      var sel = form.querySelector('.js-itstore-pb-select[data-slot="' + slot + '"]');
      if (!sel) { return null; }
      var opt = sel.options[sel.selectedIndex];
      if (!opt || !opt.value) { return null; }
      return (opt.getAttribute('data-compat') || '').trim();
    }

    function checkCompat() {
      if (!warning) { return; }
      var cpu = compatOf('CPU');
      var mb = compatOf('MB');
      // Only warn when both are chosen, both expose a value, and they differ.
      var mismatch = cpu && mb && cpu.toLowerCase() !== mb.toLowerCase();
      warning.hidden = !mismatch;
    }

    function recompute() {
      var total = 0;
      if (list) { list.innerHTML = ''; }
      Array.prototype.forEach.call(selects, function (sel) {
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { return; }
        var price = parseFloat(opt.getAttribute('data-price')) || 0;
        total += price;
        if (list) {
          var li = document.createElement('li');
          li.textContent = opt.textContent;
          list.appendChild(li);
        }
      });
      if (totalEl) { totalEl.textContent = sign + total.toFixed(2); }
      checkCompat();
    }

    Array.prototype.forEach.call(selects, function (sel) {
      sel.addEventListener('change', recompute);
    });
    recompute();
  }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
