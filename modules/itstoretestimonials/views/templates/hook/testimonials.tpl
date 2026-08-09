{**
 * IT Store — testimonials.
 *}
<section class="page-section itstore-testi">
  <div class="container">
    {if $ts_title}<h2 class="section-title">{$ts_title|escape:'html':'UTF-8'}</h2>{/if}
    <div class="itstore-testi__grid">
      {foreach from=$ts_items item=t}
        <figure class="itstore-testi__card">
          <div class="itstore-testi__stars" aria-hidden="true">★★★★★</div>
          <blockquote class="itstore-testi__text">&ldquo;{$t.text|escape:'html':'UTF-8'}&rdquo;</blockquote>
          <figcaption class="itstore-testi__author">
            {if $t.img}
              <span class="itstore-testi__avatar"><img src="{$t.img|escape:'html':'UTF-8'}" alt="{$t.name|escape:'html':'UTF-8'}" loading="lazy" decoding="async"></span>
            {else}
              <span class="itstore-testi__avatar itstore-testi__avatar--initial">{$t.initial|escape:'html':'UTF-8'}</span>
            {/if}
            <span>
              <span class="itstore-testi__name">{$t.name|escape:'html':'UTF-8'}</span>
              {if $t.role}<span class="itstore-testi__role">{$t.role|escape:'html':'UTF-8'}</span>{/if}
            </span>
          </figcaption>
        </figure>
      {/foreach}
    </div>
  </div>
</section>
