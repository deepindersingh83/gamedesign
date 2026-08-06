<?php
/**
 * IT Store — Recommended & Recently Viewed.
 *
 * "Recommended For You" is a server-side best-sellers block (falling back to new
 * products) on the home page. "Recently Viewed" is a browser-stored strip: the
 * product page records the current product to localStorage and both the home
 * and product pages render the strip from it.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorerecent extends Module
{
    public function __construct()
    {
        $this->name = 'itstorerecent';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Recommended & Recently Viewed');
        $this->description = $this->l('“Recommended For You” best-sellers block and a “Recently Viewed” strip.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-recent',
            'modules/' . $this->name . '/views/css/recent.css',
            ['media' => 'all', 'priority' => 123]
        );
        $this->context->controller->registerJavascript(
            'itstore-recent',
            'modules/' . $this->name . '/views/js/recent.js',
            ['position' => 'bottom', 'priority' => 123]
        );
    }

    public function hookDisplayHome($params)
    {
        $this->smarty->assign([
            'recent_recommended' => $this->recommended(),
            'recent_capture' => null,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/home.tpl');
    }

    public function hookDisplayFooterProduct($params)
    {
        $capture = null;
        if (isset($params['product'])) {
            $p = $params['product'];
            $id = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
            if ($id > 0) {
                $capture = $this->productCard($id);
            }
        }
        $this->smarty->assign('recent_capture', $capture ? json_encode($capture, JSON_UNESCAPED_SLASHES) : '');

        return $this->display(__FILE__, 'views/templates/hook/product.tpl');
    }

    /**
     * Best sellers, falling back to new products.
     */
    protected function recommended()
    {
        $idLang = (int) $this->context->language->id;
        $rows = [];
        if (class_exists('ProductSale')) {
            $rows = ProductSale::getBestSalesLight($idLang, 0, 8);
        }
        if (empty($rows)) {
            $rows = Product::getNewProducts($idLang, 0, 8) ?: [];
        }
        if (!is_array($rows)) {
            return [];
        }

        $cards = [];
        foreach ($rows as $r) {
            if (empty($r['id_product'])) {
                continue;
            }
            $cards[] = $this->productCard((int) $r['id_product'], $r);
        }

        return $cards;
    }

    protected function productCard($idProduct, $row = [])
    {
        $idLang = (int) $this->context->language->id;
        $linkRewrite = isset($row['link_rewrite']) ? $row['link_rewrite'] : null;
        $name = isset($row['name']) ? $row['name'] : null;
        if ($linkRewrite === null || $name === null) {
            $product = new Product($idProduct, false, $idLang);
            $linkRewrite = $product->link_rewrite;
            $name = $product->name;
        }
        $idImage = isset($row['id_image']) ? (int) $row['id_image'] : 0;
        if (!$idImage) {
            $cover = Product::getCover($idProduct);
            $idImage = is_array($cover) && isset($cover['id_image']) ? (int) $cover['id_image'] : 0;
        }

        return [
            'id' => $idProduct,
            'name' => $name,
            'url' => $this->context->link->getProductLink($idProduct, $linkRewrite),
            'image' => $this->context->link->getImageLink($linkRewrite, $idImage, 'home_default'),
            'price' => $this->context->getCurrentLocale()->formatPrice(
                (float) Product::getPriceStatic($idProduct, true),
                $this->context->currency->iso_code
            ),
        ];
    }
}
