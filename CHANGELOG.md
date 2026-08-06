# Changelog

All notable changes to the IT Store theme and its modules are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/).

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
