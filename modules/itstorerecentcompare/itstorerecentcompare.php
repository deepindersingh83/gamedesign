<?php
/**
 * IT Store — Recently Compared.
 *
 * A browser-stored strip of the products a visitor has recently run through the
 * compare page. On the compare page this module emits JSON cards for the
 * compared products; a small script stores them in localStorage and both the
 * home page and product pages render the strip from it. No server storage.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorerecentcompare extends Module
{
    public function __construct()
    {
        $this->name = 'itstorerecentcompare';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Recently Compared', [], 'Modules.Itstorerecentcompare.Admin');
        $this->description = $this->trans('A “Recently Compared” strip of products the visitor has run through the compare page.', [], 'Modules.Itstorerecentcompare.Admin');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-recentcompare',
            'modules/' . $this->name . '/views/css/recentcompare.css',
            ['media' => 'all', 'priority' => 124]
        );
        $this->context->controller->registerJavascript(
            'itstore-recentcompare',
            'modules/' . $this->name . '/views/js/recentcompare.js',
            ['position' => 'bottom', 'priority' => 124, 'attribute' => 'defer']
        );
    }

    public function hookDisplayFooter($params)
    {
        $capture = '';
        // On the compare page, capture the products being compared.
        $onCompare = Tools::getValue('module') === 'itstorecompare' && Tools::getValue('ids');
        if ($onCompare) {
            $ids = array_filter(array_map('intval', explode(',', (string) Tools::getValue('ids'))));
            $cards = [];
            foreach (array_slice(array_unique($ids), 0, 8) as $id) {
                $card = $this->productCard((int) $id);
                if ($card) {
                    $cards[] = $card;
                }
            }
            $capture = $cards ? json_encode($cards, JSON_UNESCAPED_SLASHES) : '';
        }

        $this->smarty->assign([
            'rc_capture' => $capture,
            'rc_hide' => $onCompare ? 1 : 0,
            'rc_title' => $this->trans('Recently compared', [], 'Modules.Itstorerecentcompare.Shop'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/strip.tpl');
    }

    protected function productCard($idProduct)
    {
        $idLang = (int) $this->context->language->id;
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product) || !$product->active) {
            return null;
        }
        $cover = Product::getCover($idProduct);
        $idImage = is_array($cover) && isset($cover['id_image']) ? (int) $cover['id_image'] : 0;

        return [
            'id' => $idProduct,
            'name' => $product->name,
            'url' => $this->context->link->getProductLink($idProduct, $product->link_rewrite),
            'image' => $this->context->link->getImageLink($product->link_rewrite, $idImage, 'home_default'),
            'price' => $this->context->getCurrentLocale()->formatPrice(
                (float) Product::getPriceStatic($idProduct, true),
                $this->context->currency->iso_code
            ),
        ];
    }
}
