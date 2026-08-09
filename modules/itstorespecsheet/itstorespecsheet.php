<?php
/**
 * IT Store — Spec sheet.
 *
 * Adds a clean, formatted specifications table to the product page, built from
 * the product's features. Rendered as an extra product tab via the modern
 * `displayProductExtraContent` hook (PrestaShop 1.7 – 9.x).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

class Itstorespecsheet extends Module
{
    public function __construct()
    {
        $this->name = 'itstorespecsheet';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Spec Sheet', [], 'Modules.Itstorespecsheet.Admin');
        $this->description = $this->trans('Formatted specifications tab on the product page, built from features.', [], 'Modules.Itstorespecsheet.Admin');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-specsheet',
                'modules/' . $this->name . '/views/css/specsheet.css',
                ['media' => 'all', 'priority' => 140]
            );
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            $idProduct = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
        }
        if ($idProduct <= 0) {
            return [];
        }

        $features = Product::getFrontFeaturesStatic((int) $this->context->language->id, $idProduct);
        if (empty($features)) {
            return [];
        }

        // Group features so repeated feature names (multi-value) stay together.
        $rows = [];
        foreach ($features as $f) {
            if (!isset($f['name'])) {
                continue;
            }
            $rows[] = ['name' => $f['name'], 'value' => isset($f['value']) ? $f['value'] : ''];
        }

        $this->smarty->assign([
            'itstore_specs' => $rows,
            'itstore_specs_sheet_url' => $this->context->link->getModuleLink(
                $this->name,
                'sheet',
                ['id_product' => $idProduct],
                true
            ),
        ]);
        $html = $this->fetch('module:' . $this->name . '/views/templates/hook/specsheet.tpl');

        $extra = new ProductExtraContent();
        $extra->setTitle($this->trans('Specifications', [], 'Modules.Itstorespecsheet.Admin'))
            ->setContent($html);

        return [$extra];
    }
}
