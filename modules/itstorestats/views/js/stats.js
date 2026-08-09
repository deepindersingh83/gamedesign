/** IT Store — stats count-up on scroll (numeric values only). */
(function () {
  'use strict';
  function animate(el) {
    var raw = el.textContent;
    var m = raw.match(/^([^\d]*)([\d.,]+)(.*)$/);
    if (!m) { return; }
    var prefix = m[1], target = parseFloat(m[2].replace(/,/g, '')), suffix = m[3];
    var decimals = (m[2].split('.')[1] || '').length;
    var start = null, dur = 900;
    function step(ts) {
      if (start === null) { start = ts; }
      var p = Math.min((ts - start) / dur, 1);
      var val = (target * (0.2 + 0.8 * p));
      el.textContent = prefix + val.toFixed(decimals) + suffix;
      if (p < 1) { requestAnimationFrame(step); } else { el.textContent = raw; }
    }
    requestAnimationFrame(step);
  }
  function ready() {
    var band = document.querySelector('[data-itstore-stats]');
    if (!band) { return; }
    var values = band.querySelectorAll('[data-stat-value]');
    if (!('IntersectionObserver' in window)) { return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          values.forEach(animate);
          io.disconnect();
        }
      });
    }, { threshold: 0.4 });
    io.observe(band);
  }
  if (document.readyState !== 'loading') { ready(); }
  else { document.addEventListener('DOMContentLoaded', ready); }
})();
