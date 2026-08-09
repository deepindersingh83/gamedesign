{**
 * IT Store — Recently Compared strip. Populated from localStorage by
 * recentcompare.js; the container stays hidden until it has cards.
 *}
<section class="itstore-rc" data-rc-hide="{$rc_hide|intval}"{if $rc_capture} data-rc-capture='{$rc_capture nofilter}'{/if} hidden>
  <div class="container">
    <div class="itstore-rc__head">
      <h2 class="itstore-rc__title">{$rc_title|escape:'html':'UTF-8'}</h2>
    </div>
    <div class="itstore-rc__track" aria-live="polite"></div>
  </div>
</section>
