{extends file='customer/page.tpl'}
{block name='page_title'}{l s='My subscriptions' d='Modules.Itstoreautoreorder.Shop'}{/block}
{block name='page_content'}
  {if $ar_subs}
    <table class="table itstore-ar-table">
      <thead>
        <tr>
          <th>{l s='Product' d='Modules.Itstoreautoreorder.Shop'}</th>
          <th>{l s='Next reminder' d='Modules.Itstoreautoreorder.Shop'}</th>
          <th>{l s='Every' d='Modules.Itstoreautoreorder.Shop'}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$ar_subs item=s}
          <tr>
            <td><a href="{$s.url|escape:'html':'UTF-8'}">{$s.name|escape:'html':'UTF-8'}</a></td>
            <td>{$s.next|escape:'html':'UTF-8'}</td>
            <td>{$s.interval|intval} {l s='days' d='Modules.Itstoreautoreorder.Shop'}</td>
            <td><a class="text-danger" href="{$s.cancel_url|escape:'html':'UTF-8'}">{l s='Cancel' d='Modules.Itstoreautoreorder.Shop'}</a></td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {else}
    <p>{l s='You have no active subscriptions.' d='Modules.Itstoreautoreorder.Shop'}</p>
  {/if}
{/block}
