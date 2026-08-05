{**
 * IT Store — Subscribe & Save opt-in.
 *}
<label class="itstore-ar">
  <input type="checkbox" name="itstore_autoreorder" value="1" class="itstore-ar__check">
  <span class="itstore-ar__body">
    <span class="itstore-ar__title">{l s='Subscribe & Save' mod='itstoreautoreorder'} {$ar_discount|intval}%</span>
    <span class="itstore-ar__sub">{l s='Auto-reorder' mod='itstoreautoreorder'} {$ar_interval|escape:'html':'UTF-8'}</span>
  </span>
</label>
