# IT Store — PrestaShop theme

A modern, tech-focused storefront theme for **PrestaShop 1.7.6 – 9.1.x**, built
from the *IT Store Mockups* design. It ships as a child of the `classic` theme
plus a suite of companion `itstore*` modules that implement the interactive and
commercial features of an IT/tech store.

**Compatibility:** every module declares `ps_versions_compliancy` `1.7.6.0 –
9.99.99` and the theme's `theme.yml` sets `compatibility.to: 9.99.99`. Modules
target PHP 8.1+ and avoid APIs removed in PrestaShop 9 — prices are formatted via
`Context::getCurrentLocale()->formatPrice()` (with a `Tools::displayPrice`
fallback for older installs), product tabs use the modern
`displayProductExtraContent` / `ProductExtraContent` API, and all front
controllers extend `ModuleFrontController`.

> **Note on the source design:** this implementation was built against the
> *IT Store Mockups* Claude Design project. The design files
> (`IT Store Mockups.dc.html`, `image-slot.js`, `support.js`) could not be read
> automatically from this environment because Claude Design access requires an
> interactive login. The `image-slot.js` and `support.js` behaviours here are
> faithful, self-contained re-implementations based on their names/roles in the
> selection. If you re-run after using Claude Design's *"Send to Claude Code
> Web"* (which seeds the project files into the workspace), the components can be
> reconciled 1:1 with the originals.

## What's in here

```
themes/itstore/                     # the IT Store theme (child of classic)
  theme.yml                         # manifest: layouts, hooks, image types, module wiring
  config/theme.yml                  # runtime theme settings
  assets/css/custom.css             # IT Store identity + dark mode + a11y (auto-loaded)
  assets/css/custom-rtl.css         # right-to-left overrides (auto-loaded when RTL)
  assets/js/custom.js               # small progressive enhancements
  assets/js/image-slot.js           # hero slider component (reference copy)
  assets/js/support.js              # support widget component (reference copy)
  assets/img/                       # logo.png, logo-white.png, favicon.png
  assets/fonts/                     # self-hosted font drop-in (README)
  templates/index.tpl               # home page (composes displayHome hooks)
  templates/_partials/footer.tpl    # footer override with IT Store copyright
  mails/                            # theme email-override guide
  preview.png                       # back-office preview thumbnail (905×600)
  preview.svg                       # vector source of the preview

build/package-theme.sh              # assembles dist/itstore.zip with bundled modules
.github/workflows/lint.yml          # CI: php -l, node --check, YAML validation
LICENSE  CHANGELOG.md               # MIT licence + changelog

modules/itstoreimageslot/           # hero "image slot" slider (displayHome)
modules/itstorecategoriesblock/     # "shop by category" tiles (displayHome)
modules/itstoredeals/               # deals / price-drop block (displayHome)
modules/itstorebrands/              # brand / manufacturer logo strip (displayHome)
modules/itstoretrustbar/            # site-wide trust / USP bar (displayWrapperTop)
modules/itstoresupport/             # floating support / help widget (displayFooterAfter)
modules/itstoremegamenu/            # department mega-menu (displayTop)
modules/itstorepcbuilder/           # custom-PC builder + home CTA (front controller)
modules/itstorecompare/             # product comparison tray + page (front controller)
modules/itstorequickview/           # quick-view modal on listings (displayProductListReviews)
modules/itstorespecsheet/           # spec table tab (displayProductExtraContent)
modules/itstorereviews/             # verified-buyer reviews tab (displayProductExtraContent)
modules/itstorefinance/             # "from $x/month" messaging (displayProductAdditionalInfo)
modules/itstorestock/               # stock indicator + back-in-stock alerts (front controller)
modules/itstorewarranty/            # warranty upsell (displayProductAdditionalInfo)
modules/itstorebundles/             # frequently bought together (displayFooterProduct)
```

All sixteen `itstore*` modules are custom to this theme and auto-enabled via
`theme.yml`. The theme also uses PrestaShop's own bundled modules (`ps_mainmenu`,
`ps_searchbar`, `ps_shoppingcart`, `ps_featuredproducts`, `ps_facetedsearch`,
`ps_emailsubscription`, `ps_linklist`, `ps_contactinfo`, …) — those ship with
PrestaShop and only need enabling, which `theme.yml` handles.

## Theme

* **Parent theme:** `classic`. `index.tpl` and `_partials/footer.tpl` are
  overridden; every other template falls back to the parent, and `custom.css`
  restyles the existing classic markup (`#header`, `#footer`, `.product-miniature`,
  `.block_newsletter`, …) with the IT Store identity (navy + blue/cyan + orange
  accent). Ships **dark-mode**, **RTL**, **print** and **reduced-motion** styles.
* **Layouts:** full-width home, left-column listings.
* **Home composition** (via `displayHome` in `theme.yml`): image-slot hero →
  category tiles → PC-builder CTA → featured products → deals → brands → banner →
  custom text.

### Install the theme

**Recommended — one zip with everything bundled:**

1. Run `bash build/package-theme.sh` → produces `dist/itstore.zip`. The script
   copies every `itstore*` module into the theme's `dependencies/modules/` so
   PrestaShop installs them automatically on import.
2. Back office → **Design → Theme & Logo → Add new theme → Import from your
   computer** → upload `dist/itstore.zip`, then **Use** it.
3. Set the shop logo to `themes/itstore/assets/img/logo.png` (or your own) under
   **Design → Theme & Logo**, and the favicon to `assets/img/favicon.png`.

**Manual:** copy `themes/itstore/` into `themes/` and each `modules/itstore*/`
into `modules/`, then apply the theme; `theme.yml` enables and positions all
sixteen modules. Configure each from **Modules** as needed.

## `itstoreimageslot` — hero image slot

* Responsive, swipeable, autoplaying hero slider hooked into `displayHome`.
* Slides stored in `ps_itstore_slide`; managed from the module's configuration
  screen (image, title, subtitle, caption, CTA text/link, position, active).
* Three demo slides are seeded on install.
* Front-end behaviour lives in `views/js/image-slot.js` (`window.ImageSlot`),
  auto-initialising any `[data-image-slot]` element with keyboard, touch and
  lazy-loading support.

## `itstoresupport` — support widget

* Floating launcher + panel hooked into `displayFooterAfter`, site-wide.
* Quick actions (live chat, phone, email), a live-filtered FAQ, and a contact
  shortcut. All labels/contacts are configurable in the back office.
* Behaviour lives in `views/js/support.js` (`window.SupportWidget`),
  auto-initialising any `[data-support-widget]` element.

## `itstorecategoriesblock` — shop by category

* Home grid of category tiles built from the children of a configurable parent
  category (defaults to the Home category), with title and tile-count settings.
* Hooked into `displayHome`; uses core `Category` APIs (no custom table).

## `itstoredeals` — deals block

* Home grid of discounted products from `Product::getPricesDrop`, with a
  percentage-off badge and locale-aware pricing (`getCurrentLocale()->formatPrice`,
  falling back to `Tools::displayPrice`).
* Configurable title and product count. Renders nothing when there are no
  current price drops.

## `itstorebrands` — brands strip

* Home row of manufacturer logos linking to each brand's listing. Only
  manufacturers with an uploaded logo are shown (grayscale → colour on hover).
* Configurable title and max logos; uses core `Manufacturer` APIs.

## `itstoretrustbar` — trust / USP bar

* Site-wide reassurance strip (free delivery, warranty, secure payment, expert
  support) rendered on `displayWrapperTop`, so it appears under the header on
  every page. Icons are inline SVG (no icon-font dependency).
* Four fully configurable items (icon keyword, title, text) plus an on/off
  switch, stored in `Configuration`.

## Feature modules (product & catalogue)

| Module | What it adds | Integration |
|---|---|---|
| `itstoremegamenu` | Two-level department mega-menu from the category tree with an optional promo panel | `displayTop` |
| `itstorepcbuilder` | Guided custom-PC builder: maps component slots to categories, live total, add-whole-build-to-cart, home CTA | front controller `builder` |
| `itstorecompare` | Compare tray (browser-stored) + side-by-side spec table page | front controller `compare` |
| `itstorequickview` | Quick-view modal button on listing miniatures | `displayProductListReviews` + `displayFooter` |
| `itstorespecsheet` | Formatted specifications tab from product features | `displayProductExtraContent` |
| `itstorereviews` | Verified-buyer reviews: star summary, moderated list, submit form, BO moderation, **JSON-LD `AggregateRating`/`Review`** | `displayProductExtraContent` + front controller `submit` |
| `itstorefinance` | "From $x/month" instalment messaging (term + optional APR) | `displayProductAdditionalInfo` |
| `itstorestock` | Stock indicator + back-in-stock capture **and email sender** | `displayProductAdditionalInfo` + `notify` / `cron` controllers |
| `itstorewarranty` | Extended-warranty upsell tiers, each optionally mapped to a cart product | `displayProductAdditionalInfo` |
| `itstorebundles` | "Frequently bought together" from native product accessories | `displayFooterProduct` |

Notes:
* `itstorereviews` and `itstorestock` create their own tables
  (`ps_itstore_review`, `ps_itstore_stock_alert`) and drop them on uninstall.
* **Back-in-stock emails** are sent by `itstorestock`'s `cron` controller — call
  the token-protected URL shown on the module's config screen from a server cron
  (uses the themed `modules/itstorestock/mails/en/backinstock.*` templates).
* `itstorepcbuilder` warns when the selected CPU and motherboard disagree on a
  configurable **compatibility feature** (default `Socket`).
* PrestaShop ships free equivalents for a couple of these (`productcomments`,
  `blockwishlist`) — use whichever you prefer.

## Internationalisation & accessibility

* Every user-facing string uses `$this->l()` / `{l s=…}` and each module carries
  a `translations/` catalogue. Generate full XLIFF with
  `php bin/console translation:extract <locale>` on your shop, or translate from
  **International → Translations**.
* `assets/css/custom-rtl.css` is auto-loaded for RTL languages.
* Fonts are self-hosted / system-stack — **no external Google Fonts request**
  (GDPR-friendly). Drop a `.woff2` into `assets/fonts/` to ship Inter (see its
  README).
* Includes a skip-to-content link, visible focus rings, `prefers-reduced-motion`
  and `prefers-color-scheme: dark` support.

## Development & verification

> **Verified on PrestaShop 9.1.** The integration workflow booted a live
> PrestaShop 9.1 + MySQL, installed **all 24 `itstore*` modules** via
> `bin/console prestashop:module install` (step green), and passed the
> front-office smoke tests (home, product, category, PC-builder & compare
> controllers — no HTTP 5xx, no PHP errors). Run `31065110761`.

* No build step is required — the theme relies on the parent `classic` assets
  plus `custom.css`/`custom.js`, which PrestaShop's `FrontController` loads.
* Module PHP targets PrestaShop 1.7.6 – 9.1.x (`HelperForm`, hook registration,
  `registerStylesheet`/`registerJavascript`, `ModuleFrontController`,
  `ProductExtraContent`) and PHP 8.1+. Prices via
  `Context::getCurrentLocale()->formatPrice()` (with a `Tools::displayPrice()`
  fallback).
* **CI — lint** (`.github/workflows/lint.yml`): `php -l` (PHP 8.1/8.2/8.3),
  `node --check`, and YAML validation on every push.
* **CI — integration** (`.github/workflows/integration.yml`): boots a real
  PrestaShop (9.1, falling back to 9.0) + MySQL, installs all sixteen modules via
  `bin/console prestashop:module install`, and smoke-tests the home page, a
  product page, the category listing, and the PC-builder / compare front
  controllers for HTTP 5xx and PHP errors. Because the stock `classic` theme
  fires the same hooks the modules use, this exercises the real data paths
  (`getPricesDrop`, `getManufacturers`, `getFrontFeaturesStatic`,
  `getAccessories`, the review query, the builder cart add).
