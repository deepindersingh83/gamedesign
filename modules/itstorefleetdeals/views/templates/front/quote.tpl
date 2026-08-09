{extends file='page.tpl'}
{block name='page_title'}{l s='Request a bulk quote' d='Modules.Itstorefleetdeals.Shop'}{/block}
{block name='page_content'}
  <div class="itstore-quote">
    {if isset($quote_sent) && $quote_sent}
      <div class="itstore-quote__ok">{l s='Thanks — our team will email you a tailored quote within 1 business day.' d='Modules.Itstorefleetdeals.Shop'}</div>
    {else}
      <p class="itstore-quote__lead">{l s='Buying in volume? Tell us what you need and we’ll price it up.' d='Modules.Itstorefleetdeals.Shop'}</p>
      <form class="itstore-quote__form" method="post" action="{$quote_action|escape:'html':'UTF-8'}">
        <input type="hidden" name="submitQuote" value="1">
        <input type="text" name="itstore_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="itstore-hp">
        <input type="hidden" name="itstore_ts" value="{$smarty.now|intval}">
        <div class="itstore-quote__row">
          <input type="text" name="name" required placeholder="{l s='Your name' d='Modules.Itstorefleetdeals.Shop'}">
          <input type="text" name="company" placeholder="{l s='Company' d='Modules.Itstorefleetdeals.Shop'}">
        </div>
        <div class="itstore-quote__row">
          <input type="email" name="email" required placeholder="{l s='you@company.com' d='Modules.Itstorefleetdeals.Shop'}">
          <input type="text" name="phone" placeholder="{l s='Phone (optional)' d='Modules.Itstorefleetdeals.Shop'}">
        </div>
        <textarea name="details" rows="5" required placeholder="{l s='What do you need? e.g. 15× business desktops, 3-year warranty…' d='Modules.Itstorefleetdeals.Shop'}"></textarea>
        <button type="submit" class="btn btn-primary">{l s='Send request' d='Modules.Itstorefleetdeals.Shop'}</button>
      </form>
    {/if}
  </div>
{/block}
