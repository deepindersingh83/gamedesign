{extends file='page.tpl'}
{block name='page_title'}{$faq_title|escape:'html':'UTF-8'}{/block}
{block name='page_content'}
  <div class="itstore-faq">
    {if $faq_jsonld}<script type="application/ld+json">{$faq_jsonld nofilter}</script>{/if}
    {if $faq_groups}
      {foreach from=$faq_groups item=group}
        <section class="itstore-faq__group">
          <h2 class="itstore-faq__cat">{$group.category|escape:'html':'UTF-8'}</h2>
          <div class="itstore-faq__items">
            {foreach from=$group.items item=item}
              <details class="itstore-faq__item">
                <summary class="itstore-faq__q">{$item.question|escape:'html':'UTF-8'}</summary>
                <div class="itstore-faq__a rte-content">{$item.answer nofilter}</div>
              </details>
            {/foreach}
          </div>
        </section>
      {/foreach}
    {else}
      <p class="itstore-faq__empty">{l s='No questions have been published yet.' d='Modules.Itstorefaq.Shop'}</p>
    {/if}
  </div>
{/block}
