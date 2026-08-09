{**
 * IT Store — saved cart restore banner.
 *}
<div class="itstore-savedcart">
  <span class="itstore-savedcart__text">
    {l s='You have a saved cart from' d='Modules.Itstoresavedcart.Shop'} <strong>{$sc_date|escape:'html':'UTF-8'}</strong>
    {l s='with' d='Modules.Itstoresavedcart.Shop'} {$sc_count|intval} {l s='item(s).' d='Modules.Itstoresavedcart.Shop'}
  </span>
  <a class="btn btn-primary itstore-savedcart__btn" href="{$sc_restore_url|escape:'html':'UTF-8'}">{l s='Restore it' d='Modules.Itstoresavedcart.Shop'}</a>
</div>
