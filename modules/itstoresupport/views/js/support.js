/**
 * support.js
 * ------------------------------------------------------------------
 * Floating support / help widget for the IT Store.
 *
 * Renders a launcher button in the corner of the viewport that opens a
 * panel with quick support actions (live chat, call, email, FAQ search
 * and a contact shortcut). It is deliberately self-contained so it can be
 * dropped into any layout via the `itstoresupport` module.
 *
 * Markup (rendered by the module, hydrated here):
 *   <div class="support-widget" data-support-widget>
 *     <button class="support-widget__launcher" data-support-toggle>...</button>
 *     <div class="support-widget__panel" hidden>
 *       <input class="support-widget__search" data-support-search>
 *       <ul class="support-widget__faq"> <li data-faq>...</li> </ul>
 *       ... quick links ...
 *     </div>
 *   </div>
 * ------------------------------------------------------------------
 */
(function (global) {
  'use strict';

  function SupportWidget(root) {
    this.root = root;
    this.launcher = root.querySelector('[data-support-toggle]');
    this.panel = root.querySelector('.support-widget__panel');
    this.search = root.querySelector('[data-support-search]');
    this.faqItems = Array.prototype.slice.call(root.querySelectorAll('[data-faq]'));
    this.open = false;

    if (!this.launcher || !this.panel) { return; }
    this._bind();
  }

  SupportWidget.prototype._bind = function () {
    this.launcher.addEventListener('click', this.toggle.bind(this));

    var closeBtn = this.root.querySelector('[data-support-close]');
    if (closeBtn) { closeBtn.addEventListener('click', this.close.bind(this)); }

    // Close on Escape.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && this.open) { this.close(); }
    }.bind(this));

    // Close when clicking outside the widget.
    document.addEventListener('click', function (e) {
      if (this.open && !this.root.contains(e.target)) { this.close(); }
    }.bind(this));

    // Live FAQ filtering.
    if (this.search) {
      this.search.addEventListener('input', this._filter.bind(this));
    }
  };

  SupportWidget.prototype._filter = function () {
    var q = (this.search.value || '').trim().toLowerCase();
    var visible = 0;
    this.faqItems.forEach(function (item) {
      var text = (item.textContent || '').toLowerCase();
      var match = q === '' || text.indexOf(q) !== -1;
      item.hidden = !match;
      if (match) { visible++; }
    });
    var empty = this.root.querySelector('[data-support-empty]');
    if (empty) { empty.hidden = visible !== 0; }
  };

  SupportWidget.prototype.toggle = function () {
    this.open ? this.close() : this.show();
  };

  SupportWidget.prototype.show = function () {
    this.open = true;
    this.panel.hidden = false;
    // Force reflow so the CSS transition runs.
    void this.panel.offsetWidth;
    this.root.classList.add('is-open');
    this.launcher.setAttribute('aria-expanded', 'true');
    if (this.search) { this.search.focus(); }
    this.root.dispatchEvent(new CustomEvent('support:open'));
  };

  SupportWidget.prototype.close = function () {
    this.open = false;
    this.root.classList.remove('is-open');
    this.launcher.setAttribute('aria-expanded', 'false');
    var panel = this.panel;
    // Allow the collapse transition to finish before hiding.
    setTimeout(function () { if (!panel.closest('.is-open')) { panel.hidden = true; } }, 250);
    this.root.dispatchEvent(new CustomEvent('support:close'));
  };

  function initAll(context) {
    var scope = context || document;
    var nodes = scope.querySelectorAll('[data-support-widget]');
    Array.prototype.forEach.call(nodes, function (node) {
      if (!node.__supportWidget) {
        node.__supportWidget = new SupportWidget(node);
      }
    });
  }

  global.SupportWidget = SupportWidget;
  global.SupportWidget.initAll = initAll;

  if (document.readyState !== 'loading') {
    initAll();
  } else {
    document.addEventListener('DOMContentLoaded', function () { initAll(); });
  }
})(window);
