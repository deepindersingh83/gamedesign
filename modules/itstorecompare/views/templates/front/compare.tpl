{**
 * IT Store — comparison page.
 *}
{extends file='page.tpl'}

{block name='page_title'}{l s='Compare products' mod='itstorecompare'}{/block}

{block name='page_content'}
  <section class="itstore-compare-page">
    {if $compare_products|@count < 1}
      <div class="itstore-compare-empty">
        <p>{l s='No products selected to compare.' mod='itstorecompare'}</p>
        <a class="btn btn-primary" href="{$urls.base_url}">{l s='Browse products' mod='itstorecompare'}</a>
      </div>
    {elseif $compare_products|@count < 2}
      <div class="itstore-compare-empty">
        <p>{l s='Add at least two products to compare them side by side.' mod='itstorecompare'}</p>
        <a class="btn btn-primary" href="{$urls.base_url}">{l s='Browse products' mod='itstorecompare'}</a>
      </div>
    {else}
      <div class="itstore-compare-scroll">
        <table class="itstore-compare-table">
          <thead>
            <tr>
              <th class="itstore-compare-table__corner">{l s='Product' mod='itstorecompare'}</th>
              {foreach from=$compare_products item=p}
                <th class="itstore-compare-table__head">
                  <a href="{$p.url|escape:'html':'UTF-8'}">
                    <img src="{$p.image|escape:'html':'UTF-8'}" alt="{$p.name|escape:'html':'UTF-8'}">
                    <span class="itstore-compare-table__name">{$p.name|escape:'html':'UTF-8'}</span>
                  </a>
                  <span class="itstore-compare-table__price">{$p.price}</span>
                </th>
              {/foreach}
            </tr>
          </thead>
          <tbody>
            {foreach from=$compare_feature_names item=fname}
              <tr>
                <th class="itstore-compare-table__key">{$fname|escape:'html':'UTF-8'}</th>
                {foreach from=$compare_products item=p}
                  <td>{if isset($p.features[$fname])}{$p.features[$fname]|escape:'html':'UTF-8'}{else}&mdash;{/if}</td>
                {/foreach}
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    {/if}
  </section>
{/block}
