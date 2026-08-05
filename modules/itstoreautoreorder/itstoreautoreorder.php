<?php
/**
 * IT Store — Subscribe & Save (auto-reorder).
 *
 * The design's "Subscribe & Save 10% — auto-reorder every 3 months" opt-in on
 * the product page. This renders the marketing option and records intent on the
 * cart line; recurring billing itself requires a subscription payment module,
 * so this is presentational + opt-in, not a payment scheduler.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreautoreorder extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreautoreorder';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Subscribe & Save');
        $this->description = $this->l('“Subscribe & Save / auto-reorder” opt-in on the product page.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_AR_ENABLED' => 1,
            'ITSTORE_AR_DISCOUNT' => 10,
            'ITSTORE_AR_INTERVAL' => $this->l('every 3 months, cancel anytime'),
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayProductAdditionalInfo')
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
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-autoreorder',
                'modules/' . $this->name . '/views/css/autoreorder.css',
                ['media' => 'all', 'priority' => 147]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int) Configuration::get('ITSTORE_AR_ENABLED')) {
            return '';
        }
        $this->smarty->assign([
            'ar_discount' => (int) Configuration::get('ITSTORE_AR_DISCOUNT'),
            'ar_interval' => Configuration::get('ITSTORE_AR_INTERVAL'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/autoreorder.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreAr')) {
            Configuration::updateValue('ITSTORE_AR_ENABLED', (int) Tools::getValue('ITSTORE_AR_ENABLED'));
            Configuration::updateValue('ITSTORE_AR_DISCOUNT', (int) Tools::getValue('ITSTORE_AR_DISCOUNT'));
            Configuration::updateValue('ITSTORE_AR_INTERVAL', Tools::getValue('ITSTORE_AR_INTERVAL'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Subscribe & Save'), 'icon' => 'icon-refresh'],
            'input' => [
                [
                    'type' => 'switch', 'label' => $this->l('Enabled'), 'name' => 'ITSTORE_AR_ENABLED', 'is_bool' => true,
                    'values' => [
                        ['id' => 'ar_on', 'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'ar_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
                ['type' => 'text', 'label' => $this->l('Discount %'), 'name' => 'ITSTORE_AR_DISCOUNT', 'class' => 'fixed-width-sm'],
                ['type' => 'text', 'label' => $this->l('Interval text'), 'name' => 'ITSTORE_AR_INTERVAL'],
            ],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreAr'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreAr';
        $helper->fields_value = [
            'ITSTORE_AR_ENABLED' => (int) Configuration::get('ITSTORE_AR_ENABLED'),
            'ITSTORE_AR_DISCOUNT' => (int) Configuration::get('ITSTORE_AR_DISCOUNT'),
            'ITSTORE_AR_INTERVAL' => Configuration::get('ITSTORE_AR_INTERVAL'),
        ];

        return $helper->generateForm([$form]);
    }
}
