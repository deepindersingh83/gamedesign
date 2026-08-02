{**
 * IT Store — trust / USP bar.
 * Icons are rendered as inline SVG (no external icon font dependency),
 * chosen from the configured icon keyword with a safe default.
 *}
<a class="itstore-skip-link" href="#content">{l s='Skip to content' mod='itstoretrustbar'}</a>
<div class="itstore-trustbar">
  <div class="container">
    <ul class="itstore-trustbar__list">
      {foreach from=$itstore_tb_items item=item}
        <li class="itstore-trustbar__item">
          <span class="itstore-trustbar__icon" aria-hidden="true">
            {assign var=ic value=$item.icon|default:''}
            {if $ic == 'local_shipping'}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            {elseif $ic == 'lock' || $ic == 'secure'}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            {elseif $ic == 'support_agent' || $ic == 'support'}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            {else}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
            {/if}
          </span>
          <span class="itstore-trustbar__copy">
            <strong class="itstore-trustbar__title">{$item.title|escape:'html':'UTF-8'}</strong>
            {if $item.text}<span class="itstore-trustbar__text">{$item.text|escape:'html':'UTF-8'}</span>{/if}
          </span>
        </li>
      {/foreach}
    </ul>
  </div>
</div>
