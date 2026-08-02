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
    var sign = (totalEl && totalEl.textContent.replace(/[0-9.,\s]/g, '')) || '';

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
    }

    Array.prototype.forEach.call(selects, function (sel) {
      sel.addEventListener('change', recompute);
    });
    recompute();
  }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
