<?php
/**
 * IT Store — printable spec sheet.
 *
 * Renders a clean, print-ready specifications page for a product (features +
 * price + shop details) that the browser can save as PDF. Standalone output —
 * no theme chrome — so "Save as PDF" produces a tidy datasheet.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorespecsheetSheetModuleFrontController extends ModuleFrontController
{
    /** @var bool render without the theme layout */
    public $ajax = true;

    public function initContent()
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $idProduct = (int) Tools::getValue('id_product');
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product)) {
            Tools::redirect($this->context->link->getBaseLink());
        }

        $rows = [];
        foreach (Product::getFrontFeaturesStatic($idLang, $idProduct) as $f) {
            if (isset($f['name'])) {
                $rows[] = ['name' => $f['name'], 'value' => isset($f['value']) ? $f['value'] : ''];
            }
        }

        $price = $this->context->getCurrentLocale()->formatPrice(
            (float) Product::getPriceStatic($idProduct, true),
            $this->context->currency->iso_code
        );

        $this->context->smarty->assign([
            'sheet_product' => is_array($product->name) ? reset($product->name) : $product->name,
            'sheet_reference' => $product->reference,
            'sheet_price' => $price,
            'sheet_rows' => $rows,
            'sheet_shop' => Configuration::get('PS_SHOP_NAME'),
            'sheet_url' => $this->context->link->getProductLink($idProduct),
            'sheet_date' => date('Y-m-d'),
        ]);

        $this->setTemplate('module:itstorespecsheet/views/templates/front/sheet.tpl');
    }
}
