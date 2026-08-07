{**
 * IT Store — stock indicator + back-in-stock form.
 *}
<div class="itstore-stock itstore-stock--{$itstore_stk_state}">
  {if $itstore_stk_state == 'in'}
    <span class="itstore-stock__dot"></span>{l s='In stock' d='Modules.Itstorestock.Shop'}
  {elseif $itstore_stk_state == 'low'}
    <span class="itstore-stock__dot"></span>{l s='Only' d='Modules.Itstorestock.Shop'} {$itstore_stk_qty|intval} {l s='left — order soon' d='Modules.Itstorestock.Shop'}
  {else}
    <span class="itstore-stock__dot"></span>{l s='Currently out of stock' d='Modules.Itstorestock.Shop'}
  {/if}
</div>

{if $itstore_stk_state == 'out'}
  <form class="itstore-stock__notify js-itstore-stock-notify" action="{$itstore_stk_notify_url|escape:'html':'UTF-8'}" method="post">
    <input type="hidden" name="id_product" value="{$itstore_stk_id_product|intval}">
    <input type="hidden" name="id_product_attribute" value="{$itstore_stk_id_attr|intval}">
    <input type="hidden" name="token" value="{$itstore_stk_token|escape:'html':'UTF-8'}">
    <input type="text" name="itstore_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="itstore-hp">
    <input type="hidden" name="itstore_ts" value="{$smarty.now|intval}">
    <label class="itstore-stock__notify-label">{l s='Get notified when it’s back:' d='Modules.Itstorestock.Shop'}</label>
    <div class="itstore-stock__notify-row">
      <input type="email" name="email" required placeholder="{l s='your@email.com' d='Modules.Itstorestock.Shop'}">
      <button type="submit" class="btn btn-primary">{l s='Notify me' d='Modules.Itstorestock.Shop'}</button>
    </div>
    <p class="itstore-stock__notify-msg" role="status" hidden></p>
  </form>
{/if}
