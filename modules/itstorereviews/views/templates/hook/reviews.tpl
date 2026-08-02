{**
 * IT Store — product reviews (summary, list, form).
 *}
<div class="itstore-reviews">
  <div class="itstore-reviews__summary">
    <div class="itstore-reviews__score">
      <span class="itstore-reviews__avg">{$itstore_rv_avg}</span>
      <span class="itstore-reviews__stars" aria-hidden="true">
        {section name=s start=1 loop=6 step=1}{if $smarty.section.s.index <= $itstore_rv_avg}★{else}☆{/if}{/section}
      </span>
      <span class="itstore-reviews__count">
        {if $itstore_rv_count}{$itstore_rv_count} {l s='review(s)' mod='itstorereviews'}{else}{l s='No reviews yet' mod='itstorereviews'}{/if}
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
            {if $r.verified}<span class="itstore-reviews__verified">{l s='Verified buyer' mod='itstorereviews'}</span>{/if}
          </div>
          {if $r.title}<p class="itstore-reviews__title">{$r.title|escape:'html':'UTF-8'}</p>{/if}
          <p class="itstore-reviews__text">{$r.content|escape:'html':'UTF-8'|nl2br nofilter}</p>
        </li>
      {/foreach}
    </ul>
  {/if}

  <div class="itstore-reviews__form-wrap">
    <h4 class="itstore-reviews__form-title">{l s='Write a review' mod='itstorereviews'}</h4>
    {if $itstore_rv_can_review}
      <form class="itstore-reviews__form js-itstore-review-form" action="{$itstore_rv_submit_url|escape:'html':'UTF-8'}" method="post">
        <input type="hidden" name="id_product" value="{$itstore_rv_id_product|intval}">
        <input type="hidden" name="token" value="{$itstore_rv_token|escape:'html':'UTF-8'}">
        <input type="hidden" name="rating" value="5" class="js-itstore-rating-value">
        <div class="itstore-reviews__rating js-itstore-rating" role="radiogroup" aria-label="{l s='Rating' mod='itstorereviews'}">
          {section name=st start=1 loop=6 step=1}
            <button type="button" class="itstore-reviews__star js-itstore-star" data-value="{$smarty.section.st.index}" aria-label="{$smarty.section.st.index}">★</button>
          {/section}
        </div>
        <input type="text" name="title" maxlength="255" placeholder="{l s='Title (optional)' mod='itstorereviews'}">
        <textarea name="content" rows="4" required placeholder="{l s='Share your experience…' mod='itstorereviews'}"></textarea>
        <button type="submit" class="btn btn-primary">{l s='Submit review' mod='itstorereviews'}</button>
        <p class="itstore-reviews__msg" role="status" hidden></p>
      </form>
    {else}
      <p class="itstore-reviews__signin">{l s='Please sign in to write a review.' mod='itstorereviews'}</p>
    {/if}
  </div>
</div>
