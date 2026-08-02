<?php
/**
 * IT Store — Extended warranty upsell.
 *
 * Presents extended-warranty tiers on the product page. Each tier can be mapped
 * to an existing "warranty" product so it is added to the cart with one click;
 * otherwise the tier is shown as informational.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorewarranty extends Module
{
    const TIERS = 3;

    public function __construct()
    {
        $this->name = 'itstorewarranty';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Warranty');
        $this->description = $this->l('Extended-warranty upsell tiers on the product page.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_WR_ENABLED' => 1,
            'ITSTORE_WR_1_LABEL' => $this->l('+1 year cover'), 'ITSTORE_WR_1_PRICE' => '29', 'ITSTORE_WR_1_PID' => 0,
            'ITSTORE_WR_2_LABEL' => $this->l('+2 years cover'), 'ITSTORE_WR_2_PRICE' => '49', 'ITSTORE_WR_2_PID' => 0,
            'ITSTORE_WR_3_LABEL' => $this->l('+3 years cover'), 'ITSTORE_WR_3_PRICE' => '69', 'ITSTORE_WR_3_PID' => 0,
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
                'itstore-warranty',
                'modules/' . $this->name . '/views/css/warranty.css',
                ['media' => 'all', 'priority' => 144]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int) Configuration::get('ITSTORE_WR_ENABLED')) {
            return '';
        }

        $tiers = [];
        for ($i = 1; $i <= self::TIERS; $i++) {
            $label = Configuration::get('ITSTORE_WR_' . $i . '_LABEL');
            if ($label === false || $label === '') {
                continue;
            }
            $pid = (int) Configuration::get('ITSTORE_WR_' . $i . '_PID');
            $tiers[] = [
                'label' => $label,
                'price' => Configuration::get('ITSTORE_WR_' . $i . '_PRICE'),
                'add_url' => $pid > 0 ? $this->cartAddUrl($pid) : '',
            ];
        }
        if (empty($tiers)) {
            return '';
        }

        $this->smarty->assign('itstore_wr_tiers', $tiers);

        return $this->display(__FILE__, 'views/templates/hook/warranty.tpl');
    }

    protected function cartAddUrl($idProduct)
    {
        return $this->context->link->getPageLink(
            'cart',
            true,
            null,
            ['add' => 1, 'id_product' => (int) $idProduct, 'token' => Tools::getToken(false)]
        );
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreWr')) {
            Configuration::updateValue('ITSTORE_WR_ENABLED', (int) Tools::getValue('ITSTORE_WR_ENABLED'));
            for ($i = 1; $i <= self::TIERS; $i++) {
                Configuration::updateValue('ITSTORE_WR_' . $i . '_LABEL', Tools::getValue('ITSTORE_WR_' . $i . '_LABEL'));
                Configuration::updateValue('ITSTORE_WR_' . $i . '_PRICE', Tools::getValue('ITSTORE_WR_' . $i . '_PRICE'));
                Configuration::updateValue('ITSTORE_WR_' . $i . '_PID', (int) Tools::getValue('ITSTORE_WR_' . $i . '_PID'));
            }
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $inputs = [[
            'type' => 'switch', 'label' => $this->l('Enabled'), 'name' => 'ITSTORE_WR_ENABLED', 'is_bool' => true,
            'values' => [
                ['id' => 'wr_on', 'value' => 1, 'label' => $this->l('Yes')],
                ['id' => 'wr_off', 'value' => 0, 'label' => $this->l('No')],
            ],
        ]];
        for ($i = 1; $i <= self::TIERS; $i++) {
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->l('Tier %d — label'), $i), 'name' => 'ITSTORE_WR_' . $i . '_LABEL'];
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->l('Tier %d — price'), $i), 'name' => 'ITSTORE_WR_' . $i . '_PRICE', 'class' => 'fixed-width-sm'];
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->l('Tier %d — product ID'), $i), 'name' => 'ITSTORE_WR_' . $i . '_PID', 'class' => 'fixed-width-sm', 'desc' => $this->l('Optional: a warranty product added to cart when chosen.')];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('Warranty tiers'), 'icon' => 'icon-shield'],
            'input' => $inputs,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreWr'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreWr';

        $values = ['ITSTORE_WR_ENABLED' => (int) Configuration::get('ITSTORE_WR_ENABLED')];
        for ($i = 1; $i <= self::TIERS; $i++) {
            $values['ITSTORE_WR_' . $i . '_LABEL'] = Configuration::get('ITSTORE_WR_' . $i . '_LABEL');
            $values['ITSTORE_WR_' . $i . '_PRICE'] = Configuration::get('ITSTORE_WR_' . $i . '_PRICE');
            $values['ITSTORE_WR_' . $i . '_PID'] = (int) Configuration::get('ITSTORE_WR_' . $i . '_PID');
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
