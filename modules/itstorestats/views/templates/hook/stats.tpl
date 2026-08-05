{**
 * IT Store — stats band.
 *}
<section class="itstore-stats" data-itstore-stats>
  <div class="itstore-stats__inner">
    {foreach from=$itstore_stats item=s}
      <div class="itstore-stats__item">
        <span class="itstore-stats__value" data-stat-value>{$s.value|escape:'html':'UTF-8'}</span>
        <span class="itstore-stats__label">{$s.label|escape:'html':'UTF-8'}</span>
      </div>
    {/foreach}
  </div>
</section>
