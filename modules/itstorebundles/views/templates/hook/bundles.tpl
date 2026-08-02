{**
 * IT Store — frequently bought together.
 *}
<section class="itstore-bundle">
  <h3 class="itstore-bundle__title">{l s='Frequently bought together' mod='itstorebundles'}</h3>
  <div class="itstore-bundle__row">
    <div class="itstore-bundle__product itstore-bundle__product--main">
      <img src="{$itstore_bundle_main.image|escape:'html':'UTF-8'}" alt="{$itstore_bundle_main.name|escape:'html':'UTF-8'}" loading="lazy">
      <span class="itstore-bundle__name">{$itstore_bundle_main.name|escape:'html':'UTF-8'}</span>
      <span class="itstore-bundle__tag">{l s='This item' mod='itstorebundles'}</span>
    </div>

    {foreach from=$itstore_bundle_items item=item}
      <span class="itstore-bundle__plus" aria-hidden="true">+</span>
      <div class="itstore-bundle__product">
        <a href="{$item.url|escape:'html':'UTF-8'}">
          <img src="{$item.image|escape:'html':'UTF-8'}" alt="{$item.name|escape:'html':'UTF-8'}" loading="lazy">
        </a>
        <a class="itstore-bundle__name" href="{$item.url|escape:'html':'UTF-8'}">{$item.name|escape:'html':'UTF-8'}</a>
        {if $item.price}<span class="itstore-bundle__price">{$item.price}</span>{/if}
        {if $item.add_url && $item.available}
          <a class="btn btn-primary itstore-bundle__add" data-button-action="add-to-cart" href="{$item.add_url|escape:'html':'UTF-8'}" rel="nofollow">
            {l s='Add' mod='itstorebundles'}
          </a>
        {/if}
      </div>
    {/foreach}
  </div>
</section>
