#!/usr/bin/env bash
#
# Package the IT Store theme into an installable PrestaShop theme zip.
#
# PrestaShop installs the modules a theme needs when they are shipped inside the
# theme package under `dependencies/`. This repo keeps the modules DRY in the
# top-level `modules/` directory and assembles the installable bundle here, so
# there is a single source of truth.
#
# Output: dist/itstore.zip
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

THEME="itstore"

echo "==> Staging theme"
mkdir -p "$STAGE/$THEME"
cp -r "$ROOT/themes/$THEME/." "$STAGE/$THEME/"

echo "==> Bundling module dependencies"
mkdir -p "$STAGE/$THEME/dependencies/modules"
for m in "$ROOT"/modules/itstore*; do
  name="$(basename "$m")"
  cp -r "$m" "$STAGE/$THEME/dependencies/modules/$name"
done

# Strip VCS / OS noise from the bundle.
find "$STAGE" -name '.DS_Store' -delete 2>/dev/null || true

echo "==> Zipping"
mkdir -p "$DIST"
rm -f "$DIST/$THEME.zip"
( cd "$STAGE" && zip -rq "$DIST/$THEME.zip" "$THEME" )

echo "==> Done: $DIST/$THEME.zip"
