{**
 * IT Store — support widget markup.
 * Hydrated by views/js/support.js.
 *}
<div class="support-widget" data-support-widget>
  <button class="support-widget__launcher" type="button" data-support-toggle aria-expanded="false" aria-label="{l s='Open support' d='Modules.Itstoresupport.Shop'}">
    <span class="support-widget__launcher-icon" aria-hidden="true">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
      </svg>
    </span>
    <span class="support-widget__launcher-label">{l s='Need help?' d='Modules.Itstoresupport.Shop'}</span>
  </button>

  <div class="support-widget__panel" hidden>
    <div class="support-widget__header">
      <div>
        <strong class="support-widget__title">{l s='How can we help?' d='Modules.Itstoresupport.Shop'}</strong>
        {if $sup_hours}<span class="support-widget__hours">{$sup_hours|escape:'html':'UTF-8'}</span>{/if}
      </div>
      <button class="support-widget__close" type="button" data-support-close aria-label="{l s='Close' d='Modules.Itstoresupport.Shop'}">&times;</button>
    </div>

    <div class="support-widget__actions">
      {if $sup_chat_url}
        <a class="support-widget__action" href="{$sup_chat_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">
          <span class="support-widget__action-ico">💬</span>{l s='Live chat' d='Modules.Itstoresupport.Shop'}
        </a>
      {/if}
      {if $sup_phone}
        <a class="support-widget__action" href="tel:{$sup_phone|escape:'html':'UTF-8'|replace:' ':''}">
          <span class="support-widget__action-ico">📞</span>{$sup_phone|escape:'html':'UTF-8'}
        </a>
      {/if}
      {if $sup_email}
        <a class="support-widget__action" href="mailto:{$sup_email|escape:'html':'UTF-8'}">
          <span class="support-widget__action-ico">✉️</span>{l s='Email us' d='Modules.Itstoresupport.Shop'}
        </a>
      {/if}
    </div>

    <div class="support-widget__faq-wrap">
      <label class="support-widget__search-label" for="support-search">{l s='Search the help centre' d='Modules.Itstoresupport.Shop'}</label>
      <input class="support-widget__search" id="support-search" type="search" data-support-search
             placeholder="{l s='Type a question…' d='Modules.Itstoresupport.Shop'}" autocomplete="off">

      <ul class="support-widget__faq">
        {foreach from=$sup_faqs item=faq}
          <li class="support-widget__faq-item" data-faq>
            <details>
              <summary>{$faq.q|escape:'html':'UTF-8'}</summary>
              <p>{$faq.a|escape:'html':'UTF-8'}</p>
            </details>
          </li>
        {/foreach}
      </ul>
      <p class="support-widget__empty" data-support-empty hidden>{l s='No results — try our contact page.' d='Modules.Itstoresupport.Shop'}</p>
    </div>

    <a class="support-widget__contact btn btn-primary" href="{$sup_contact_url|escape:'html':'UTF-8'}">
      {l s='Contact us' d='Modules.Itstoresupport.Shop'}
    </a>
  </div>
</div>
