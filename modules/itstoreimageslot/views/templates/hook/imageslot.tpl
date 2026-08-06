{**
 * IT Store — hero image slot markup.
 * Hydrated by views/js/image-slot.js (the theme's image-slot component).
 *}
<section class="itstore-hero">
  <div class="container">
    <div class="image-slot" data-image-slot data-interval="{$itstore_slider_interval|intval}" data-effect="slide">
      <div class="image-slot__track">
        {foreach from=$itstore_slides item=slide name=slides}
          <div class="image-slot__slide{if $smarty.foreach.slides.first} is-active{/if}" aria-hidden="{if $smarty.foreach.slides.first}false{else}true{/if}">
            {if $slide.image}
              <img class="image-slot__img"
                   {if $smarty.foreach.slides.first}src="{$slide.image|escape:'html':'UTF-8'}"{else}data-src="{$slide.image|escape:'html':'UTF-8'}"{/if}
                   alt="{$slide.title|escape:'html':'UTF-8'}" loading="lazy">
            {/if}
            <div class="image-slot__overlay"></div>
            <div class="image-slot__caption">
              {if $slide.subtitle}<span class="image-slot__eyebrow">{$slide.subtitle|escape:'html':'UTF-8'}</span>{/if}
              {if $slide.title}<h2 class="image-slot__title">{$slide.title|escape:'html':'UTF-8'}</h2>{/if}
              {if $slide.caption}<p class="image-slot__text">{$slide.caption|escape:'html':'UTF-8'}</p>{/if}
              {if $slide.btn_text && $slide.btn_link}
                <a class="btn btn-accent image-slot__cta" href="{$slide.btn_link|escape:'html':'UTF-8'}">
                  {$slide.btn_text|escape:'html':'UTF-8'}
                </a>
              {/if}
            </div>
          </div>
        {/foreach}
      </div>

      {if count($itstore_slides) > 1}
        <button class="image-slot__nav image-slot__nav--prev" type="button" aria-label="{l s='Previous slide' d='Modules.Itstoreimageslot.Shop'}">
          <span aria-hidden="true">&#8249;</span>
        </button>
        <button class="image-slot__nav image-slot__nav--next" type="button" aria-label="{l s='Next slide' d='Modules.Itstoreimageslot.Shop'}">
          <span aria-hidden="true">&#8250;</span>
        </button>
        <div class="image-slot__dots" role="tablist"></div>
      {/if}
    </div>
  </div>
</section>
