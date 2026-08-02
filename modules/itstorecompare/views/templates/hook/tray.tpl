{**
 * IT Store — floating compare tray (once per page).
 *}
<div class="itstore-cmp-tray" data-itstore-cmp-tray hidden>
  <div class="container itstore-cmp-tray__inner">
    <span class="itstore-cmp-tray__label">{l s='Compare' mod='itstorecompare'} (<span data-cmp-count>0</span>/{$itstore_cmp_max|intval})</span>
    <div class="itstore-cmp-tray__items" data-cmp-items></div>
    <div class="itstore-cmp-tray__actions">
      <button type="button" class="btn btn-primary" data-cmp-go>{l s='Compare now' mod='itstorecompare'}</button>
      <button type="button" class="itstore-cmp-tray__clear" data-cmp-clear>{l s='Clear' mod='itstorecompare'}</button>
    </div>
  </div>
</div>
