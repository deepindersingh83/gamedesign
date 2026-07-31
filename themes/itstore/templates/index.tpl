{**
 * IT Store — home page.
 * The home content is composed from modules hooked into `displayHome`
 * (see theme.yml): the itstoreimageslot hero, featured products, banners
 * and custom text blocks.
 *}
{extends file='page.tpl'}

{block name='page_content_container'}
  <section id="content" class="page-home page-home--itstore">
    {block name='page_content'}
      {hook h='displayHome'}
    {/block}
  </section>
{/block}
