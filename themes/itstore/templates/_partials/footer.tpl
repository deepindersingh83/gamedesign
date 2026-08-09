{**
 * IT Store — footer.
 * Keeps the classic footer hooks and adds the IT Store copyright lockup.
 *}
<div class="footer-container js-footer">
  <div class="container">
    <div class="row">
      {block name='hook_footer_before'}
        {hook h='displayFooterBefore'}
      {/block}
    </div>
    <div class="row">
      {block name='hook_footer'}
        {hook h='displayFooter'}
      {/block}
    </div>
    {block name='hook_footer_after'}
      {hook h='displayFooterAfter'}
    {/block}

    <div class="row footer__copyright">
      <div class="col-md-12">
        <span class="itstore-footer__brand">IT<span>Store</span></span>
        &copy; {'Y'|date} {$shop.name} &mdash;
        {l s='Built with PrestaShop' d='Shop.Theme.Global'}
      </div>
    </div>
  </div>
</div>
