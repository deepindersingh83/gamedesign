<?php
/**
 * IT Store — Quick view.
 *
 * Adds a "Quick view" button to each product miniature in listings. Clicking it
 * opens the product in a lightweight modal (rendered in an isolated frame) so
 * shoppers can preview without leaving the listing.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorequickview extends Module
{
    public function __construct()
    {
        $this->name = 'itstorequickview';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Quick View', [], 'Modules.Itstorequickview.Admin');
        $this->description = $this->trans('Quick-view modal button on product listings.', [], 'Modules.Itstorequickview.Admin');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayProductListReviews')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayFooter');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-quickview',
            'modules/' . $this->name . '/views/css/quickview.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'itstore-quickview',
            'modules/' . $this->name . '/views/js/quickview.js',
            ['position' => 'bottom', 'priority' => 150, 'attribute' => 'defer']
        );
    }

    /**
     * Injected inside each product miniature.
     */
    public function hookDisplayProductListReviews($params)
    {
        if (empty($params['product'])) {
            return '';
        }
        $product = $params['product'];
        $url = '';
        if (is_array($product) && isset($product['url'])) {
            $url = $product['url'];
        } elseif (is_array($product) && isset($product['id_product'])) {
            $url = $this->context->link->getProductLink((int) $product['id_product']);
        }
        if (!$url) {
            return '';
        }

        $this->smarty->assign('itstore_qv_url', $url);

        return $this->display(__FILE__, 'views/templates/hook/button.tpl');
    }

    /**
     * The modal container, rendered once per page.
     */
    public function hookDisplayFooter($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/modal.tpl');
    }
}
