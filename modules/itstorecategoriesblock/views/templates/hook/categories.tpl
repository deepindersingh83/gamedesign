{**
 * IT Store — shop by category tiles.
 *}
<section class="page-section itstore-cats">
  <div class="container">
    {if $itstore_cb_title}<h2 class="section-title">{$itstore_cb_title|escape:'html':'UTF-8'}</h2>{/if}
    <div class="itstore-cats__grid">
      {foreach from=$itstore_cb_tiles item=tile}
        <a class="itstore-cats__tile" href="{$tile.url|escape:'html':'UTF-8'}">
          <span class="itstore-cats__media">
            <img src="{$tile.image|escape:'html':'UTF-8'}" alt="{$tile.name|escape:'html':'UTF-8'}" loading="lazy">
          </span>
          <span class="itstore-cats__name">{$tile.name|escape:'html':'UTF-8'}</span>
        </a>
      {/foreach}
    </div>
  </div>
</section>
