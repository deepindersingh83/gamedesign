# Changelog

All notable changes to the IT Store theme and its modules are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/).

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
