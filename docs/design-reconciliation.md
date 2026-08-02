# IT Store — design reconciliation checklist

This tracks every point where the current theme was built to a **reasonable
IT-store interpretation** rather than to the source design
(`IT Store Mockups.dc.html`, `image-slot.js`, `support.js`). Work through it with
the mockup open, fill the **Design value** column, apply the change to the listed
file(s), and tick the box.

> The current values below are what the theme ships today — they are the deltas
> to verify, not confirmed matches.

## How to use
1. Open the mockup and the referenced files side by side.
2. For each row, record the real design value, then edit the file(s).
3. Keep `php -l` / `node --check` clean; the CI workflows enforce this.
4. Commit to `claude/prestashop-theme-it-store-xncfvt` so PR #1 updates.

---

## 1. Brand & visual language

| # | Item | Current value (to verify) | Design value | File(s) | Done |
|---|------|---------------------------|--------------|---------|------|
| 1 | Primary palette | navy `#0b1f33`, blue `#1f6feb`, cyan `#22d3ee`, orange `#ff7a18`, ink `#0f172a` | | `themes/itstore/assets/css/custom.css` (`:root` vars) | ☐ |
| 2 | Dark-mode palette | derived overrides | | `custom.css` `@media (prefers-color-scheme: dark)` | ☐ |
| 3 | Typography | `Inter` / system stack; weights 400–800 | | `custom.css` `--it-font` + `assets/fonts/` | ☐ |
| 4 | Type scale | `section-title` 24px, hero 28–46px clamp | | `custom.css`, `modules/itstoreimageslot/views/css/imageslot.css` | ☐ |
| 5 | Radius / shadow tokens | radius 12px/8px, soft shadows | | `custom.css` `:root` | ☐ |
| 6 | Logo / wordmark | placeholder "IT**Store**" + generated PNG | | `themes/itstore/assets/img/logo*.png`, `templates/_partials/footer.tpl` | ☐ |
| 7 | Favicon | generated placeholder | | `themes/itstore/assets/img/favicon.png` | ☐ |
| 8 | Icon set | hand-inlined SVGs | | trustbar / finance / warranty / quickview / compare templates | ☐ |
| 9 | Button system | fill/radius/hover in `.btn*` | | `custom.css` `.btn` group | ☐ |

## 2. Layout & structure

| # | Item | Current approach | Design | File(s) | Done |
|---|------|------------------|--------|---------|------|
| 10 | **Header** | NOT overridden — classic markup restyled via CSS | | needs `templates/_partials/header.tpl` override | ☐ |
| 11 | Utility/top bar | none (contact via classic `ps_contactinfo`) | | header override / `itstoretrustbar` | ☐ |
| 12 | Hero (image-slot) | full-bleed slider, left caption, cyan eyebrow, orange CTA | | `modules/itstoreimageslot/views/templates/hook/imageslot.tpl` + css | ☐ |
| 13 | Home section order | hero → categories → PC-builder CTA → featured → deals → brands → banner → text | | `themes/itstore/theme.yml` (`displayHome`) | ☐ |
| 14 | Home sections that may be missing | testimonials, stats/counters, "why choose us", promo grid, blog/news, brand story | | new module(s) if the design has them | ☐ |
| 15 | Trust/USP bar | 4 items, placeholder copy/icons | | `modules/itstoretrustbar/*` | ☐ |
| 16 | Product card | classic miniature restyled via CSS | | needs `templates/catalog/_partials/miniatures/product.tpl` | ☐ |
| 17 | Category / listing | left-column facets (classic) | | `theme.yml` layout + CSS | ☐ |
| 18 | Product page (PDP) | classic layout + extra tabs; finance/stock/warranty in `displayProductAdditionalInfo`; spec/reviews tabs; bundles in footer | | hook positions in `theme.yml` + module templates | ☐ |
| 19 | Footer | columns + gradient newsletter + copyright lockup | | `templates/_partials/footer.tpl`, `custom.css` footer | ☐ |
| 20 | Mega-menu | departments + promo panel | | `modules/itstoremegamenu/*` | ☐ |

## 3. Content & behaviour

| # | Item | Current | Design | File(s) | Done |
|---|------|---------|--------|---------|------|
| 21 | All copy | placeholder headings/CTAs/FAQ/warranty tiers/finance terms | | module templates + BO config defaults | ☐ |
| 22 | `image-slot.js` | faithful re-implementation (`window.ImageSlot`) | reconcile 1:1 | `themes/itstore/assets/js/image-slot.js`, `modules/itstoreimageslot/views/js/image-slot.js` | ☐ |
| 23 | `support.js` | faithful re-implementation (`window.SupportWidget`) | reconcile 1:1 | `themes/itstore/assets/js/support.js`, `modules/itstoresupport/views/js/support.js` | ☐ |
| 24 | Support widget presence | assumed floating launcher + FAQ | confirm it exists in design | `modules/itstoresupport/*` | ☐ |
| 25 | Micro-interactions | reveal-on-scroll, hover lifts, slider timing 6s | | `custom.js`, module JS/CSS | ☐ |
| 26 | Responsive / mobile nav | own breakpoints; mega-menu accordion | | `custom.css`, `megamenu.css/js` | ☐ |
| 27 | Imagery / art direction | Unsplash demo slides | | `itstoreimageslot` default slides, product imagery | ☐ |
| 28 | Bespoke components unique to mockup | none accounted for | | new module(s) as needed | ☐ |

---

## Notes on the two highest-risk structural items

- **Header override (#10/#11):** deliberately not hand-written blind — a wrong
  reproduction of the classic 9 header can silently break search, cart, or the
  menu without a 500 (so CI wouldn't catch it). Build it against the mockup on a
  running store and verify the header interactions manually.
- **Miniature override (#16):** the classic product card carries flags, variants,
  quick-add and rating hooks; replace it only with the mockup card in front of
  you so no commerce feature is lost. The quick-view and compare buttons are
  already injected via the `displayProductListReviews` hook and can move into the
  card markup during that override.
