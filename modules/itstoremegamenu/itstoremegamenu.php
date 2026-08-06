<?php
/**
 * IT Store — Department mega-menu.
 *
 * A two-level mega-menu built from the category tree: top-level departments
 * with their sub-categories laid out in columns, plus an optional promo panel.
 * Hooked into `displayTop`.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoremegamenu extends Module
{
    public function __construct()
    {
        $this->name = 'itstoremegamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Mega Menu', [], 'Modules.Itstoremegamenu.Admin');
        $this->description = $this->trans('Department mega-menu built from your category tree, with a promo panel.', [], 'Modules.Itstoremegamenu.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_MM_ROOT' => 0,
            'ITSTORE_MM_DEPTH' => 8,
            'ITSTORE_MM_PROMO_TITLE' => $this->trans('Build your own PC', [], 'Modules.Itstoremegamenu.Admin'),
            'ITSTORE_MM_PROMO_TEXT' => $this->trans('Pick your parts, we assemble & test.', [], 'Modules.Itstoremegamenu.Admin'),
            'ITSTORE_MM_PROMO_URL' => '',
            'ITSTORE_MM_PROMO_IMG' => '',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayTop')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }

        return true;
    }

    public function uninstall()
    {
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-megamenu',
            'modules/' . $this->name . '/views/css/megamenu.css',
            ['media' => 'all', 'priority' => 90]
        );
        $this->context->controller->registerJavascript(
            'itstore-megamenu',
            'modules/' . $this->name . '/views/js/megamenu.js',
            ['position' => 'bottom', 'priority' => 90]
        );
    }

    public function hookDisplayTop($params)
    {
        $departments = $this->getTree();
        if (empty($departments)) {
            return '';
        }

        $this->smarty->assign([
            'itstore_mm_departments' => $departments,
            'itstore_mm_promo' => [
                'title' => Configuration::get('ITSTORE_MM_PROMO_TITLE'),
                'text' => Configuration::get('ITSTORE_MM_PROMO_TEXT'),
                'url' => Configuration::get('ITSTORE_MM_PROMO_URL'),
                'img' => Configuration::get('ITSTORE_MM_PROMO_IMG'),
            ],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/megamenu.tpl');
    }

    protected function getTree()
    {
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;
        $root = (int) Configuration::get('ITSTORE_MM_ROOT');
        if ($root <= 0) {
            $root = (int) Configuration::get('PS_HOME_CATEGORY');
        }
        $depth = (int) Configuration::get('ITSTORE_MM_DEPTH');
        if ($depth <= 0) {
            $depth = 8;
        }

        $departments = Category::getChildren($root, $idLang, true, $idShop);
        if (!is_array($departments)) {
            return [];
        }

        $tree = [];
        foreach (array_slice($departments, 0, $depth) as $dep) {
            $children = Category::getChildren((int) $dep['id_category'], $idLang, true, $idShop);
            $subs = [];
            foreach ((is_array($children) ? array_slice($children, 0, 12) : []) as $c) {
                $subs[] = [
                    'name' => $c['name'],
                    'url' => $this->context->link->getCategoryLink((int) $c['id_category'], $c['link_rewrite']),
                ];
            }
            $tree[] = [
                'name' => $dep['name'],
                'url' => $this->context->link->getCategoryLink((int) $dep['id_category'], $dep['link_rewrite']),
                'subs' => $subs,
            ];
        }

        return $tree;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreMm')) {
            $url = trim(Tools::getValue('ITSTORE_MM_PROMO_URL'));
            if ($url !== '' && !Validate::isUrlOrEmpty($url)) {
                $output .= $this->displayError($this->trans('The promo URL is not valid.', [], 'Modules.Itstoremegamenu.Admin'));
            } else {
                Configuration::updateValue('ITSTORE_MM_ROOT', (int) Tools::getValue('ITSTORE_MM_ROOT'));
                Configuration::updateValue('ITSTORE_MM_DEPTH', (int) Tools::getValue('ITSTORE_MM_DEPTH'));
                Configuration::updateValue('ITSTORE_MM_PROMO_TITLE', Tools::getValue('ITSTORE_MM_PROMO_TITLE'));
                Configuration::updateValue('ITSTORE_MM_PROMO_TEXT', Tools::getValue('ITSTORE_MM_PROMO_TEXT'));
                Configuration::updateValue('ITSTORE_MM_PROMO_URL', $url);
                Configuration::updateValue('ITSTORE_MM_PROMO_IMG', Tools::getValue('ITSTORE_MM_PROMO_IMG'));
                $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoremegamenu.Admin'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Mega menu', [], 'Modules.Itstoremegamenu.Admin'), 'icon' => 'icon-sitemap'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Root category ID', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_ROOT', 'class' => 'fixed-width-sm', 'desc' => $this->trans('Departments = children of this category. Defaults to Home.', [], 'Modules.Itstoremegamenu.Admin')],
                ['type' => 'text', 'label' => $this->trans('Max departments', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_DEPTH', 'class' => 'fixed-width-sm'],
                ['type' => 'text', 'label' => $this->trans('Promo title', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_PROMO_TITLE'],
                ['type' => 'text', 'label' => $this->trans('Promo text', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_PROMO_TEXT'],
                ['type' => 'text', 'label' => $this->trans('Promo image URL', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_PROMO_IMG'],
                ['type' => 'text', 'label' => $this->trans('Promo link URL', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'ITSTORE_MM_PROMO_URL'],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoremegamenu.Admin'), 'name' => 'submitItstoreMm'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreMm';
        $helper->fields_value = [
            'ITSTORE_MM_ROOT' => (int) Configuration::get('ITSTORE_MM_ROOT'),
            'ITSTORE_MM_DEPTH' => (int) Configuration::get('ITSTORE_MM_DEPTH'),
            'ITSTORE_MM_PROMO_TITLE' => Configuration::get('ITSTORE_MM_PROMO_TITLE'),
            'ITSTORE_MM_PROMO_TEXT' => Configuration::get('ITSTORE_MM_PROMO_TEXT'),
            'ITSTORE_MM_PROMO_URL' => Configuration::get('ITSTORE_MM_PROMO_URL'),
            'ITSTORE_MM_PROMO_IMG' => Configuration::get('ITSTORE_MM_PROMO_IMG'),
        ];

        return $helper->generateForm([$form]);
    }
}
