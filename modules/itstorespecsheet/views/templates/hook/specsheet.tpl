{**
 * IT Store — product specifications table.
 *}
<div class="itstore-specsheet">
  {if isset($itstore_specs_sheet_url)}
    <a class="itstore-specsheet__download" href="{$itstore_specs_sheet_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">
      <span aria-hidden="true">📄</span> {l s='Download Spec Sheet' d='Modules.Itstorespecsheet.Shop'}
    </a>
  {/if}
  <table class="itstore-specsheet__table">
    <tbody>
      {foreach from=$itstore_specs item=spec name=specs}
        <tr class="itstore-specsheet__row{if $smarty.foreach.specs.index % 2} is-alt{/if}">
          <th class="itstore-specsheet__key">{$spec.name|escape:'html':'UTF-8'}</th>
          <td class="itstore-specsheet__val">{$spec.value|escape:'html':'UTF-8'}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
