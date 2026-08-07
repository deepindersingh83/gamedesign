{**
 * IT Store — home hero (audience-toggled).
 *}
<section class="itstore-hero2" data-itstore-hero>
  <div class="itstore-hero2__inner">
    <div class="itstore-hero2__copy">
      <div class="itstore-hero2__toggle" role="tablist">
        <button type="button" class="itstore-hero2__tab is-active" data-hero-view="business" role="tab" aria-selected="true">{l s='For Business' d='Modules.Itstorehero.Shop'}</button>
        <button type="button" class="itstore-hero2__tab" data-hero-view="gamer" role="tab" aria-selected="false">{l s='For Gamers & Home' d='Modules.Itstorehero.Shop'}</button>
      </div>

      <div class="itstore-hero2__panel is-active" data-hero-panel="business">
        {if $hero_business.eyebrow}<span class="itstore-hero2__eyebrow">{$hero_business.eyebrow|escape:'html':'UTF-8'}</span>{/if}
        <h1 class="itstore-hero2__title">{$hero_business.title|escape:'html':'UTF-8'}</h1>
        <p class="itstore-hero2__sub">{$hero_business.sub|escape:'html':'UTF-8'}</p>
        <div class="itstore-hero2__actions">
          <a class="btn btn-primary itstore-hero2__cta" href="{$hero_business.link|escape:'html':'UTF-8'}">{$hero_business.cta|escape:'html':'UTF-8'}</a>
          <a class="itstore-hero2__quote" href="{$hero_bulk_link|escape:'html':'UTF-8'}">{l s='Get Bulk Quote' d='Modules.Itstorehero.Shop'}</a>
        </div>
      </div>
      <div class="itstore-hero2__panel" data-hero-panel="gamer">
        {if $hero_gamer.eyebrow}<span class="itstore-hero2__eyebrow">{$hero_gamer.eyebrow|escape:'html':'UTF-8'}</span>{/if}
        <h1 class="itstore-hero2__title">{$hero_gamer.title|escape:'html':'UTF-8'}</h1>
        <p class="itstore-hero2__sub">{$hero_gamer.sub|escape:'html':'UTF-8'}</p>
        <div class="itstore-hero2__actions">
          <a class="btn btn-primary itstore-hero2__cta" href="{$hero_gamer.link|escape:'html':'UTF-8'}">{$hero_gamer.cta|escape:'html':'UTF-8'}</a>
          <a class="itstore-hero2__quote" href="{$hero_bulk_link|escape:'html':'UTF-8'}">{l s='Get Bulk Quote' d='Modules.Itstorehero.Shop'}</a>
        </div>
      </div>
    </div>

    <div class="itstore-hero2__media">
      {if $hero_business.img}<img class="itstore-hero2__img is-active" data-hero-img="business" src="{$hero_business.img|escape:'html':'UTF-8'}" alt="" decoding="async" fetchpriority="high">{/if}
      {if $hero_gamer.img}<img class="itstore-hero2__img" data-hero-img="gamer" src="{$hero_gamer.img|escape:'html':'UTF-8'}" alt="" loading="lazy" decoding="async">{/if}
    </div>
  </div>
</section>
