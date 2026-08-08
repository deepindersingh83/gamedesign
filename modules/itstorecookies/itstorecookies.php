<?php
/**
 * IT Store — Cookie consent.
 *
 * A lightweight, dependency-free cookie-consent banner shown until the visitor
 * accepts or declines. The choice is stored in a first-party cookie; a
 * JavaScript event (`itstore:consent`) is dispatched so other scripts can gate
 * non-essential trackers on it. Text and the policy link are configurable.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorecookies extends Module
{
    public function __construct()
    {
        $this->name = 'itstorecookies';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Cookie Consent', [], 'Modules.Itstorecookies.Admin');
        $this->description = $this->trans('A configurable cookie-consent banner with accept/decline and a consent event for gating trackers.', [], 'Modules.Itstorecookies.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_CK_ENABLED' => 1,
            'ITSTORE_CK_TEXT' => $this->trans('We use cookies to run the shop, remember your cart and improve your experience.', [], 'Modules.Itstorecookies.Admin'),
            'ITSTORE_CK_ACCEPT' => $this->trans('Accept', [], 'Modules.Itstorecookies.Admin'),
            'ITSTORE_CK_DECLINE' => $this->trans('Decline', [], 'Modules.Itstorecookies.Admin'),
            'ITSTORE_CK_LINK_TEXT' => $this->trans('Privacy & cookies', [], 'Modules.Itstorecookies.Admin'),
            'ITSTORE_CK_CMS' => 0,
            'ITSTORE_CK_DAYS' => 180,
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayFooterAfter')
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
        if (!(int) Configuration::get('ITSTORE_CK_ENABLED')) {
            return;
        }
        $this->context->controller->registerStylesheet(
            'itstore-cookies',
            'modules/' . $this->name . '/views/css/cookies.css',
            ['media' => 'all', 'priority' => 200]
        );
        $this->context->controller->registerJavascript(
            'itstore-cookies',
            'modules/' . $this->name . '/views/js/cookies.js',
            ['position' => 'bottom', 'priority' => 200, 'attribute' => 'defer']
        );
    }

    public function hookDisplayFooterAfter($params)
    {
        if (!(int) Configuration::get('ITSTORE_CK_ENABLED')) {
            return '';
        }

        $link = '';
        $idCms = (int) Configuration::get('ITSTORE_CK_CMS');
        if ($idCms > 0) {
            $link = $this->context->link->getCMSLink($idCms);
        }

        $this->smarty->assign([
            'ck_text' => Configuration::get('ITSTORE_CK_TEXT'),
            'ck_accept' => Configuration::get('ITSTORE_CK_ACCEPT'),
            'ck_decline' => Configuration::get('ITSTORE_CK_DECLINE'),
            'ck_link_text' => Configuration::get('ITSTORE_CK_LINK_TEXT'),
            'ck_link' => $link,
            'ck_days' => (int) Configuration::get('ITSTORE_CK_DAYS') ?: 180,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/banner.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreCk')) {
            Configuration::updateValue('ITSTORE_CK_ENABLED', (int) Tools::getValue('ITSTORE_CK_ENABLED'));
            Configuration::updateValue('ITSTORE_CK_TEXT', Tools::getValue('ITSTORE_CK_TEXT'));
            Configuration::updateValue('ITSTORE_CK_ACCEPT', Tools::getValue('ITSTORE_CK_ACCEPT'));
            Configuration::updateValue('ITSTORE_CK_DECLINE', Tools::getValue('ITSTORE_CK_DECLINE'));
            Configuration::updateValue('ITSTORE_CK_LINK_TEXT', Tools::getValue('ITSTORE_CK_LINK_TEXT'));
            Configuration::updateValue('ITSTORE_CK_CMS', (int) Tools::getValue('ITSTORE_CK_CMS'));
            Configuration::updateValue('ITSTORE_CK_DAYS', (int) Tools::getValue('ITSTORE_CK_DAYS'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorecookies.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function cmsOptions()
    {
        $options = [['id' => 0, 'name' => $this->trans('— none —', [], 'Modules.Itstorecookies.Admin')]];
        $pages = CMS::getCMSPages((int) $this->context->language->id) ?: [];
        foreach ($pages as $p) {
            $options[] = ['id' => (int) $p['id_cms'], 'name' => $p['meta_title']];
        }

        return $options;
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Cookie consent', [], 'Modules.Itstorecookies.Admin'), 'icon' => 'icon-legal'],
            'input' => [
                [
                    'type' => 'switch', 'label' => $this->trans('Show the banner', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_ENABLED', 'is_bool' => true,
                    'values' => [
                        ['id' => 'ck_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstorecookies.Admin')],
                        ['id' => 'ck_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstorecookies.Admin')],
                    ],
                ],
                ['type' => 'textarea', 'label' => $this->trans('Message', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_TEXT', 'rows' => 2],
                ['type' => 'text', 'label' => $this->trans('Accept button', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_ACCEPT', 'col' => 3],
                ['type' => 'text', 'label' => $this->trans('Decline button', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_DECLINE', 'col' => 3],
                ['type' => 'text', 'label' => $this->trans('Policy link text', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_LINK_TEXT', 'col' => 4],
                [
                    'type' => 'select', 'label' => $this->trans('Policy CMS page', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_CMS',
                    'options' => ['query' => $this->cmsOptions(), 'id' => 'id', 'name' => 'name'],
                ],
                ['type' => 'text', 'label' => $this->trans('Remember choice (days)', [], 'Modules.Itstorecookies.Admin'), 'name' => 'ITSTORE_CK_DAYS', 'col' => 2],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorecookies.Admin'), 'name' => 'submitItstoreCk'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreCk';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
