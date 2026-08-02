<?php
/**
 * IT Store — Custom PC builder.
 *
 * A guided builder that maps each component slot (CPU, motherboard, RAM, GPU,
 * storage, PSU, case) to a category. Shoppers pick one product per slot, see a
 * live running total and add the whole build to the cart in one go.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorepcbuilder extends Module
{
    public function __construct()
    {
        $this->name = 'itstorepcbuilder';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store PC Builder');
        $this->description = $this->l('Guided custom-PC builder mapping component slots to categories.');
    }

    /** Component slots: key => label. */
    public function slots()
    {
        return [
            'CPU' => $this->l('Processor (CPU)'),
            'MB' => $this->l('Motherboard'),
            'RAM' => $this->l('Memory (RAM)'),
            'GPU' => $this->l('Graphics card'),
            'STO' => $this->l('Storage'),
            'PSU' => $this->l('Power supply'),
            'CASE' => $this->l('Case'),
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        foreach (array_keys($this->slots()) as $k) {
            Configuration::updateValue('ITSTORE_PB_' . $k, 0);
        }
        Configuration::updateValue('ITSTORE_PB_CTA', $this->l('Build your own PC'));
        Configuration::updateValue('ITSTORE_PB_COMPAT', 'Socket');

        return true;
    }

    public function uninstall()
    {
        foreach (array_keys($this->slots()) as $k) {
            Configuration::deleteByName('ITSTORE_PB_' . $k);
        }
        Configuration::deleteByName('ITSTORE_PB_CTA');
        Configuration::deleteByName('ITSTORE_PB_COMPAT');

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-pcbuilder',
            'modules/' . $this->name . '/views/css/pcbuilder.css',
            ['media' => 'all', 'priority' => 152]
        );
        $this->context->controller->registerJavascript(
            'itstore-pcbuilder',
            'modules/' . $this->name . '/views/js/pcbuilder.js',
            ['position' => 'bottom', 'priority' => 152]
        );
    }

    /** A CTA banner on the home page linking to the builder. */
    public function hookDisplayHome($params)
    {
        $this->smarty->assign([
            'itstore_pb_cta' => Configuration::get('ITSTORE_PB_CTA'),
            'itstore_pb_url' => $this->context->link->getModuleLink($this->name, 'builder', [], true),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/cta.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstorePb')) {
            foreach (array_keys($this->slots()) as $k) {
                Configuration::updateValue('ITSTORE_PB_' . $k, (int) Tools::getValue('ITSTORE_PB_' . $k));
            }
            Configuration::updateValue('ITSTORE_PB_CTA', Tools::getValue('ITSTORE_PB_CTA'));
            Configuration::updateValue('ITSTORE_PB_COMPAT', Tools::getValue('ITSTORE_PB_COMPAT'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $inputs = [
            ['type' => 'text', 'label' => $this->l('Home CTA text'), 'name' => 'ITSTORE_PB_CTA'],
            ['type' => 'text', 'label' => $this->l('Compatibility feature'), 'name' => 'ITSTORE_PB_COMPAT', 'desc' => $this->l('Feature name compared between CPU and Motherboard (e.g. Socket). Leave empty to disable.'), 'class' => 'fixed-width-lg'],
        ];
        foreach ($this->slots() as $k => $label) {
            $inputs[] = [
                'type' => 'text',
                'label' => sprintf($this->l('%s — category ID'), $label),
                'name' => 'ITSTORE_PB_' . $k,
                'class' => 'fixed-width-sm',
            ];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('PC builder slots'), 'icon' => 'icon-wrench'],
            'input' => $inputs,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstorePb'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstorePb';

        $values = [
            'ITSTORE_PB_CTA' => Configuration::get('ITSTORE_PB_CTA'),
            'ITSTORE_PB_COMPAT' => Configuration::get('ITSTORE_PB_COMPAT'),
        ];
        foreach (array_keys($this->slots()) as $k) {
            $values['ITSTORE_PB_' . $k] = (int) Configuration::get('ITSTORE_PB_' . $k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
