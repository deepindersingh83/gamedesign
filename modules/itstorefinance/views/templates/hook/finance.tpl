{**
 * IT Store — finance messaging.
 *}
<div class="itstore-finance">
  <span class="itstore-finance__icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
  </span>
  <span class="itstore-finance__text">
    {l s='or from' d='Modules.Itstorefinance.Shop'}
    <strong class="itstore-finance__amount">{$itstore_fin_monthly}</strong>
    /{l s='month' d='Modules.Itstorefinance.Shop'}
    {if $itstore_fin_apr > 0}
      <span class="itstore-finance__terms">({$itstore_fin_months|intval} {l s='months' d='Modules.Itstorefinance.Shop'}, {$itstore_fin_apr}% {l s='APR' d='Modules.Itstorefinance.Shop'})</span>
    {else}
      <span class="itstore-finance__terms">({$itstore_fin_months|intval} {l s='months interest-free' d='Modules.Itstorefinance.Shop'})</span>
    {/if}
    {if $itstore_fin_url}
      &mdash; <a href="{$itstore_fin_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">{$itstore_fin_provider|escape:'html':'UTF-8'}</a>
    {else}
      &mdash; {$itstore_fin_provider|escape:'html':'UTF-8'}
    {/if}
  </span>
</div>
