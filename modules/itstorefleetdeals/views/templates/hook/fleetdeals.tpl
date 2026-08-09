{**
 * IT Store — Business Fleet Deals + Clearance band.
 *}
<section class="page-section itstore-fd">
  <div class="container">
    <div class="itstore-fd__grid">
      <div class="itstore-fd__fleet">
        <div>
          <span class="itstore-fd__eyebrow">{$fd_eyebrow|escape:'html':'UTF-8'}</span>
          <h2 class="itstore-fd__title">{$fd_title|escape:'html':'UTF-8'}</h2>
          <p class="itstore-fd__sub">{$fd_sub|escape:'html':'UTF-8'}</p>
        </div>
        <a class="itstore-fd__quote-btn" href="{$fd_quote_url|escape:'html':'UTF-8'}">{l s='Request Quote' d='Modules.Itstorefleetdeals.Shop'}</a>
      </div>
      <div class="itstore-fd__clearance">
        <span class="itstore-fd__cl-eyebrow">{$fd_cl_eyebrow|escape:'html':'UTF-8'}</span>
        <div class="itstore-fd__cl-title">{$fd_cl_title|escape:'html':'UTF-8'}</div>
        <a class="itstore-fd__cl-link" href="{$fd_cl_link|escape:'html':'UTF-8'}">{l s='Shop Deals' d='Modules.Itstorefleetdeals.Shop'} &rarr;</a>
      </div>
    </div>
  </div>
</section>
