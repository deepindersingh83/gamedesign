<?php
/**
 * IT Store — Product comparison.
 *
 * Lets shoppers add products to a compare tray (kept in the browser) and view a
 * side-by-side specification table on a dedicated comparison page. A "Compare"
 * toggle is injected into each product miniature and a floating tray sits at the
 * bottom of the page.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorecompare extends Module
{
    const MAX = 4;

    public function __construct()
    {
        $this->name = 'itstorecompare';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Compare');
        $this->description = $this->l('Side-by-side product comparison with a compare tray.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductListReviews')
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-compare',
            'modules/' . $this->name . '/views/css/compare.css',
            ['media' => 'all', 'priority' => 151]
        );
        Media::addJsDef([
            'itstoreCompare' => [
                'max' => self::MAX,
                'url' => $this->context->link->getModuleLink($this->name, 'compare', [], true),
            ],
        ]);
        $this->context->controller->registerJavascript(
            'itstore-compare',
            'modules/' . $this->name . '/views/js/compare.js',
            ['position' => 'bottom', 'priority' => 151]
        );
    }

    public function hookDisplayProductListReviews($params)
    {
        if (empty($params['product'])) {
            return '';
        }
        $product = $params['product'];
        $id = is_array($product) && isset($product['id_product']) ? (int) $product['id_product'] : 0;
        if (!$id) {
            return '';
        }
        $name = is_array($product) && isset($product['name']) ? $product['name'] : '';

        $this->smarty->assign(['itstore_cmp_id' => $id, 'itstore_cmp_name' => $name]);

        return $this->display(__FILE__, 'views/templates/hook/button.tpl');
    }

    public function hookDisplayFooter($params)
    {
        $this->smarty->assign('itstore_cmp_max', self::MAX);

        return $this->display(__FILE__, 'views/templates/hook/tray.tpl');
    }
}
