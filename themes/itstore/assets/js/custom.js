/**
 * IT Store — theme behaviour.
 * Progressive enhancements shared across the storefront.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // Sticky header shadow on scroll.
    var header = document.getElementById('header');
    if (header) {
      var onScroll = function () {
        header.classList.toggle('is-scrolled', window.scrollY > 8);
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }

    // Reveal-on-scroll for elements marked with [data-reveal].
    var reveals = document.querySelectorAll('[data-reveal]');
    if (reveals.length && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      reveals.forEach(function (el) { io.observe(el); });
    }
  });
})();
