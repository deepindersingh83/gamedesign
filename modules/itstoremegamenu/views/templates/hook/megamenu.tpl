{**
 * IT Store — department mega-menu.
 *}
<nav class="itstore-mm" aria-label="{l s='Departments' mod='itstoremegamenu'}">
  <div class="container">
    <ul class="itstore-mm__bar">
      {foreach from=$itstore_mm_departments item=dep}
        <li class="itstore-mm__item">
          <a class="itstore-mm__link js-itstore-mm-toggle" href="{$dep.url|escape:'html':'UTF-8'}" aria-haspopup="true" aria-expanded="false">
            {$dep.name|escape:'html':'UTF-8'}
            {if $dep.subs}<span class="itstore-mm__caret" aria-hidden="true">▾</span>{/if}
          </a>
          {if $dep.subs}
            <div class="itstore-mm__panel">
              <div class="itstore-mm__cols">
                <ul class="itstore-mm__sublist">
                  {foreach from=$dep.subs item=sub}
                    <li><a href="{$sub.url|escape:'html':'UTF-8'}">{$sub.name|escape:'html':'UTF-8'}</a></li>
                  {/foreach}
                </ul>
              </div>
              {if $itstore_mm_promo.title || $itstore_mm_promo.img}
                <a class="itstore-mm__promo" {if $itstore_mm_promo.url}href="{$itstore_mm_promo.url|escape:'html':'UTF-8'}"{else}href="{$dep.url|escape:'html':'UTF-8'}"{/if}>
                  {if $itstore_mm_promo.img}<img src="{$itstore_mm_promo.img|escape:'html':'UTF-8'}" alt="" loading="lazy">{/if}
                  {if $itstore_mm_promo.title}<strong>{$itstore_mm_promo.title|escape:'html':'UTF-8'}</strong>{/if}
                  {if $itstore_mm_promo.text}<span>{$itstore_mm_promo.text|escape:'html':'UTF-8'}</span>{/if}
                </a>
              {/if}
            </div>
          {/if}
        </li>
      {/foreach}
    </ul>
  </div>
</nav>
