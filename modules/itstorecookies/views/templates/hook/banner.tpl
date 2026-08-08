{**
 * IT Store — cookie consent banner. Hidden by default; cookies.js reveals it
 * only when no prior choice is stored.
 *}
<div class="itstore-ck" id="itstore-ck" data-days="{$ck_days|intval}" role="dialog" aria-live="polite" aria-label="{l s='Cookie notice' d='Modules.Itstorecookies.Shop'}" hidden>
  <div class="itstore-ck__inner">
    <p class="itstore-ck__text">
      {$ck_text|escape:'html':'UTF-8'}
      {if $ck_link}<a class="itstore-ck__link" href="{$ck_link|escape:'html':'UTF-8'}">{$ck_link_text|escape:'html':'UTF-8'}</a>{/if}
    </p>
    <div class="itstore-ck__actions">
      <button type="button" class="itstore-ck__btn itstore-ck__btn--decline" data-ck-choice="declined">{$ck_decline|escape:'html':'UTF-8'}</button>
      <button type="button" class="itstore-ck__btn itstore-ck__btn--accept" data-ck-choice="accepted">{$ck_accept|escape:'html':'UTF-8'}</button>
    </div>
  </div>
</div>
