{**
 * IT Store — product specifications table.
 *}
<div class="itstore-specsheet">
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
