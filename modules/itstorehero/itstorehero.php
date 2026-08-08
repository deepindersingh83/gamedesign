<?php
/**
 * IT Store — Home hero.
 *
 * The design's "Deal of the Month" hero: a dark gradient banner with an
 * audience toggle (For Business / For Gamers & Home) that swaps the headline,
 * subtitle and CTA, a primary CTA + "Get Bulk Quote", and a product image.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorehero extends Module
{
    public function __construct()
    {
        $this->name = 'itstorehero';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Hero', [], 'Modules.Itstorehero.Admin');
        $this->description = $this->trans('Dark gradient home hero with a Business / Gamer audience toggle.', [], 'Modules.Itstorehero.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_HERO_BULK_LINK' => '',
            // Business view
            'ITSTORE_HERO_B_EYEBROW' => $this->trans('Deal of the Month', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_B_TITLE' => $this->trans('Outfit your whole office in one order', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_B_SUB' => $this->trans('Business desktops, laptops and network kits — pre-configured, bulk-priced and dispatched same day.', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_B_CTA' => $this->trans('Shop business gear', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_B_LINK' => '',
            'ITSTORE_HERO_B_IMG' => '',
            // Gamer / home view
            'ITSTORE_HERO_G_EYEBROW' => $this->trans('Build of the Month', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_G_TITLE' => $this->trans('Build your dream rig', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_G_SUB' => $this->trans('RTX-ready desktops, high-refresh monitors and the components to push every frame.', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_G_CTA' => $this->trans('Shop gaming', [], 'Modules.Itstorehero.Admin'),
            'ITSTORE_HERO_G_LINK' => '',
            'ITSTORE_HERO_G_IMG' => '',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
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
            'itstore-hero',
            'modules/' . $this->name . '/views/css/hero.css',
            ['media' => 'all', 'priority' => 100]
        );
        $this->context->controller->registerJavascript(
            'itstore-hero',
            'modules/' . $this->name . '/views/js/hero.js',
            ['position' => 'bottom', 'priority' => 100, 'attribute' => 'defer']
        );
    }

    public function hookDisplayHome($params)
    {
        $bulk = Configuration::get('ITSTORE_HERO_BULK_LINK');
        $this->smarty->assign([
            'hero_bulk_link' => $bulk ?: $this->context->link->getPageLink('contact', true),
            'hero_business' => [
                'eyebrow' => Configuration::get('ITSTORE_HERO_B_EYEBROW'),
                'title' => Configuration::get('ITSTORE_HERO_B_TITLE'),
                'sub' => Configuration::get('ITSTORE_HERO_B_SUB'),
                'cta' => Configuration::get('ITSTORE_HERO_B_CTA'),
                'link' => Configuration::get('ITSTORE_HERO_B_LINK') ?: '#',
                'img' => Configuration::get('ITSTORE_HERO_B_IMG'),
            ],
            'hero_gamer' => [
                'eyebrow' => Configuration::get('ITSTORE_HERO_G_EYEBROW'),
                'title' => Configuration::get('ITSTORE_HERO_G_TITLE'),
                'sub' => Configuration::get('ITSTORE_HERO_G_SUB'),
                'cta' => Configuration::get('ITSTORE_HERO_G_CTA'),
                'link' => Configuration::get('ITSTORE_HERO_G_LINK') ?: '#',
                'img' => Configuration::get('ITSTORE_HERO_G_IMG'),
            ],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/hero.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreHero')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorehero.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $mk = function ($name, $label) {
            return ['type' => 'text', 'label' => $label, 'name' => $name];
        };
        $inputs = [
            $mk('ITSTORE_HERO_BULK_LINK', $this->trans('“Get Bulk Quote” link', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_EYEBROW', $this->trans('Business — eyebrow', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_TITLE', $this->trans('Business — title', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_SUB', $this->trans('Business — subtitle', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_CTA', $this->trans('Business — CTA text', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_LINK', $this->trans('Business — CTA link', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_B_IMG', $this->trans('Business — image URL', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_EYEBROW', $this->trans('Gamer — eyebrow', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_TITLE', $this->trans('Gamer — title', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_SUB', $this->trans('Gamer — subtitle', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_CTA', $this->trans('Gamer — CTA text', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_LINK', $this->trans('Gamer — CTA link', [], 'Modules.Itstorehero.Admin')),
            $mk('ITSTORE_HERO_G_IMG', $this->trans('Gamer — image URL', [], 'Modules.Itstorehero.Admin')),
        ];

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Hero', [], 'Modules.Itstorehero.Admin'), 'icon' => 'icon-picture'],
            'input' => $inputs,
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorehero.Admin'), 'name' => 'submitItstoreHero'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreHero';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
