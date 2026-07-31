{**
 * IT Store — brands strip.
 *}
<section class="page-section itstore-brands">
  <div class="container">
    {if $itstore_br_title}<h2 class="section-title">{$itstore_br_title|escape:'html':'UTF-8'}</h2>{/if}
    <div class="itstore-brands__strip">
      {foreach from=$itstore_brands item=b}
        <a class="itstore-brands__item" href="{$b.url|escape:'html':'UTF-8'}" title="{$b.name|escape:'html':'UTF-8'}">
          <img src="{$b.logo|escape:'html':'UTF-8'}" alt="{$b.name|escape:'html':'UTF-8'}" loading="lazy">
        </a>
      {/foreach}
    </div>
  </div>
</section>
