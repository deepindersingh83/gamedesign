{**
 * IT Store — compare toggle (inside product miniatures).
 *}
<button type="button" class="itstore-cmp-btn js-itstore-cmp"
        data-cmp-id="{$itstore_cmp_id|intval}"
        data-cmp-name="{$itstore_cmp_name|escape:'html':'UTF-8'}">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg>
  <span>{l s='Compare' d='Modules.Itstorecompare.Shop'}</span>
</button>
