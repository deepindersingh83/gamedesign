# Theme email templates

PrestaShop lets a theme override transactional email templates by mirroring the
core `mails/` structure here, e.g.:

```
themes/itstore/mails/en/order_conf.html
themes/itstore/mails/en/order_conf.txt
```

Copy the templates you want to restyle from `mails/themes/modern/en/` (or the
core `mails/en/`) into this folder and apply the IT Store colours
(navy `#0b1f33`, blue `#1f6feb`, cyan `#22d3ee`).

A worked example of a fully themed module email lives in
`modules/itstorestock/mails/en/backinstock.html`.

> Only the templates present here are overridden; anything you don't copy keeps
> the shop's current mail theme, so it is safe to add them incrementally.
