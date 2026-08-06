{extends file='customer/page.tpl'}
{block name='page_title'}{l s='My subscriptions' mod='itstoreautoreorder'}{/block}
{block name='page_content'}
  {if $ar_subs}
    <table class="table itstore-ar-table">
      <thead>
        <tr>
          <th>{l s='Product' mod='itstoreautoreorder'}</th>
          <th>{l s='Next reminder' mod='itstoreautoreorder'}</th>
          <th>{l s='Every' mod='itstoreautoreorder'}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$ar_subs item=s}
          <tr>
            <td><a href="{$s.url|escape:'html':'UTF-8'}">{$s.name|escape:'html':'UTF-8'}</a></td>
            <td>{$s.next|escape:'html':'UTF-8'}</td>
            <td>{$s.interval|intval} {l s='days' mod='itstoreautoreorder'}</td>
            <td><a class="text-danger" href="{$s.cancel_url|escape:'html':'UTF-8'}">{l s='Cancel' mod='itstoreautoreorder'}</a></td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {else}
    <p>{l s='You have no active subscriptions.' mod='itstoreautoreorder'}</p>
  {/if}
{/block}
