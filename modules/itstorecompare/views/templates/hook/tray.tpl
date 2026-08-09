{**
 * IT Store — floating compare tray (once per page).
 *}
<div class="itstore-cmp-tray" data-itstore-cmp-tray hidden>
  <div class="container itstore-cmp-tray__inner">
    <span class="itstore-cmp-tray__label">{l s='Compare' d='Modules.Itstorecompare.Shop'} (<span data-cmp-count>0</span>/{$itstore_cmp_max|intval})</span>
    <div class="itstore-cmp-tray__items" data-cmp-items></div>
    <div class="itstore-cmp-tray__actions">
      <button type="button" class="btn btn-primary" data-cmp-go>{l s='Compare now' d='Modules.Itstorecompare.Shop'}</button>
      <button type="button" class="itstore-cmp-tray__clear" data-cmp-clear>{l s='Clear' d='Modules.Itstorecompare.Shop'}</button>
    </div>
  </div>
</div>
