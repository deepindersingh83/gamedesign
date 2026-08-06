{**
 * IT Store — header utility top bar.
 *}
<div class="itstore-topbar">
  <div class="container itstore-topbar__inner">
    <span class="itstore-topbar__msg">{$topbar_msg|escape:'html':'UTF-8'}</span>
    <nav class="itstore-topbar__links" aria-label="{l s='Utility' mod='itstoretopbar'}">
      <a href="{$topbar_track|escape:'html':'UTF-8'}">{l s='Track Order' mod='itstoretopbar'}</a>
      <a href="{$topbar_biz|escape:'html':'UTF-8'}">{l s='Business Accounts' mod='itstoretopbar'}</a>
    </nav>
  </div>
</div>
