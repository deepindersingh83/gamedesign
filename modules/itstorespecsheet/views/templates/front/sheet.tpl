<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$sheet_product|escape:'html':'UTF-8'} — {l s='Spec Sheet' d='Modules.Itstorespecsheet.Shop'}</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; color: #0b1220; margin: 0; padding: 32px; }
  .sheet { max-width: 720px; margin: 0 auto; }
  .sheet__head { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 3px solid #0b1220; padding-bottom: 12px; }
  .sheet__brand { font-size: 20px; font-weight: 800; }
  .sheet__brand span { color: #2f6fed; }
  .sheet__date { color: #5a6478; font-size: 12px; }
  h1 { font-size: 22px; margin: 20px 0 4px; }
  .sheet__meta { color: #5a6478; font-size: 13px; margin-bottom: 18px; }
  .sheet__price { font-weight: 800; font-size: 18px; color: #0b1220; }
  table { width: 100%; border-collapse: collapse; }
  th, td { text-align: left; padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #eef0f4; }
  th { width: 38%; background: #f8f9fc; font-weight: 700; }
  .sheet__foot { margin-top: 22px; font-size: 11px; color: #8b94a8; }
  .sheet__print { margin: 18px 0; }
  .sheet__print button { background: #2f6fed; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; font-weight: 700; cursor: pointer; }
  @media print { .sheet__print { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<div class="sheet">
  <div class="sheet__head">
    <span class="sheet__brand">IT<span>Store</span></span>
    <span class="sheet__date">{$sheet_shop|escape:'html':'UTF-8'} · {$sheet_date|escape:'html':'UTF-8'}</span>
  </div>
  <h1>{$sheet_product|escape:'html':'UTF-8'}</h1>
  <div class="sheet__meta">
    {if $sheet_reference}{l s='SKU' d='Modules.Itstorespecsheet.Shop'}: {$sheet_reference|escape:'html':'UTF-8'} · {/if}
    <span class="sheet__price">{$sheet_price}</span>
  </div>
  <div class="sheet__print"><button type="button" onclick="window.print()">{l s='Print / Save as PDF' d='Modules.Itstorespecsheet.Shop'}</button></div>
  {if $sheet_rows}
    <table>
      <tbody>
        {foreach from=$sheet_rows item=r}
          <tr><th>{$r.name|escape:'html':'UTF-8'}</th><td>{$r.value|escape:'html':'UTF-8'}</td></tr>
        {/foreach}
      </tbody>
    </table>
  {else}
    <p>{l s='No published specifications for this product.' d='Modules.Itstorespecsheet.Shop'}</p>
  {/if}
  <p class="sheet__foot">{$sheet_url|escape:'html':'UTF-8'}</p>
</div>
</body>
</html>
