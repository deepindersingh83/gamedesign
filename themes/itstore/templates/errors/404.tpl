{**
 * IT Store — branded 404 page.
 *}
{extends file='page.tpl'}

{block name='page_title'}{l s='Page not found' d='Shop.Theme.Global'}{/block}

{block name='page_content_container'}
  <section class="itstore-404">
    <div class="container">
      <span class="itstore-404__code">404</span>
      <h1 class="itstore-404__title">{l s='We couldn’t find that page' d='Shop.Theme.Global'}</h1>
      <p class="itstore-404__text">{l s='The page you’re after may have moved, sold out or never existed. Try a search or head back to the store.' d='Shop.Theme.Global'}</p>

      <form class="itstore-404__search" method="get" action="{$urls.pages.search}">
        <input type="hidden" name="controller" value="search">
        <input type="text" name="s" class="itstore-404__input" placeholder="{l s='Search products…' d='Shop.Theme.Catalog'}" aria-label="{l s='Search' d='Shop.Theme.Catalog'}">
        <button type="submit" class="btn btn-primary">{l s='Search' d='Shop.Theme.Catalog'}</button>
      </form>

      <div class="itstore-404__links">
        <a class="btn btn-secondary" href="{$urls.base_url}">{l s='Back to home' d='Shop.Theme.Global'}</a>
        {if isset($urls.pages.contact)}<a class="btn btn-secondary" href="{$urls.pages.contact}">{l s='Contact us' d='Shop.Theme.Global'}</a>{/if}
      </div>
    </div>
  </section>
{/block}
