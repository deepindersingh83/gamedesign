{extends file='page.tpl'}
{block name='page_title'}{l s='Track your order' mod='itstoreordertrack'}{/block}
{block name='page_content'}
  <div class="itstore-track">
    <form class="itstore-track__form" method="post" action="{$track_action|escape:'html':'UTF-8'}">
      <input type="hidden" name="submitTrack" value="1">
      <input type="text" name="reference" required placeholder="{l s='Order reference (e.g. ABCDEFGHI)' mod='itstoreordertrack'}">
      <input type="email" name="email" required placeholder="{l s='Email on the order' mod='itstoreordertrack'}">
      <button type="submit" class="btn btn-primary">{l s='Track order' mod='itstoreordertrack'}</button>
    </form>

    {if $track_error}
      <p class="itstore-track__error">{$track_error|escape:'html':'UTF-8'}</p>
    {/if}

    {if $track_result}
      <div class="itstore-track__result">
        <div class="itstore-track__head">
          <div>
            <span class="itstore-track__ref">{$track_result.reference|escape:'html':'UTF-8'}</span>
            <span class="itstore-track__status">{$track_result.status|escape:'html':'UTF-8'}</span>
          </div>
          <span class="itstore-track__total">{$track_result.total}</span>
        </div>
        {if $track_result.steps}
          <ol class="itstore-track__steps">
            {foreach from=$track_result.steps item=s}
              <li><span class="itstore-track__step-name">{$s.name|escape:'html':'UTF-8'}</span>
                <span class="itstore-track__step-date">{$s.date|escape:'html':'UTF-8'}</span></li>
            {/foreach}
          </ol>
        {/if}
      </div>
    {/if}
  </div>
{/block}
