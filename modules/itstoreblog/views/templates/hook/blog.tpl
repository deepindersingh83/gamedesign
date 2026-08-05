{**
 * IT Store — From the Blog.
 *}
<section class="page-section itstore-blog itstore-blog--band">
  <div class="container">
    <div class="itstore-blog__head">
      <h2 class="section-title" style="margin-bottom:0">{$bl_title|escape:'html':'UTF-8'}</h2>
      {if $bl_all}<a class="itstore-blog__all" href="{$bl_all|escape:'html':'UTF-8'}">{l s='View all articles' mod='itstoreblog'} &rarr;</a>{/if}
    </div>
    <div class="itstore-blog__grid">
      {foreach from=$bl_posts item=post}
        <a class="itstore-blog__card" href="{$post.link|escape:'html':'UTF-8'}">
          {if $post.img}<span class="itstore-blog__media"><img src="{$post.img|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" loading="lazy"></span>{/if}
          <span class="itstore-blog__body">
            {if $post.tag}<span class="itstore-blog__tag">{$post.tag|escape:'html':'UTF-8'}</span>{/if}
            <span class="itstore-blog__title">{$post.title|escape:'html':'UTF-8'}</span>
          </span>
        </a>
      {/foreach}
    </div>
  </div>
</section>
