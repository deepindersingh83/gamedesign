# IT Store — PrestaShop theme

A modern, tech-focused storefront theme for **PrestaShop 1.7.6 – 8.x**, built
from the *IT Store Mockups* design. It ships as a child of the `classic` theme
plus two companion modules that implement the interactive pieces of the mockup:
a hero **image slot** slider and a floating **support** widget.

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
  assets/css/custom.css             # IT Store visual identity (auto-loaded by core)
  assets/js/custom.js               # small progressive enhancements
  assets/js/image-slot.js           # hero slider component (reference copy)
  assets/js/support.js              # support widget component (reference copy)
  templates/index.tpl               # home page (composes displayHome hooks)
  preview.svg                       # back-office preview thumbnail

modules/itstoreimageslot/           # hero "image slot" slider (displayHome)
modules/itstorecategoriesblock/     # "shop by category" tiles (displayHome)
modules/itstoredeals/               # deals / price-drop block (displayHome)
modules/itstorebrands/              # brand / manufacturer logo strip (displayHome)
modules/itstoretrustbar/            # site-wide trust / USP bar (displayWrapperTop)
modules/itstoresupport/             # floating support / help widget (displayFooterAfter)
```

All six `itstore*` modules are custom to this theme and auto-enabled via
`theme.yml`. The theme also uses PrestaShop's own bundled modules (`ps_mainmenu`,
`ps_searchbar`, `ps_shoppingcart`, `ps_featuredproducts`, `ps_facetedsearch`,
`ps_emailsubscription`, `ps_linklist`, `ps_contactinfo`, …) — those ship with
PrestaShop and only need enabling, which `theme.yml` handles.

## Theme

* **Parent theme:** `classic`. Only `index.tpl` is overridden; every other
  template falls back to the parent, and `custom.css` restyles the existing
  classic markup (`#header`, `#footer`, `.product-miniature`, `.block_newsletter`,
  …) with the IT Store identity (navy + blue/cyan + orange accent, Inter type).
* **Layouts:** full-width home, left-column listings.
* **Home composition** (via `displayHome` in `theme.yml`): image slot hero →
  featured products → banner → custom text.

### Install the theme

1. Copy `themes/itstore/` into your shop's `themes/` directory.
2. Copy `modules/itstoreimageslot/` and `modules/itstoresupport/` into `modules/`.
3. Back office → **Design → Theme & Logo → Add new theme → Import from FTP**
   (or select `itstore` if already present) and use it.
4. PrestaShop enables and hooks the two modules automatically from the theme
   manifest. If installing modules manually, install **IT Store Image Slot** and
   **IT Store Support Widget** from **Modules**.

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

## Modules we can add next (roadmap)

Custom `itstore*` blocks that would further strengthen an IT/tech store:

| Module idea | What it adds |
|---|---|
| `itstorecompare` | Side-by-side spec comparison for components/laptops |
| `itstorepcbuilder` | Guided custom-PC builder with compatibility checks |
| `itstorespecsheet` | Structured spec table on the product page from features |
| `itstorestock` | Live stock / “X left” + back-in-stock email alerts |
| `itstorefinance` | “From $x/month” finance / buy-now-pay-later messaging |
| `itstorebundles` | “Frequently bought together” / accessory bundles |
| `itstorewarranty` | Extended-warranty upsell add-on at product/cart |
| `itstorequickview` | Product quick-view modal from listing cards |
| `itstoremegamenu` | Department mega-menu with promo columns |
| `itstorereviews` | Verified-buyer reviews with rating summaries |

(PrestaShop already ships free equivalents for some of these — e.g.
`productcomments` for reviews, `blockwishlist` for wishlists — so those can be
enabled rather than built.)

## Development notes

* No build step is required — the theme relies on the parent `classic` assets
  plus `custom.css`/`custom.js`, which PrestaShop's `FrontController` loads
  automatically.
* Module PHP targets PrestaShop 1.7.6+ / 8.x conventions (`HelperForm`, hook
  registration, `registerStylesheet`/`registerJavascript`).
