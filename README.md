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

modules/itstoreimageslot/           # hero "image slot" slider module
modules/itstoresupport/             # floating support / help widget module
```

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

## Development notes

* No build step is required — the theme relies on the parent `classic` assets
  plus `custom.css`/`custom.js`, which PrestaShop's `FrontController` loads
  automatically.
* Module PHP targets PrestaShop 1.7.6+ / 8.x conventions (`HelperForm`, hook
  registration, `registerStylesheet`/`registerJavascript`).
