{**
 * IT Store — footer.
 * Keeps the classic footer hooks and adds the IT Store copyright lockup.
 *
 * The footer link blocks (ps_linklist / ps_customtext / ps_contactinfo) are laid
 * out with CSS grid in custom.css. The grid is applied to `.footer__links` — the
 * wrapper around the displayFooter hook — so each block becomes its own column.
 * The newsletter (displayFooterBefore), the after-hook and the copyright bar sit
 * outside that grid so they span the full width.
 *}
<div class="footer-container js-footer">
  <div class="container">
    <div class="footer__before">
      {block name='hook_footer_before'}
        {hook h='displayFooterBefore'}
      {/block}
    </div>

    <div class="footer__links">
      {block name='hook_footer'}
        {hook h='displayFooter'}
      {/block}
    </div>

    <div class="footer__after">
      {block name='hook_footer_after'}
        {hook h='displayFooterAfter'}
      {/block}
    </div>

    <div class="footer__copyright">
      <span class="itstore-footer__brand">IT<span>Store</span></span>
      &copy; {'Y'|date} {$shop.name} &mdash;
      {l s='Built with PrestaShop' d='Shop.Theme.Global'}
    </div>
  </div>
</div>
