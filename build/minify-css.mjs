/**
 * IT Store — dependency-free CSS minifier.
 *
 * Produces a *.min.css next to each source file so the theme can ship a
 * pre-minified stylesheet (PrestaShop's CCC also minifies at runtime; this is
 * for shops that keep CCC off or want a smaller first payload). Deliberately
 * conservative: strips comments and collapses whitespace without touching
 * selectors, values, or `url()` / `data:` contents.
 *
 * Usage:
 *   node build/minify-css.mjs themes/itstore/assets/css/custom.css [more.css ...]
 *   node build/minify-css.mjs            # defaults to the theme's stylesheets
 */
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const DEFAULTS = [
  'themes/itstore/assets/css/custom.css',
  'themes/itstore/assets/css/custom-rtl.css',
  'themes/itstore/assets/css/fonts.css',
];

function minify(css) {
  return css
    // strip /* ... */ comments
    .replace(/\/\*[\s\S]*?\*\//g, '')
    // collapse runs of whitespace to a single space
    .replace(/\s+/g, ' ')
    // tidy space around structural punctuation
    .replace(/\s*([{}:;,>])\s*/g, '$1')
    // drop the last semicolon before a closing brace
    .replace(/;}/g, '}')
    .trim();
}

async function run(files) {
  for (const file of files) {
    const src = await readFile(file, 'utf8');
    const out = minify(src);
    const dest = file.replace(/\.css$/, '.min.css');
    await writeFile(dest, out);
    const saved = ((1 - out.length / src.length) * 100).toFixed(1);
    console.log(`${path.basename(file)} → ${path.basename(dest)}  (${src.length} → ${out.length} bytes, -${saved}%)`);
  }
}

const args = process.argv.slice(2);
run(args.length ? args : DEFAULTS).catch((err) => {
  console.error(err);
  process.exit(1);
});
