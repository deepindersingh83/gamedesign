/**
 * image-slot.js
 * ------------------------------------------------------------------
 * A lightweight, dependency-free image slot / slider component.
 *
 * An "image slot" is a container that holds one or more image slides and
 * cross-fades (or slides) between them. It is used for the IT Store hero
 * banner and for any promotional carousel in the storefront.
 *
 * Markup:
 *   <div class="image-slot" data-image-slot data-interval="6000" data-effect="slide">
 *     <div class="image-slot__track">
 *       <div class="image-slot__slide">
 *         <img data-src="banner-1.jpg" alt="...">
 *         <div class="image-slot__caption">...</div>
 *       </div>
 *       ...
 *     </div>
 *     <button class="image-slot__nav image-slot__nav--prev" aria-label="Previous"></button>
 *     <button class="image-slot__nav image-slot__nav--next" aria-label="Next"></button>
 *     <div class="image-slot__dots"></div>
 *   </div>
 *
 * Images may use `data-src` for lazy loading; the slot loads the current
 * and adjacent slides only.
 * ------------------------------------------------------------------
 */
(function (global) {
  'use strict';

  function ImageSlot(root, options) {
    this.root = root;
    this.opts = Object.assign({
      interval: parseInt(root.getAttribute('data-interval'), 10) || 6000,
      effect: root.getAttribute('data-effect') || 'slide',
      autoplay: root.getAttribute('data-autoplay') !== 'false',
      loop: root.getAttribute('data-loop') !== 'false'
    }, options || {});

    this.track = root.querySelector('.image-slot__track');
    this.slides = Array.prototype.slice.call(root.querySelectorAll('.image-slot__slide'));
    this.index = 0;
    this.timer = null;
    this.pointer = { down: false, startX: 0, dx: 0 };

    if (this.slides.length === 0) { return; }

    root.classList.add('image-slot--' + this.opts.effect);
    this._buildDots();
    this._bind();
    this._go(0, true);
    if (this.opts.autoplay && this.slides.length > 1) { this.play(); }
  }

  ImageSlot.prototype._buildDots = function () {
    this.dotsWrap = this.root.querySelector('.image-slot__dots');
    if (!this.dotsWrap) { return; }
    this.dots = this.slides.map(function (_, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'image-slot__dot';
      b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
      b.addEventListener('click', this._go.bind(this, i, false));
      this.dotsWrap.appendChild(b);
      return b;
    }.bind(this));
  };

  ImageSlot.prototype._bind = function () {
    var prev = this.root.querySelector('.image-slot__nav--prev');
    var next = this.root.querySelector('.image-slot__nav--next');
    if (prev) { prev.addEventListener('click', this.prev.bind(this)); }
    if (next) { next.addEventListener('click', this.next.bind(this)); }

    // Pause on hover.
    this.root.addEventListener('mouseenter', this.pause.bind(this));
    this.root.addEventListener('mouseleave', this._resume.bind(this));

    // Keyboard support.
    this.root.setAttribute('tabindex', '0');
    this.root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { this.prev(); }
      if (e.key === 'ArrowRight') { this.next(); }
    }.bind(this));

    // Touch / pointer swipe.
    if (this.track) {
      this.track.addEventListener('pointerdown', this._onDown.bind(this));
      window.addEventListener('pointermove', this._onMove.bind(this));
      window.addEventListener('pointerup', this._onUp.bind(this));
    }

    // Pause when tab is hidden.
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { this.pause(); } else { this._resume(); }
    }.bind(this));
  };

  ImageSlot.prototype._lazyLoad = function (i) {
    [i - 1, i, i + 1].forEach(function (n) {
      var slide = this.slides[(n + this.slides.length) % this.slides.length];
      if (!slide) { return; }
      var img = slide.querySelector('img[data-src]');
      if (img) {
        img.src = img.getAttribute('data-src');
        img.removeAttribute('data-src');
      }
    }.bind(this));
  };

  ImageSlot.prototype._go = function (i, immediate) {
    var total = this.slides.length;
    if (this.opts.loop) {
      i = (i + total) % total;
    } else {
      i = Math.max(0, Math.min(total - 1, i));
    }
    this.index = i;
    this._lazyLoad(i);

    if (this.opts.effect === 'slide' && this.track) {
      this.track.style.transition = immediate ? 'none' : '';
      this.track.style.transform = 'translateX(' + (-i * 100) + '%)';
    }
    this.slides.forEach(function (s, n) {
      s.classList.toggle('is-active', n === i);
      s.setAttribute('aria-hidden', n === i ? 'false' : 'true');
    });
    if (this.dots) {
      this.dots.forEach(function (d, n) { d.classList.toggle('is-active', n === i); });
    }
    this.root.dispatchEvent(new CustomEvent('imageslot:change', { detail: { index: i } }));
  };

  ImageSlot.prototype.next = function () { this._go(this.index + 1, false); this._restart(); };
  ImageSlot.prototype.prev = function () { this._go(this.index - 1, false); this._restart(); };

  ImageSlot.prototype.play = function () {
    this.pause();
    this.timer = setInterval(this.next.bind(this), this.opts.interval);
  };
  ImageSlot.prototype.pause = function () {
    if (this.timer) { clearInterval(this.timer); this.timer = null; }
  };
  ImageSlot.prototype._resume = function () {
    if (this.opts.autoplay && !this.timer && this.slides.length > 1) { this.play(); }
  };
  ImageSlot.prototype._restart = function () {
    if (this.timer) { this.play(); }
  };

  // --- pointer/swipe handlers ---
  ImageSlot.prototype._onDown = function (e) {
    this.pointer.down = true;
    this.pointer.startX = e.clientX;
    this.pointer.dx = 0;
    this.pause();
  };
  ImageSlot.prototype._onMove = function (e) {
    if (!this.pointer.down) { return; }
    this.pointer.dx = e.clientX - this.pointer.startX;
  };
  ImageSlot.prototype._onUp = function () {
    if (!this.pointer.down) { return; }
    this.pointer.down = false;
    var threshold = Math.max(40, this.root.offsetWidth * 0.15);
    if (this.pointer.dx > threshold) { this.prev(); }
    else if (this.pointer.dx < -threshold) { this.next(); }
    else { this._resume(); }
  };

  // Auto-initialise all image slots on the page.
  function initAll(context) {
    var scope = context || document;
    var nodes = scope.querySelectorAll('[data-image-slot]');
    Array.prototype.forEach.call(nodes, function (node) {
      if (!node.__imageSlot) {
        node.__imageSlot = new ImageSlot(node);
      }
    });
  }

  global.ImageSlot = ImageSlot;
  global.ImageSlot.initAll = initAll;

  if (document.readyState !== 'loading') {
    initAll();
  } else {
    document.addEventListener('DOMContentLoaded', function () { initAll(); });
  }
})(window);
