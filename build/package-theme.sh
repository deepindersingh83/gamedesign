#!/usr/bin/env bash
#
# Package the IT Store theme into an installable PrestaShop theme zip.
#
# PrestaShop installs the modules a theme needs when they are shipped inside the
# theme package under `dependencies/`. This repo keeps the modules DRY in the
# top-level `modules/` directory and assembles the installable bundle here, so
# there is a single source of truth.
#
# IMPORTANT — zip layout: PrestaShop's "Import from computer" derives the target
# theme folder from the zip name (itstore.zip -> themes/itstore/) and extracts
# the archive *into* it. So the archive must contain the theme's CONTENTS at its
# root (config/theme.yml, templates/, assets/, dependencies/ …) with NO wrapping
# `itstore/` folder — otherwise you get themes/itstore/itstore/… and PrestaShop
# reports "Missing configuration file".
#
# Output: dist/itstore.zip
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

THEME="itstore"

echo "==> Staging theme contents (at archive root, no wrapping folder)"
cp -r "$ROOT/themes/$THEME/." "$STAGE/"

echo "==> Bundling module dependencies"
mkdir -p "$STAGE/dependencies/modules"
for m in "$ROOT"/modules/itstore*; do
  name="$(basename "$m")"
  cp -r "$m" "$STAGE/dependencies/modules/$name"
done

# Strip VCS / OS noise from the bundle.
find "$STAGE" -name '.DS_Store' -delete 2>/dev/null || true

# Ship the CCC (Combine/Compress/Cache) directory pre-created and writable so
# PrestaShop never has to mkdir() themes/itstore/assets/cache/ at runtime — that
# on-the-fly mkdir is what fatals with "Permission denied" when the theme's
# assets/ dir is not writable by PHP. Shipping it removes the need for any manual
# chmod/chown or nginx/vhost change on the live server.
mkdir -p "$STAGE/assets/cache"
chmod 0775 "$STAGE/assets/cache"

# Sanity check: the manifest PrestaShop reads must sit at config/theme.yml.
if [ ! -f "$STAGE/config/theme.yml" ]; then
  echo "ERROR: config/theme.yml missing from the staged theme" >&2
  exit 1
fi

echo "==> Zipping"
mkdir -p "$DIST"
rm -f "$DIST/$THEME.zip"
( cd "$STAGE" && zip -rq "$DIST/$THEME.zip" . )

echo "==> Done: $DIST/$THEME.zip"
