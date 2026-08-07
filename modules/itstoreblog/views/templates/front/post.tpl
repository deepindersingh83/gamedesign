{extends file='page.tpl'}
{block name='page_title'}{$post.title|escape:'html':'UTF-8'}{/block}
{block name='page_content'}
  <article class="itstore-post">
    {if $post_jsonld}<script type="application/ld+json">{$post_jsonld nofilter}</script>{/if}
    <header class="itstore-post__head">
      {if $post.tag}<span class="itstore-post__tag">{$post.tag|escape:'html':'UTF-8'}</span>{/if}
      <h1 class="itstore-post__title">{$post.title|escape:'html':'UTF-8'}</h1>
      <div class="itstore-post__meta">{$post.date|escape:'html':'UTF-8'}</div>
    </header>
    {if $post.image}
      <div class="itstore-post__hero"><img src="{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" decoding="async"></div>
    {/if}
    <div class="itstore-post__content rte-content">
      {$post.content nofilter}
    </div>
    <footer class="itstore-post__foot">
      <a class="btn btn-secondary" href="{$blog_list_url|escape:'html':'UTF-8'}">&larr; {l s='Back to the blog' d='Modules.Itstoreblog.Shop'}</a>
    </footer>
  </article>
{/block}
