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

        $this->displayName = $this->l('IT Store Hero');
        $this->description = $this->l('Dark gradient home hero with a Business / Gamer audience toggle.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_HERO_BULK_LINK' => '',
            // Business view
            'ITSTORE_HERO_B_EYEBROW' => $this->l('Deal of the Month'),
            'ITSTORE_HERO_B_TITLE' => $this->l('Outfit your whole office in one order'),
            'ITSTORE_HERO_B_SUB' => $this->l('Business desktops, laptops and network kits — pre-configured, bulk-priced and dispatched same day.'),
            'ITSTORE_HERO_B_CTA' => $this->l('Shop business gear'),
            'ITSTORE_HERO_B_LINK' => '',
            'ITSTORE_HERO_B_IMG' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80',
            // Gamer / home view
            'ITSTORE_HERO_G_EYEBROW' => $this->l('Build of the Month'),
            'ITSTORE_HERO_G_TITLE' => $this->l('Build your dream rig'),
            'ITSTORE_HERO_G_SUB' => $this->l('RTX-ready desktops, high-refresh monitors and the components to push every frame.'),
            'ITSTORE_HERO_G_CTA' => $this->l('Shop gaming'),
            'ITSTORE_HERO_G_LINK' => '',
            'ITSTORE_HERO_G_IMG' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=1200&q=80',
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
            ['position' => 'bottom', 'priority' => 100]
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
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $mk = function ($name, $label) {
            return ['type' => 'text', 'label' => $label, 'name' => $name];
        };
        $inputs = [
            $mk('ITSTORE_HERO_BULK_LINK', $this->l('“Get Bulk Quote” link')),
            $mk('ITSTORE_HERO_B_EYEBROW', $this->l('Business — eyebrow')),
            $mk('ITSTORE_HERO_B_TITLE', $this->l('Business — title')),
            $mk('ITSTORE_HERO_B_SUB', $this->l('Business — subtitle')),
            $mk('ITSTORE_HERO_B_CTA', $this->l('Business — CTA text')),
            $mk('ITSTORE_HERO_B_LINK', $this->l('Business — CTA link')),
            $mk('ITSTORE_HERO_B_IMG', $this->l('Business — image URL')),
            $mk('ITSTORE_HERO_G_EYEBROW', $this->l('Gamer — eyebrow')),
            $mk('ITSTORE_HERO_G_TITLE', $this->l('Gamer — title')),
            $mk('ITSTORE_HERO_G_SUB', $this->l('Gamer — subtitle')),
            $mk('ITSTORE_HERO_G_CTA', $this->l('Gamer — CTA text')),
            $mk('ITSTORE_HERO_G_LINK', $this->l('Gamer — CTA link')),
            $mk('ITSTORE_HERO_G_IMG', $this->l('Gamer — image URL')),
        ];

        $form = ['form' => [
            'legend' => ['title' => $this->l('Hero'), 'icon' => 'icon-picture'],
            'input' => $inputs,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreHero'],
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
