<?php
/**
 * IT Store — Brands strip.
 *
 * A row of brand / manufacturer logos on the home page, linking to each
 * brand's product listing. Only manufacturers that have a logo uploaded are
 * shown, so the strip always looks tidy.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorebrands extends Module
{
    public function __construct()
    {
        $this->name = 'itstorebrands';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Brands');
        $this->description = $this->l('Row of brand / manufacturer logos on the home page.');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        Configuration::updateValue('ITSTORE_BR_TITLE', $this->l('Top brands'));
        Configuration::updateValue('ITSTORE_BR_NB', 12);

        return true;
    }

    public function uninstall()
    {
        foreach (['ITSTORE_BR_TITLE', 'ITSTORE_BR_NB'] as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-brands',
            'modules/' . $this->name . '/views/css/brands.css',
            ['media' => 'all', 'priority' => 119]
        );
    }

    public function hookDisplayHome($params)
    {
        $brands = $this->getBrands();
        if (empty($brands)) {
            return '';
        }

        $this->smarty->assign([
            'itstore_br_title' => Configuration::get('ITSTORE_BR_TITLE'),
            'itstore_brands' => $brands,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/brands.tpl');
    }

    protected function getBrands()
    {
        $idLang = (int) $this->context->language->id;
        $limit = (int) Configuration::get('ITSTORE_BR_NB');
        if ($limit <= 0) {
            $limit = 12;
        }

        $manufacturers = Manufacturer::getManufacturers(false, $idLang, true);
        if (!is_array($manufacturers)) {
            return [];
        }

        $brands = [];
        foreach ($manufacturers as $m) {
            $id = (int) $m['id_manufacturer'];
            // Only show brands that actually have a logo uploaded.
            if (!file_exists(_PS_MANU_IMG_DIR_ . $id . '.jpg')) {
                continue;
            }
            $brands[] = [
                'id' => $id,
                'name' => $m['name'],
                'url' => $this->context->link->getManufacturerLink($id, null),
                'logo' => $this->context->link->getManufacturerImageLink($id, 'medium_default'),
            ];
            if (count($brands) >= $limit) {
                break;
            }
        }

        return $brands;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreBrands')) {
            Configuration::updateValue('ITSTORE_BR_TITLE', Tools::getValue('ITSTORE_BR_TITLE'));
            Configuration::updateValue('ITSTORE_BR_NB', (int) Tools::getValue('ITSTORE_BR_NB'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Brands strip'), 'icon' => 'icon-copyright'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Block title'), 'name' => 'ITSTORE_BR_TITLE'],
                ['type' => 'text', 'label' => $this->l('Max logos'), 'name' => 'ITSTORE_BR_NB', 'class' => 'fixed-width-sm'],
            ],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreBrands'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreBrands';
        $helper->fields_value = [
            'ITSTORE_BR_TITLE' => Configuration::get('ITSTORE_BR_TITLE'),
            'ITSTORE_BR_NB' => (int) Configuration::get('ITSTORE_BR_NB'),
        ];

        return $helper->generateForm([$form]);
    }
}
