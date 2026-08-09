{**
 * IT Store — frequently bought together.
 *}
<section class="itstore-bundle">
  <h3 class="itstore-bundle__title">{l s='Frequently bought together' d='Modules.Itstorebundles.Shop'}</h3>
  <div class="itstore-bundle__row">
    <div class="itstore-bundle__product itstore-bundle__product--main">
      <img src="{$itstore_bundle_main.image|escape:'html':'UTF-8'}" alt="{$itstore_bundle_main.name|escape:'html':'UTF-8'}" loading="lazy">
      <span class="itstore-bundle__name">{$itstore_bundle_main.name|escape:'html':'UTF-8'}</span>
      <span class="itstore-bundle__tag">{l s='This item' d='Modules.Itstorebundles.Shop'}</span>
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
            {l s='Add' d='Modules.Itstorebundles.Shop'}
          </a>
        {/if}
      </div>
    {/foreach}
  </div>

  <div class="itstore-bundle__foot">
    <div class="itstore-bundle__total">
      <span class="itstore-bundle__total-label">{l s='Total for %d items' sprintf=[$itstore_bundle_count] d='Modules.Itstorebundles.Shop'}</span>
      <span class="itstore-bundle__total-value">{$itstore_bundle_total}</span>
    </div>
    <form class="itstore-bundle__all-form" method="post" action="{$itstore_bundle_add_url|escape:'html':'UTF-8'}">
      <input type="hidden" name="id_product" value="{$itstore_bundle_id|intval}">
      <input type="hidden" name="token" value="{$itstore_bundle_token|escape:'html':'UTF-8'}">
      <button type="submit" class="btn btn-primary itstore-bundle__add-all">{l s='Add all to cart' d='Modules.Itstorebundles.Shop'}</button>
    </form>
  </div>
</section>
