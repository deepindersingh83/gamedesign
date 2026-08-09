{**
 * IT Store — header utility top bar.
 *}
<div class="itstore-topbar">
  <div class="container itstore-topbar__inner">
    <span class="itstore-topbar__msg">{$topbar_msg|escape:'html':'UTF-8'}</span>
    <nav class="itstore-topbar__links" aria-label="{l s='Utility' d='Modules.Itstoretopbar.Shop'}">
      <a href="{$topbar_track|escape:'html':'UTF-8'}">{l s='Track Order' d='Modules.Itstoretopbar.Shop'}</a>
      <a href="{$topbar_biz|escape:'html':'UTF-8'}">{l s='Business Accounts' d='Modules.Itstoretopbar.Shop'}</a>
    </nav>
  </div>
</div>
