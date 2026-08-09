/**
 * IT Store — theme behaviour.
 * Progressive enhancements shared across the storefront. Everything here is
 * additive and guarded, so a page that lacks a target simply gets nothing.
 */
(function () {
  'use strict';

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  /* Sticky header shadow on scroll. */
  function stickyHeader() {
    var header = document.getElementById('header');
    if (!header) { return; }
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Reveal-on-scroll for elements marked with [data-reveal]. */
  function revealOnScroll() {
    var reveals = document.querySelectorAll('[data-reveal]');
    if (!reveals.length || !('IntersectionObserver' in window)) { return; }
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

  /* Accessibility: inject a skip-to-content link as the first focusable element. */
  function skipLink() {
    if (document.querySelector('.itstore-skip')) { return; }
    var target = document.getElementById('content') || document.getElementById('main') || document.querySelector('main');
    if (target && !target.id) { target.id = 'content'; }
    var a = document.createElement('a');
    a.className = 'itstore-skip';
    a.href = '#' + (target && target.id ? target.id : 'content');
    a.textContent = 'Skip to content';
    document.body.insertBefore(a, document.body.firstChild);
  }

  /* Back-to-top button. */
  function backToTop() {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'itstore-totop';
    btn.setAttribute('aria-label', 'Back to top');
    btn.innerHTML = '<span aria-hidden="true">↑</span>';
    document.body.appendChild(btn);

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var onScroll = function () {
      btn.classList.toggle('is-visible', window.scrollY > 600);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  /* Optional mobile nav toggle — only wires up if a known menu + toggle exist. */
  function mobileNav() {
    var nav = document.querySelector('[data-itstore-mobile-nav]') || document.querySelector('.itstore-mega');
    var toggle = document.querySelector('[data-itstore-nav-toggle]');
    if (!nav || !toggle) { return; }
    toggle.addEventListener('click', function () {
      var open = document.body.classList.toggle('itstore-nav-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('itstore-nav-open')) {
        document.body.classList.remove('itstore-nav-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  onReady(function () {
    stickyHeader();
    revealOnScroll();
    skipLink();
    backToTop();
    mobileNav();
  });
})();
