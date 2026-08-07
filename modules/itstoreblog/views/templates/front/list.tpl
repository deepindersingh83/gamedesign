{extends file='page.tpl'}
{block name='page_title'}{$blog_title|escape:'html':'UTF-8'}{/block}
{block name='page_content'}
  <div class="itstore-bloglist">
    {if $blog_posts}
      <div class="itstore-bloglist__grid">
        {foreach from=$blog_posts item=post}
          <article class="itstore-bloglist__card">
            <a class="itstore-bloglist__link" href="{$post.url|escape:'html':'UTF-8'}">
              {if $post.image}
                <span class="itstore-bloglist__media"><img src="{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" loading="lazy" decoding="async"></span>
              {/if}
              <span class="itstore-bloglist__body">
                {if $post.tag}<span class="itstore-bloglist__tag">{$post.tag|escape:'html':'UTF-8'}</span>{/if}
                <span class="itstore-bloglist__title">{$post.title|escape:'html':'UTF-8'}</span>
                {if $post.excerpt}<span class="itstore-bloglist__excerpt">{$post.excerpt|escape:'html':'UTF-8'}</span>{/if}
                <span class="itstore-bloglist__date">{$post.date|escape:'html':'UTF-8'}</span>
              </span>
            </a>
          </article>
        {/foreach}
      </div>

      {if $blog_pages > 1}
        <nav class="itstore-bloglist__pager">
          {if $blog_prev}<a class="btn btn-secondary" href="{$blog_prev|escape:'html':'UTF-8'}">&larr; {l s='Newer' d='Modules.Itstoreblog.Shop'}</a>{/if}
          <span class="itstore-bloglist__pageno">{$blog_page} / {$blog_pages}</span>
          {if $blog_next}<a class="btn btn-secondary" href="{$blog_next|escape:'html':'UTF-8'}">{l s='Older' d='Modules.Itstoreblog.Shop'} &rarr;</a>{/if}
        </nav>
      {/if}
    {else}
      <p class="itstore-bloglist__empty">{l s='No articles published yet.' d='Modules.Itstoreblog.Shop'}</p>
    {/if}
  </div>
{/block}
