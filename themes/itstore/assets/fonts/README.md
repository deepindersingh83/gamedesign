# Self-hosted fonts

Drop the Inter web font here (e.g. `Inter-roman.var.woff2`) and uncomment the
`@font-face` block at the top of `../css/custom.css`. Serving the font locally
avoids the external Google Fonts request (a GDPR consideration in the EU/AU).

Until a font file is added, the theme uses a system font stack
(`Inter, "Segoe UI", system-ui, -apple-system, sans-serif`), so text renders
correctly with no external dependency.
