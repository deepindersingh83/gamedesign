{**
 * IT Store — extended-warranty upsell.
 *}
<div class="itstore-warranty">
  <div class="itstore-warranty__head">
    <span class="itstore-warranty__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </span>
    <strong>{l s='Protect your purchase' mod='itstorewarranty'}</strong>
  </div>
  <ul class="itstore-warranty__tiers">
    {foreach from=$itstore_wr_tiers item=tier}
      <li class="itstore-warranty__tier">
        <span class="itstore-warranty__label">{$tier.label|escape:'html':'UTF-8'}</span>
        <span class="itstore-warranty__price">{$tier.price|escape:'html':'UTF-8'}</span>
        {if $tier.add_url}
          <a class="btn btn-outline-primary itstore-warranty__add" href="{$tier.add_url|escape:'html':'UTF-8'}" rel="nofollow">{l s='Add' mod='itstorewarranty'}</a>
        {/if}
      </li>
    {/foreach}
  </ul>
  <p class="itstore-warranty__note">{l s='Warranty cover applies to this product — add it to your cart together.' mod='itstorewarranty'}</p>
</div>
