{**
 * IT Store — quick-view modal container (once per page).
 *}
<div class="itstore-qv" data-itstore-qv hidden>
  <div class="itstore-qv__backdrop" data-qv-close></div>
  <div class="itstore-qv__dialog" role="dialog" aria-modal="true" aria-label="{l s='Product quick view' mod='itstorequickview'}">
    <button type="button" class="itstore-qv__close" data-qv-close aria-label="{l s='Close' mod='itstorequickview'}">&times;</button>
    <div class="itstore-qv__loader" data-qv-loader>{l s='Loading…' mod='itstorequickview'}</div>
    <iframe class="itstore-qv__frame" data-qv-frame title="{l s='Product quick view' mod='itstorequickview'}" loading="lazy"></iframe>
  </div>
</div>
