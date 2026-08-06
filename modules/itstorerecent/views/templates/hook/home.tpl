{**
 * IT Store — Recommended For You (home) + Recently Viewed container.
 *}
{if $recent_recommended}
  <section class="page-section itstore-rec">
    <div class="container">
      <span class="section-eyebrow">{l s='Based on your browsing' mod='itstorerecent'}</span>
      <h2 class="section-title">{l s='Recommended For You' mod='itstorerecent'}</h2>
      <div class="itstore-rec__grid">
        {foreach from=$recent_recommended item=p}
          <a class="itstore-rec__card" href="{$p.url|escape:'html':'UTF-8'}">
            <span class="itstore-rec__media"><img src="{$p.image|escape:'html':'UTF-8'}" alt="{$p.name|escape:'html':'UTF-8'}" loading="lazy"></span>
            <span class="itstore-rec__name">{$p.name|escape:'html':'UTF-8'}</span>
            <span class="itstore-rec__price">{$p.price}</span>
          </a>
        {/foreach}
      </div>
    </div>
  </section>
{/if}

<section class="page-section itstore-rec itstore-rec--viewed" data-itstore-recent hidden>
  <div class="container">
    <h2 class="section-title">{l s='Recently Viewed' mod='itstorerecent'}</h2>
    <div class="itstore-rec__grid" data-recent-grid></div>
  </div>
</section>
