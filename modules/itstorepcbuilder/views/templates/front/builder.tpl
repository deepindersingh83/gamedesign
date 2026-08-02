{**
 * IT Store — PC builder page.
 *}
{extends file='page.tpl'}

{block name='page_title'}{l s='Custom PC builder' mod='itstorepcbuilder'}{/block}

{block name='page_content'}
  <form class="itstore-pb js-itstore-pb" action="{$pb_action|escape:'html':'UTF-8'}" method="post">
    <input type="hidden" name="addBuild" value="1">
    <div class="itstore-pb__slots">
      {foreach from=$pb_slots item=slot}
        <div class="itstore-pb__slot">
          <label class="itstore-pb__slot-label" for="pb_{$slot.key}">{$slot.label|escape:'html':'UTF-8'}</label>
          {if $slot.products}
            <select class="itstore-pb__select js-itstore-pb-select" id="pb_{$slot.key}" name="build[{$slot.key}]" data-slot="{$slot.key}">
              <option value="" data-price="0">{l s='— none —' mod='itstorepcbuilder'}</option>
              {foreach from=$slot.products item=p}
                <option value="{$p.id|intval}" data-price="{$p.price_raw}">{$p.name|escape:'html':'UTF-8'} — {$p.price}</option>
              {/foreach}
            </select>
          {else}
            <p class="itstore-pb__empty">{l s='No products configured for this slot yet.' mod='itstorepcbuilder'}</p>
          {/if}
        </div>
      {/foreach}
    </div>

    <aside class="itstore-pb__summary">
      <h3>{l s='Your build' mod='itstorepcbuilder'}</h3>
      <ul class="itstore-pb__list js-itstore-pb-list"></ul>
      <div class="itstore-pb__total">
        <span>{l s='Total' mod='itstorepcbuilder'}</span>
        <strong class="js-itstore-pb-total">{$pb_currency_sign|escape:'html':'UTF-8'}0.00</strong>
      </div>
      <button type="submit" class="btn btn-primary itstore-pb__add">{l s='Add build to cart' mod='itstorepcbuilder'}</button>
    </aside>
  </form>
{/block}
