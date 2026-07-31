<?php
/**
 * IT Store — Deals block.
 *
 * A home-page carousel of discounted products (price drops / specials),
 * reusing the theme's image-slot component for horizontal scrolling.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoredeals extends Module
{
    public function __construct()
    {
        $this->name = 'itstoredeals';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Deals');
        $this->description = $this->l('Home-page block of current deals / price drops.');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        Configuration::updateValue('ITSTORE_DEALS_TITLE', $this->l('Hot deals'));
        Configuration::updateValue('ITSTORE_DEALS_NB', 8);

        return true;
    }

    public function uninstall()
    {
        foreach (['ITSTORE_DEALS_TITLE', 'ITSTORE_DEALS_NB'] as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-deals',
            'modules/' . $this->name . '/views/css/deals.css',
            ['media' => 'all', 'priority' => 118]
        );
    }

    public function hookDisplayHome($params)
    {
        $products = $this->getDeals();
        if (empty($products)) {
            return '';
        }

        $this->smarty->assign([
            'itstore_deals_title' => Configuration::get('ITSTORE_DEALS_TITLE'),
            'itstore_deals' => $products,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/deals.tpl');
    }

    protected function getDeals()
    {
        $idLang = (int) $this->context->language->id;
        $nb = (int) Configuration::get('ITSTORE_DEALS_NB');
        if ($nb <= 0) {
            $nb = 8;
        }

        $rows = Product::getPricesDrop($idLang, 0, $nb);
        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        $deals = [];
        foreach ($rows as $r) {
            $idProduct = (int) $r['id_product'];
            $linkRewrite = isset($r['link_rewrite']) ? $r['link_rewrite'] : '';
            $idImage = isset($r['id_image']) ? (int) $r['id_image'] : 0;

            $final = Product::getPriceStatic($idProduct, true);
            $regular = Product::getPriceStatic($idProduct, true, null, 6, null, false, false);
            $discount = 0;
            if ($regular > 0 && $final < $regular) {
                $discount = (int) round((($regular - $final) / $regular) * 100);
            }

            $deals[] = [
                'id' => $idProduct,
                'name' => isset($r['name']) ? $r['name'] : '',
                'url' => $this->context->link->getProductLink($idProduct, $linkRewrite),
                'image' => $this->context->link->getImageLink($linkRewrite, $idImage, 'home_default'),
                'price' => $this->formatPrice($final),
                'regular' => $discount > 0 ? $this->formatPrice($regular) : '',
                'discount' => $discount,
            ];
        }

        return $deals;
    }

    /**
     * Locale-aware price formatting with a legacy fallback.
     */
    protected function formatPrice($price)
    {
        $iso = $this->context->currency ? $this->context->currency->iso_code : null;
        if (method_exists($this->context, 'getCurrentLocale')) {
            $locale = $this->context->getCurrentLocale();
            if ($locale) {
                return $locale->formatPrice($price, $iso);
            }
        }

        return Tools::displayPrice($price);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreDeals')) {
            Configuration::updateValue('ITSTORE_DEALS_TITLE', Tools::getValue('ITSTORE_DEALS_TITLE'));
            Configuration::updateValue('ITSTORE_DEALS_NB', (int) Tools::getValue('ITSTORE_DEALS_NB'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Deals block'), 'icon' => 'icon-bolt'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Block title'), 'name' => 'ITSTORE_DEALS_TITLE'],
                ['type' => 'text', 'label' => $this->l('Number of products'), 'name' => 'ITSTORE_DEALS_NB', 'class' => 'fixed-width-sm'],
            ],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreDeals'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreDeals';
        $helper->fields_value = [
            'ITSTORE_DEALS_TITLE' => Configuration::get('ITSTORE_DEALS_TITLE'),
            'ITSTORE_DEALS_NB' => (int) Configuration::get('ITSTORE_DEALS_NB'),
        ];

        return $helper->generateForm([$form]);
    }
}
