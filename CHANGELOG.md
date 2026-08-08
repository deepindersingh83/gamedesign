# Changelog

All notable changes to the IT Store theme and its modules are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/).

## [1.6.0] - 2026-08-08

### Theme
- **Page coverage**: branded 404 page plus design-matched styling carried
  across the product (sticky desktop buy-box), cart, checkout, account and
  search pages.
- **Accessibility**: skip-to-content link, visible focus-visible ring on all
  interactive elements, a back-to-top button and a guarded mobile-nav toggle.
- **Performance**: preload the body + heading woff2 (via the itstoreseo head
  hook, theme-guarded) so first paint doesn't wait on the font fetch.
- **CSS/build**: spacing/radius/shadow/type token scale + opt-in utilities;
  a dependency-free CSS minifier (`build/minify-css.mjs`).
- **Brand assets**: real branded app icons (apple-touch-icon + 192/512 PWA
  icons) and a web manifest with `theme-color`; removed the stale
  `preview.svg`; branded customer-service email overrides (contact, reply_msg).
- **theme.yml**: added `itstore_wide` (1200×480) and `itstore_tile` (600×600)
  image types.

## [1.5.0] - 2026-08-08

### Added
- **`itstorefaq`** — categorised FAQ: `itstore_faq` table + ObjectModel, a
  back-office CRUD tab (`AdminItstoreFaq`), an accordion front page grouped by
  category and FAQPage JSON-LD for rich results.
- **`itstorecookies`** — dependency-free cookie-consent banner (accept/decline,
  first-party cookie, configurable text + CMS policy link) that dispatches an
  `itstore:consent` event for gating trackers.
- **`itstorerecentcompare`** — a "Recently compared" strip built from the
  products a visitor runs through the compare page (localStorage, capped &
  de-duplicated), hidden on the compare page itself and when empty.
- **Bundle → cart**: `itstorebundles` gains a combined total and an "Add all to
  cart" action (new `addbundle` controller) that adds the main product plus its
  accessories in one go, skipping unavailable items.
- **Blog**: categories, comma-separated tags, author and a "Related articles"
  grid, plus a category filter bar, tag-filtered listings and an RSS 2.0 feed.
- **Wishlist**: themed styling for the native `blockwishlist` button, listing,
  empty state and toast.

### Changed
- **Removed all demo/fabricated content**: emptied the seeded testimonials and
  stats band (they self-hide until configured) and dropped every Unsplash
  hotlink (hero falls back to its gradient; image-slot seeds text-on-gradient).

## [1.4.0] - 2026-08-07

### Added
- **Real blog CMS** (`itstoreblog`): `itstore_blog_post` table + `ItstoreBlogPost`
  ObjectModel, a back-office CRUD tab (`AdminItstoreBlog`), front listing and
  article controllers (with pagination, breadcrumbs, per-post meta and Article
  JSON-LD) and starter posts seeded on install. The "From the Blog" home block
  now shows the latest published posts (teaser cards fall back in when empty).
- **Admin hub** (`itstoreadmin`): a single "IT Store" back-office tab grouping
  every itstore* module — live KPI counters (pending reviews, unanswered
  questions, quote requests, waiting stock alerts, active subscriptions, blog
  posts) and a status/configure grid.
- **Security headers** (`itstoresecurity`): configurable nosniff, frame,
  referrer & permissions policies, optional HSTS and an optional (Report-Only
  by default) CSP.
- **Gallery zoom** (`itstoregallery`): product-page hover-magnifier and a
  full-screen lightbox with prev/next, arrow-key and Esc navigation.
- **Quote → order**: convert a fleet quote into a draft cart owned by the
  requester and jump into PrestaShop's native create-order screen; the request
  text rides along as a private cart note.
- **Anti-spam** honeypot + time-trap on the quote, ask-a-question, review and
  stock-alert forms (silent-drop).
- **GDPR**: `actionExportGDPRData` / `actionDeleteGDPRData` on every module that
  stores personal data (reviews, questions, quotes, stock alerts, subscriptions).
- **CI**: Playwright storefront screenshots + a Lighthouse performance budget,
  both uploaded as artifacts; a real 1110×900 branded `preview.png`.
- Sample **fr-FR** XLIFF catalogues for the most visible storefront strings.

### Changed
- **Performance**: every module script now loads with `defer`; below-the-fold
  images lazy-load and async-decode; the hero LCP image is `fetchpriority=high`.

## [1.3.0] - 2026-08-06

### Added
- `itstoreseo` (Organization/WebSite/Breadcrumb JSON-LD), `itstoretopbar`
  (header utility bar), `itstoreordertrack` (guest order lookup), `itstorerecent`
  (Recommended + Recently Viewed), `itstoresavedcart` (restore a saved cart),
  and a printable **Download Spec Sheet** in `itstorespecsheet`.
- `itstoreautoreorder` upgraded to a real subscription workflow: `itstore_subscription`
  table, AJAX subscribe, "My subscriptions" account page, and a token-protected
  reminder cron with a themed reorder email (reminder-based reorder — not
  stored-card auto-charge).
- Category page: dark banner + "About" block via a minimal `category.tpl` override
  that extends the parent product-list (facets/products untouched).
- Native `blockwishlist` + `psgdpr` enabled via `theme.yml`.

### Changed
- **Self-hosted fonts**: Inter + JetBrains Mono woff2 (latin) bundled in
  `assets/fonts` with `assets/css/fonts.css`; dropped the Google Fonts request.
- **i18n**: migrated module classes to `$this->trans(..., 'Modules.<Name>.Admin')`
  and templates to `{l s='…' d='Modules.<Name>.Shop'}` (the PS 8/9 idiom).
  `$this->l()` still worked on 9.1 (CI-verified); this is future-proofing.
- **Verified on PrestaShop 9.1** end-to-end: all modules install and the front
  office passes smoke tests (integration CI green).

## [1.2.0] - 2026-08-05

### Added — B2B sections from the mockup
- `itstorehero` (audience-tabbed Deal-of-the-Month hero), `itstorefleetdeals`
  (Business Fleet Deals + Clearance + Request-Quote controller/table),
  `itstorestats` (count-up stats band), `itstoretestimonials`, `itstoreblog`,
  `itstorebulkpricing`, `itstoreautoreorder`, `itstoreaskquestion` (Q&A tab).
- Home order re-composed to match the design; hero replaces the image slider.

### Changed
- Palette/type reconciled to the real mockup (ink `#0b1220`, blue `#2f6fed`,
  Inter + JetBrains Mono + Source Serif 4); product cards, badges, buttons and
  the out-of-stock notify box restyled to the design.
- Recorded that `image-slot.js` / `support.js` are Design-Composer tooling, not
  storefront libraries (so they are intentionally not shipped).

## [1.1.0] - 2026-08-02

### Added
- Ten feature modules: `itstoremegamenu`, `itstorepcbuilder`, `itstorecompare`,
  `itstorequickview`, `itstorespecsheet`, `itstorereviews`, `itstorefinance`,
  `itstorestock`, `itstorewarranty`, `itstorebundles`.
- Theme header/footer/product-miniature template overrides carrying the IT
  Store branding.
- RTL stylesheet (`assets/css/custom-rtl.css`).
- Translation scaffolding (`.xlf` catalogues) for the theme and modules.
- Brand assets: `preview.png`, `assets/img/logo.png`, `logo-white.png`,
  `favicon.png`, and a `logo.png` for every module.
- Back-in-stock email sender (`itstorestock` `cron` controller).
- `AggregateRating` / `Review` JSON-LD in `itstorereviews`.
- Basic component compatibility warnings in `itstorepcbuilder`.
- Project tooling: MIT `LICENSE`, packaging script (`build/package-theme.sh`),
  a GitHub Actions lint workflow, and an integration workflow that installs all
  modules onto a live PrestaShop 9.1 + MySQL and smoke-tests the front office.
- Dark-mode support and accessibility refinements in the theme CSS.

### Changed
- Self-hosted / system font stack replaces the external Google Fonts `@import`
  (removes the third-party request / GDPR concern).
- `custom.css` retargeted to the theme's own header/footer markup.
- Compatibility ceiling raised to PrestaShop 9.1.x across the theme and all
  modules.

## [1.0.0] - 2026-08-02

### Added
- IT Store theme (child of `classic`) with `itstoreimageslot`, `itstoresupport`,
  `itstoretrustbar`, `itstorecategoriesblock`, `itstoredeals`, `itstorebrands`.
