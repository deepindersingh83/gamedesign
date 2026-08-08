{extends file='page.tpl'}
{block name='page_title'}{$blog_title|escape:'html':'UTF-8'}{if $blog_filter_label} — {$blog_filter_label|escape:'html':'UTF-8'}{/if}{/block}
{block name='page_content'}
  <div class="itstore-bloglist">
    <div class="itstore-bloglist__bar">
      <nav class="itstore-bloglist__cats">
        <a class="itstore-bloglist__cat{if !$blog_filter_label} is-active{/if}" href="{$blog_all_url|escape:'html':'UTF-8'}">{l s='All' d='Modules.Itstoreblog.Shop'}</a>
        {foreach from=$blog_categories item=c}
          <a class="itstore-bloglist__cat{if $c.active} is-active{/if}" href="{$c.url|escape:'html':'UTF-8'}">{$c.name|escape:'html':'UTF-8'} <span>{$c.count}</span></a>
        {/foreach}
      </nav>
      <a class="itstore-bloglist__rss" href="{$blog_rss_url|escape:'html':'UTF-8'}" title="{l s='RSS feed' d='Modules.Itstoreblog.Shop'}">RSS</a>
    </div>

    {if $blog_filter_label}
      <p class="itstore-bloglist__filter">{l s='Showing' d='Modules.Itstoreblog.Shop'} <strong>{$blog_filter_label|escape:'html':'UTF-8'}</strong> · <a href="{$blog_all_url|escape:'html':'UTF-8'}">{l s='clear' d='Modules.Itstoreblog.Shop'}</a></p>
    {/if}

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
                <span class="itstore-bloglist__meta">
                  {if $post.author}{l s='By' d='Modules.Itstoreblog.Shop'} {$post.author|escape:'html':'UTF-8'} · {/if}{$post.date|escape:'html':'UTF-8'}
                </span>
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
