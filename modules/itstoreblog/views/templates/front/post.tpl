{extends file='page.tpl'}
{block name='page_title'}{$post.title|escape:'html':'UTF-8'}{/block}
{block name='page_content'}
  <article class="itstore-post">
    {if $post_jsonld}<script type="application/ld+json">{$post_jsonld nofilter}</script>{/if}
    <header class="itstore-post__head">
      {if $post.category}<a class="itstore-post__tag" href="{$post.category_url|escape:'html':'UTF-8'}">{$post.category|escape:'html':'UTF-8'}</a>
      {elseif $post.tag}<span class="itstore-post__tag">{$post.tag|escape:'html':'UTF-8'}</span>{/if}
      <h1 class="itstore-post__title">{$post.title|escape:'html':'UTF-8'}</h1>
      <div class="itstore-post__meta">
        {if $post.author}<span class="itstore-post__author">{l s='By' d='Modules.Itstoreblog.Shop'} {$post.author|escape:'html':'UTF-8'}</span> · {/if}{$post.date|escape:'html':'UTF-8'}
      </div>
    </header>
    {if $post.image}
      <div class="itstore-post__hero"><img src="{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" decoding="async"></div>
    {/if}
    <div class="itstore-post__content rte-content">
      {$post.content nofilter}
    </div>

    {if $post.tags}
      <div class="itstore-post__tags">
        {foreach from=$post.tags item=t}
          <a class="itstore-post__tagpill" href="{$t.url|escape:'html':'UTF-8'}">#{$t.name|escape:'html':'UTF-8'}</a>
        {/foreach}
      </div>
    {/if}

    <footer class="itstore-post__foot">
      <a class="btn btn-secondary" href="{$blog_list_url|escape:'html':'UTF-8'}">&larr; {l s='Back to the blog' d='Modules.Itstoreblog.Shop'}</a>
    </footer>

    {if $post_related}
      <section class="itstore-post__related">
        <h2 class="itstore-post__related-title">{l s='Related articles' d='Modules.Itstoreblog.Shop'}</h2>
        <div class="itstore-post__related-grid">
          {foreach from=$post_related item=r}
            <a class="itstore-post__related-card" href="{$r.url|escape:'html':'UTF-8'}">
              {if $r.image}<span class="itstore-post__related-media"><img src="{$r.image|escape:'html':'UTF-8'}" alt="{$r.title|escape:'html':'UTF-8'}" loading="lazy" decoding="async"></span>{/if}
              <span class="itstore-post__related-name">{$r.title|escape:'html':'UTF-8'}</span>
              <span class="itstore-post__related-date">{$r.date|escape:'html':'UTF-8'}</span>
            </a>
          {/foreach}
        </div>
      </section>
    {/if}
  </article>
{/block}
