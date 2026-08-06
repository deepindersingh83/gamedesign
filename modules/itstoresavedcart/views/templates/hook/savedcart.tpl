{**
 * IT Store — saved cart restore banner.
 *}
<div class="itstore-savedcart">
  <span class="itstore-savedcart__text">
    {l s='You have a saved cart from' mod='itstoresavedcart'} <strong>{$sc_date|escape:'html':'UTF-8'}</strong>
    {l s='with' mod='itstoresavedcart'} {$sc_count|intval} {l s='item(s).' mod='itstoresavedcart'}
  </span>
  <a class="btn btn-primary itstore-savedcart__btn" href="{$sc_restore_url|escape:'html':'UTF-8'}">{l s='Restore it' mod='itstoresavedcart'}</a>
</div>
