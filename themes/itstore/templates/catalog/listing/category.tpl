{**
 * IT Store — category listing.
 * Extends the parent product-list template and only replaces the header block
 * with the design's dark category banner + "About" text. Facets, products and
 * pagination come unchanged from the parent, so nothing is lost.
 *}
{extends file='catalog/listing/product-list.tpl'}

{block name='product_list_header'}
  <section class="itstore-cathero">
    <div class="container">
      <span class="itstore-cathero__eyebrow">{l s='Category' d='Shop.Theme.Catalog'}</span>
      <h1 class="itstore-cathero__title">{$category.name}</h1>
    </div>
  </section>

  {if $category.description}
    <div class="itstore-catabout">
      <div class="container">
        <details open>
          <summary>{l s='About' d='Shop.Theme.Catalog'} {$category.name}</summary>
          <div class="itstore-catabout__body rte-content">{$category.description nofilter}</div>
        </details>
      </div>
    </div>
  {/if}
{/block}
