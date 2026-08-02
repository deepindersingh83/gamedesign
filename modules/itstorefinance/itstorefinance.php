<?php
/**
 * IT Store — Finance messaging.
 *
 * Shows "from $x/month" instalment messaging on the product page, computed from
 * the product price, a configurable term and (optional) APR. Purely
 * informational — links out to your finance/BNPL provider.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorefinance extends Module
{
    public function __construct()
    {
        $this->name = 'itstorefinance';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Finance');
        $this->description = $this->l('“From $x/month” finance messaging on the product page.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_FIN_ENABLED' => 1,
            'ITSTORE_FIN_MONTHS' => 12,
            'ITSTORE_FIN_APR' => 0,
            'ITSTORE_FIN_MIN' => 100,
            'ITSTORE_FIN_PROVIDER' => $this->l('Finance available'),
            'ITSTORE_FIN_URL' => '',
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
                'itstore-finance',
                'modules/' . $this->name . '/views/css/finance.css',
                ['media' => 'all', 'priority' => 141]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int) Configuration::get('ITSTORE_FIN_ENABLED')) {
            return '';
        }

        $price = $this->extractPrice($params);
        $min = (float) Configuration::get('ITSTORE_FIN_MIN');
        if ($price <= 0 || $price < $min) {
            return '';
        }

        $months = max(1, (int) Configuration::get('ITSTORE_FIN_MONTHS'));
        $apr = (float) Configuration::get('ITSTORE_FIN_APR');
        $monthly = $this->monthlyPayment($price, $months, $apr);

        $this->smarty->assign([
            'itstore_fin_monthly' => $this->formatPrice($monthly),
            'itstore_fin_months' => $months,
            'itstore_fin_apr' => $apr,
            'itstore_fin_provider' => Configuration::get('ITSTORE_FIN_PROVIDER'),
            'itstore_fin_url' => Configuration::get('ITSTORE_FIN_URL'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/finance.tpl');
    }

    protected function extractPrice($params)
    {
        if (!isset($params['product'])) {
            return 0.0;
        }
        $p = $params['product'];
        if (is_array($p)) {
            if (isset($p['price_amount'])) {
                return (float) $p['price_amount'];
            }
            if (isset($p['id_product'])) {
                return (float) Product::getPriceStatic((int) $p['id_product'], true);
            }
        } elseif (is_object($p) && isset($p->id)) {
            return (float) Product::getPriceStatic((int) $p->id, true);
        }

        return 0.0;
    }

    protected function monthlyPayment($principal, $months, $apr)
    {
        if ($apr <= 0) {
            return $principal / $months;
        }
        $r = ($apr / 100) / 12;

        return ($principal * $r) / (1 - pow(1 + $r, -$months));
    }

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
        if (Tools::isSubmit('submitItstoreFin')) {
            $url = trim(Tools::getValue('ITSTORE_FIN_URL'));
            if ($url !== '' && !Validate::isUrlOrEmpty($url)) {
                $output .= $this->displayError($this->l('The provider URL is not valid.'));
            } else {
                Configuration::updateValue('ITSTORE_FIN_ENABLED', (int) Tools::getValue('ITSTORE_FIN_ENABLED'));
                Configuration::updateValue('ITSTORE_FIN_MONTHS', (int) Tools::getValue('ITSTORE_FIN_MONTHS'));
                Configuration::updateValue('ITSTORE_FIN_APR', (float) Tools::getValue('ITSTORE_FIN_APR'));
                Configuration::updateValue('ITSTORE_FIN_MIN', (float) Tools::getValue('ITSTORE_FIN_MIN'));
                Configuration::updateValue('ITSTORE_FIN_PROVIDER', Tools::getValue('ITSTORE_FIN_PROVIDER'));
                Configuration::updateValue('ITSTORE_FIN_URL', $url);
                $output .= $this->displayConfirmation($this->l('Settings saved.'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Finance messaging'), 'icon' => 'icon-credit-card'],
            'input' => [
                [
                    'type' => 'switch', 'label' => $this->l('Enabled'), 'name' => 'ITSTORE_FIN_ENABLED', 'is_bool' => true,
                    'values' => [
                        ['id' => 'fin_on', 'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'fin_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
                ['type' => 'text', 'label' => $this->l('Term (months)'), 'name' => 'ITSTORE_FIN_MONTHS', 'class' => 'fixed-width-sm'],
                ['type' => 'text', 'label' => $this->l('APR %'), 'name' => 'ITSTORE_FIN_APR', 'class' => 'fixed-width-sm', 'desc' => $this->l('Set 0 for interest-free.')],
                ['type' => 'text', 'label' => $this->l('Minimum price'), 'name' => 'ITSTORE_FIN_MIN', 'class' => 'fixed-width-sm'],
                ['type' => 'text', 'label' => $this->l('Provider label'), 'name' => 'ITSTORE_FIN_PROVIDER'],
                ['type' => 'text', 'label' => $this->l('Provider URL'), 'name' => 'ITSTORE_FIN_URL'],
            ],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreFin'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreFin';
        $helper->fields_value = [
            'ITSTORE_FIN_ENABLED' => (int) Configuration::get('ITSTORE_FIN_ENABLED'),
            'ITSTORE_FIN_MONTHS' => (int) Configuration::get('ITSTORE_FIN_MONTHS'),
            'ITSTORE_FIN_APR' => (float) Configuration::get('ITSTORE_FIN_APR'),
            'ITSTORE_FIN_MIN' => (float) Configuration::get('ITSTORE_FIN_MIN'),
            'ITSTORE_FIN_PROVIDER' => Configuration::get('ITSTORE_FIN_PROVIDER'),
            'ITSTORE_FIN_URL' => Configuration::get('ITSTORE_FIN_URL'),
        ];

        return $helper->generateForm([$form]);
    }
}
