{**
 * IT Store — Subscribe & Save opt-in.
 *}
<div class="itstore-ar" data-itstore-ar
     data-subscribe-url="{$ar_subscribe_url|escape:'html':'UTF-8'}"
     data-id-product="{$ar_id_product|intval}"
     data-token="{$ar_token|escape:'html':'UTF-8'}">
  <div class="itstore-ar__head">
    <span class="itstore-ar__title">{l s='Subscribe & Save' mod='itstoreautoreorder'} {$ar_discount|intval}%</span>
    <span class="itstore-ar__sub">{l s='Auto-reorder' mod='itstoreautoreorder'} {$ar_interval|escape:'html':'UTF-8'}</span>
  </div>
  {if $ar_logged}
    <button type="button" class="btn btn-outline-primary itstore-ar__btn js-itstore-ar-btn">{l s='Subscribe' mod='itstoreautoreorder'}</button>
    <p class="itstore-ar__msg" role="status" hidden></p>
  {else}
    <a class="itstore-ar__signin" href="{$ar_login_url|escape:'html':'UTF-8'}">{l s='Sign in to subscribe' mod='itstoreautoreorder'}</a>
  {/if}
</div>
