{**
 * IT Store — bulk / business pricing card.
 *}
<div class="itstore-bp">
  <div class="itstore-bp__head">{l s='Bulk / Business Pricing' mod='itstorebulkpricing'}</div>
  {foreach from=$itstore_bp_rows item=r}
    <div class="itstore-bp__row">
      <span class="itstore-bp__qty">{$r.qty|escape:'html':'UTF-8'}</span>
      <span class="itstore-bp__price">{$r.price} {l s='/ unit' mod='itstorebulkpricing'}</span>
    </div>
  {/foreach}
</div>
