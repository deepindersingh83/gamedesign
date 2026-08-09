{**
 * IT Store — product reviews (summary, list, form).
 *}
<div class="itstore-reviews">
  {if $itstore_rv_jsonld}
    <script type="application/ld+json">{$itstore_rv_jsonld nofilter}</script>
  {/if}
  <div class="itstore-reviews__summary">
    <div class="itstore-reviews__score">
      <span class="itstore-reviews__avg">{$itstore_rv_avg}</span>
      <span class="itstore-reviews__stars" aria-hidden="true">
        {section name=s start=1 loop=6 step=1}{if $smarty.section.s.index <= $itstore_rv_avg}★{else}☆{/if}{/section}
      </span>
      <span class="itstore-reviews__count">
        {if $itstore_rv_count}{$itstore_rv_count} {l s='review(s)' d='Modules.Itstorereviews.Shop'}{else}{l s='No reviews yet' d='Modules.Itstorereviews.Shop'}{/if}
      </span>
    </div>
  </div>

  {if $itstore_rv_count}
    <ul class="itstore-reviews__list">
      {foreach from=$itstore_rv_reviews item=r}
        <li class="itstore-reviews__item">
          <div class="itstore-reviews__item-head">
            <span class="itstore-reviews__item-stars" aria-hidden="true">{section name=t start=1 loop=6 step=1}{if $smarty.section.t.index <= $r.rating}★{else}☆{/if}{/section}</span>
            <strong class="itstore-reviews__author">{$r.customer_name|escape:'html':'UTF-8'}</strong>
            {if $r.verified}<span class="itstore-reviews__verified">{l s='Verified buyer' d='Modules.Itstorereviews.Shop'}</span>{/if}
          </div>
          {if $r.title}<p class="itstore-reviews__title">{$r.title|escape:'html':'UTF-8'}</p>{/if}
          <p class="itstore-reviews__text">{$r.content|escape:'html':'UTF-8'|nl2br nofilter}</p>
        </li>
      {/foreach}
    </ul>
  {/if}

  <div class="itstore-reviews__form-wrap">
    <h4 class="itstore-reviews__form-title">{l s='Write a review' d='Modules.Itstorereviews.Shop'}</h4>
    {if $itstore_rv_can_review}
      <form class="itstore-reviews__form js-itstore-review-form" action="{$itstore_rv_submit_url|escape:'html':'UTF-8'}" method="post">
        <input type="hidden" name="id_product" value="{$itstore_rv_id_product|intval}">
        <input type="hidden" name="token" value="{$itstore_rv_token|escape:'html':'UTF-8'}">
        <input type="text" name="itstore_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="itstore-hp">
        <input type="hidden" name="itstore_ts" value="{$smarty.now|intval}">
        <input type="hidden" name="rating" value="5" class="js-itstore-rating-value">
        <div class="itstore-reviews__rating js-itstore-rating" role="radiogroup" aria-label="{l s='Rating' d='Modules.Itstorereviews.Shop'}">
          {section name=st start=1 loop=6 step=1}
            <button type="button" class="itstore-reviews__star js-itstore-star" data-value="{$smarty.section.st.index}" aria-label="{$smarty.section.st.index}">★</button>
          {/section}
        </div>
        <input type="text" name="title" maxlength="255" placeholder="{l s='Title (optional)' d='Modules.Itstorereviews.Shop'}">
        <textarea name="content" rows="4" required placeholder="{l s='Share your experience…' d='Modules.Itstorereviews.Shop'}"></textarea>
        <button type="submit" class="btn btn-primary">{l s='Submit review' d='Modules.Itstorereviews.Shop'}</button>
        <p class="itstore-reviews__msg" role="status" hidden></p>
      </form>
    {else}
      <p class="itstore-reviews__signin">{l s='Please sign in to write a review.' d='Modules.Itstorereviews.Shop'}</p>
    {/if}
  </div>
</div>
