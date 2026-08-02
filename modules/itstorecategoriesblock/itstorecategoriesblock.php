<?php
/**
 * IT Store — Shop by category tiles.
 *
 * Renders a grid of category tiles on the home page, built from the children
 * of a configurable parent category. Helps shoppers jump straight into the
 * main departments (Laptops, Components, Networking, …).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorecategoriesblock extends Module
{
    public function __construct()
    {
        $this->name = 'itstorecategoriesblock';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Category Tiles');
        $this->description = $this->l('“Shop by category” tiles on the home page, built from your category tree.');
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        Configuration::updateValue('ITSTORE_CB_PARENT', (int) Configuration::get('PS_HOME_CATEGORY'));
        Configuration::updateValue('ITSTORE_CB_NB', 6);
        Configuration::updateValue('ITSTORE_CB_TITLE', $this->l('Shop by category'));

        return true;
    }

    public function uninstall()
    {
        foreach (['ITSTORE_CB_PARENT', 'ITSTORE_CB_NB', 'ITSTORE_CB_TITLE'] as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-categories',
            'modules/' . $this->name . '/views/css/categories.css',
            ['media' => 'all', 'priority' => 115]
        );
    }

    public function hookDisplayHome($params)
    {
        $tiles = $this->getTiles();
        if (empty($tiles)) {
            return '';
        }

        $this->smarty->assign([
            'itstore_cb_title' => Configuration::get('ITSTORE_CB_TITLE'),
            'itstore_cb_tiles' => $tiles,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/categories.tpl');
    }

    protected function getTiles()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $parent = (int) Configuration::get('ITSTORE_CB_PARENT');
        if ($parent <= 0) {
            $parent = (int) Configuration::get('PS_HOME_CATEGORY');
        }
        $limit = (int) Configuration::get('ITSTORE_CB_NB');
        if ($limit <= 0) {
            $limit = 6;
        }

        $children = Category::getChildren($parent, $idLang, true, $idShop);
        if (!is_array($children)) {
            return [];
        }

        $tiles = [];
        foreach (array_slice($children, 0, $limit) as $c) {
            $tiles[] = [
                'id' => (int) $c['id_category'],
                'name' => $c['name'],
                'url' => $this->context->link->getCategoryLink($c['id_category'], $c['link_rewrite']),
                'image' => $this->context->link->getCatImageLink($c['link_rewrite'], (int) $c['id_category'], 'category_default'),
            ];
        }

        return $tiles;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreCb')) {
            Configuration::updateValue('ITSTORE_CB_PARENT', (int) Tools::getValue('ITSTORE_CB_PARENT'));
            Configuration::updateValue('ITSTORE_CB_NB', (int) Tools::getValue('ITSTORE_CB_NB'));
            Configuration::updateValue('ITSTORE_CB_TITLE', Tools::getValue('ITSTORE_CB_TITLE'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Category tiles'), 'icon' => 'icon-th-large'],
            'input' => [
                ['type' => 'text', 'label' => $this->l('Block title'), 'name' => 'ITSTORE_CB_TITLE'],
                ['type' => 'text', 'label' => $this->l('Parent category ID'), 'name' => 'ITSTORE_CB_PARENT', 'class' => 'fixed-width-sm', 'desc' => $this->l('Children of this category are shown. Defaults to the Home category.')],
                ['type' => 'text', 'label' => $this->l('Number of tiles'), 'name' => 'ITSTORE_CB_NB', 'class' => 'fixed-width-sm'],
            ],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreCb'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreCb';
        $helper->fields_value = [
            'ITSTORE_CB_TITLE' => Configuration::get('ITSTORE_CB_TITLE'),
            'ITSTORE_CB_PARENT' => (int) Configuration::get('ITSTORE_CB_PARENT'),
            'ITSTORE_CB_NB' => (int) Configuration::get('ITSTORE_CB_NB'),
        ];

        return $helper->generateForm([$form]);
    }
}
