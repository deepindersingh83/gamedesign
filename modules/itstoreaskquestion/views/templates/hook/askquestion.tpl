{**
 * IT Store — product Q&A tab.
 *}
<div class="itstore-aq">
  {if $aq_items}
    <ul class="itstore-aq__list">
      {foreach from=$aq_items item=qa}
        <li class="itstore-aq__item">
          <p class="itstore-aq__q"><span>Q</span>{$qa.question|escape:'html':'UTF-8'}</p>
          <p class="itstore-aq__a"><span>A</span>{$qa.answer|escape:'html':'UTF-8'|nl2br nofilter}</p>
        </li>
      {/foreach}
    </ul>
  {else}
    <p class="itstore-aq__empty">{l s='No questions yet — be the first to ask about this product.' d='Modules.Itstoreaskquestion.Shop'}</p>
  {/if}

  <form class="itstore-aq__form js-itstore-aq-form" method="post" action="{$aq_submit_url|escape:'html':'UTF-8'}">
    <input type="hidden" name="id_product" value="{$aq_id_product|intval}">
    <input type="hidden" name="token" value="{$aq_token|escape:'html':'UTF-8'}">
    <input type="text" name="itstore_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="itstore-hp">
    <input type="hidden" name="itstore_ts" value="{$smarty.now|intval}">
    <div class="itstore-aq__form-title">{l s='Ask a Question' d='Modules.Itstoreaskquestion.Shop'}</div>
    <textarea name="question" rows="3" required placeholder="{l s='e.g. Does this include a Windows 11 licence key?' d='Modules.Itstoreaskquestion.Shop'}"></textarea>
    <input type="email" name="email" placeholder="{l s='Your email (for the reply)' d='Modules.Itstoreaskquestion.Shop'}">
    <button type="submit" class="btn btn-primary">{l s='Submit Question' d='Modules.Itstoreaskquestion.Shop'}</button>
    <p class="itstore-aq__msg" role="status" hidden></p>
  </form>
</div>
