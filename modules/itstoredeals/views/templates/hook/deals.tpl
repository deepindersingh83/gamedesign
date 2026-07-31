{**
 * IT Store — deals block.
 *}
<section class="page-section itstore-deals">
  <div class="container">
    {if $itstore_deals_title}<h2 class="section-title">{$itstore_deals_title|escape:'html':'UTF-8'}</h2>{/if}
    <div class="itstore-deals__grid">
      {foreach from=$itstore_deals item=p}
        <article class="itstore-deals__card">
          <a class="itstore-deals__media" href="{$p.url|escape:'html':'UTF-8'}">
            {if $p.discount > 0}<span class="itstore-deals__badge">-{$p.discount|intval}%</span>{/if}
            <img src="{$p.image|escape:'html':'UTF-8'}" alt="{$p.name|escape:'html':'UTF-8'}" loading="lazy">
          </a>
          <div class="itstore-deals__body">
            <a class="itstore-deals__name" href="{$p.url|escape:'html':'UTF-8'}">{$p.name|escape:'html':'UTF-8'}</a>
            <div class="itstore-deals__price">
              <span class="itstore-deals__now">{$p.price}</span>
              {if $p.regular}<span class="itstore-deals__was">{$p.regular}</span>{/if}
            </div>
          </div>
        </article>
      {/foreach}
    </div>
  </div>
</section>
