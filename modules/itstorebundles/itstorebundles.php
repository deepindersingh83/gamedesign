<?php
/**
 * IT Store — Frequently bought together.
 *
 * Shows the current product's accessories as a "bundle" beneath the product,
 * with per-item add-to-cart and a combined total. Uses PrestaShop's native
 * product accessories relation — no extra data to maintain.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorebundles extends Module
{
    public function __construct()
    {
        $this->name = 'itstorebundles';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Bundles');
        $this->description = $this->l('“Frequently bought together” block from product accessories.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-bundles',
                'modules/' . $this->name . '/views/css/bundles.css',
                ['media' => 'all', 'priority' => 142]
            );
        }
    }

    public function hookDisplayFooterProduct($params)
    {
        $idProduct = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            $idProduct = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
        }
        if ($idProduct <= 0) {
            return '';
        }

        $idLang = (int) $this->context->language->id;
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product)) {
            return '';
        }

        $accessories = $product->getAccessories($idLang);
        if (empty($accessories)) {
            return '';
        }

        $items = [];
        foreach (array_slice($accessories, 0, 4) as $a) {
            $items[] = [
                'id' => (int) $a['id_product'],
                'name' => isset($a['name']) ? $a['name'] : '',
                'url' => isset($a['link']) ? $a['link'] : $this->context->link->getProductLink((int) $a['id_product']),
                'add_url' => isset($a['add_to_cart_url']) ? $a['add_to_cart_url'] : '',
                'price' => isset($a['price']) ? $a['price'] : '',
                'image' => $this->context->link->getImageLink(
                    isset($a['link_rewrite']) ? $a['link_rewrite'] : '',
                    isset($a['id_image']) ? (int) $a['id_image'] : 0,
                    'home_default'
                ),
                'available' => !isset($a['quantity_all_versions']) || (int) $a['quantity'] > 0 || (int) $a['allow_oosp'] === 1,
            ];
        }

        $cover = Product::getCover($idProduct);
        $coverId = is_array($cover) && isset($cover['id_image']) ? (int) $cover['id_image'] : 0;

        $this->smarty->assign([
            'itstore_bundle_main' => [
                'name' => $product->name,
                'image' => $this->context->link->getImageLink($product->link_rewrite, $coverId, 'home_default'),
            ],
            'itstore_bundle_items' => $items,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/bundles.tpl');
    }
}
