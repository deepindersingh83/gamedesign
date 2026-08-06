{**
 * IT Store — capture current product + Recently Viewed strip on product page.
 *}
{if $recent_capture}
  <script type="application/json" data-recent-capture>{$recent_capture nofilter}</script>
{/if}
<section class="itstore-rec itstore-rec--viewed" data-itstore-recent hidden>
  <h2 class="itstore-rec__title">{l s='Recently Viewed' mod='itstorerecent'}</h2>
  <div class="itstore-rec__grid" data-recent-grid></div>
</section>
