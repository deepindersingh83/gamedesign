/**
 * IT Store — product gallery zoom & lightbox (dependency-free).
 *
 * Enhances the classic/child-theme product page:
 *  - desktop hover-magnifier over the main cover image
 *  - click to open a full-screen lightbox that cycles through every image
 *    (thumbnails + cover), with prev/next buttons, arrow keys and Esc.
 *
 * It reads the images the theme already rendered, so it works whatever the
 * product's image set is, and it re-binds when the theme swaps the cover
 * (colour/combination change) via a MutationObserver.
 */
(function () {
  'use strict';

  var ZOOM = 2.4;

  function q(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  function getCover() {
    return q('.product-cover img.js-qv-product-cover') ||
           q('.product-cover img') ||
           q('#product .images-container img');
  }

  // Collect the highest-resolution URL for every gallery image (cover + thumbs).
  function collectImages() {
    var urls = [];
    var seen = {};
    var push = function (u) {
      if (!u || seen[u]) { return; }
      seen[u] = 1;
      urls.push(u);
    };
    var cover = getCover();
    if (cover) { push(cover.getAttribute('data-image-large-src') || cover.getAttribute('src')); }
    qa('.js-qv-product-images img, .product-images img, .js-thumb').forEach(function (img) {
      push(img.getAttribute('data-image-large-src') || img.getAttribute('src'));
    });
    return urls;
  }

  /* ---------------- hover magnifier ---------------- */

  function bindZoom(cover) {
    if (!cover || cover.dataset.itstoreZoom === '1') { return; }
    cover.dataset.itstoreZoom = '1';

    var wrap = cover.closest('.product-cover') || cover.parentElement;
    if (!wrap) { return; }
    wrap.classList.add('itstore-zoom-wrap');

    var lens = document.createElement('div');
    lens.className = 'itstore-zoom-lens';
    lens.style.backgroundImage = 'url("' + (cover.getAttribute('data-image-large-src') || cover.src) + '")';
    wrap.appendChild(lens);

    var onMove = function (e) {
      var rect = cover.getBoundingClientRect();
      var x = (e.clientX - rect.left) / rect.width;
      var y = (e.clientY - rect.top) / rect.height;
      if (x < 0 || x > 1 || y < 0 || y > 1) { lens.style.opacity = '0'; return; }
      lens.style.opacity = '1';
      lens.style.backgroundSize = (rect.width * ZOOM) + 'px ' + (rect.height * ZOOM) + 'px';
      lens.style.backgroundPosition = (x * 100) + '% ' + (y * 100) + '%';
    };

    // Keep the lens image in sync when the theme swaps the cover.
    var refresh = function () {
      lens.style.backgroundImage = 'url("' + (cover.getAttribute('data-image-large-src') || cover.src) + '")';
    };

    if (window.matchMedia && window.matchMedia('(pointer: fine)').matches) {
      wrap.addEventListener('mousemove', onMove);
      wrap.addEventListener('mouseleave', function () { lens.style.opacity = '0'; });
      cover.addEventListener('load', refresh);
    }
    cover.addEventListener('click', function (e) { e.preventDefault(); openLightbox(collectImages(), 0, cover); });
  }

  /* ---------------- lightbox ---------------- */

  var lb;

  function buildLightbox() {
    lb = document.createElement('div');
    lb.className = 'itstore-lb';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.innerHTML =
      '<button class="itstore-lb__close" type="button" aria-label="Close">&times;</button>' +
      '<button class="itstore-lb__nav itstore-lb__prev" type="button" aria-label="Previous">&#8249;</button>' +
      '<img class="itstore-lb__img" alt="">' +
      '<button class="itstore-lb__nav itstore-lb__next" type="button" aria-label="Next">&#8250;</button>' +
      '<div class="itstore-lb__count"></div>';
    document.body.appendChild(lb);

    q('.itstore-lb__close', lb).addEventListener('click', closeLightbox);
    q('.itstore-lb__prev', lb).addEventListener('click', function () { step(-1); });
    q('.itstore-lb__next', lb).addEventListener('click', function () { step(1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) { closeLightbox(); } });
    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('is-open')) { return; }
      if (e.key === 'Escape') { closeLightbox(); }
      else if (e.key === 'ArrowLeft') { step(-1); }
      else if (e.key === 'ArrowRight') { step(1); }
    });
  }

  var state = { images: [], index: 0, opener: null };

  function render() {
    var img = q('.itstore-lb__img', lb);
    img.src = state.images[state.index];
    q('.itstore-lb__count', lb).textContent = (state.index + 1) + ' / ' + state.images.length;
    var multi = state.images.length > 1;
    q('.itstore-lb__prev', lb).style.display = multi ? '' : 'none';
    q('.itstore-lb__next', lb).style.display = multi ? '' : 'none';
  }

  function step(d) {
    state.index = (state.index + d + state.images.length) % state.images.length;
    render();
  }

  function openLightbox(images, index, opener) {
    if (!images || !images.length) { return; }
    if (!lb) { buildLightbox(); }
    state.images = images;
    state.index = index || 0;
    state.opener = opener || null;
    render();
    lb.classList.add('is-open');
    document.body.classList.add('itstore-lb-open');
    q('.itstore-lb__close', lb).focus();
  }

  function closeLightbox() {
    if (!lb) { return; }
    lb.classList.remove('is-open');
    document.body.classList.remove('itstore-lb-open');
    if (state.opener && state.opener.focus) { state.opener.focus(); }
  }

  /* ---------------- boot + re-bind on cover swaps ---------------- */

  function init() {
    var cover = getCover();
    if (cover) { bindZoom(cover); }

    var container = q('#product .images-container') || q('.product-cover') || document.body;
    if (container && window.MutationObserver) {
      var obs = new MutationObserver(function () {
        var c = getCover();
        if (c) { bindZoom(c); }
      });
      obs.observe(container, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
