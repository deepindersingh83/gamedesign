<?php
/**
 * IT Store — Header utility top bar.
 *
 * The design's slim top bar: Track Order / Business Accounts / quick contact.
 * Added additively via `displayNav1` (the classic header's top-left slot) so it
 * augments the existing header rather than overriding the template.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoretopbar extends Module
{
    public function __construct()
    {
        $this->name = 'itstoretopbar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Top Bar');
        $this->description = $this->l('Slim header utility bar: Track Order, Business Accounts, contact.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_TOPBAR_MSG' => $this->l('Free next-day delivery on in-stock orders over $99'),
            'ITSTORE_TOPBAR_TRACK' => '',
            'ITSTORE_TOPBAR_BIZ' => '',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayNav1')
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
            'itstore-topbar',
            'modules/' . $this->name . '/views/css/topbar.css',
            ['media' => 'all', 'priority' => 95]
        );
    }

    public function hookDisplayNav1($params)
    {
        $track = Configuration::get('ITSTORE_TOPBAR_TRACK');
        $biz = Configuration::get('ITSTORE_TOPBAR_BIZ');

        $this->smarty->assign([
            'topbar_msg' => Configuration::get('ITSTORE_TOPBAR_MSG'),
            'topbar_track' => $track ?: $this->context->link->getModuleLink('itstoreordertrack', 'track', [], true),
            'topbar_biz' => $biz ?: $this->context->link->getPageLink('authentication', true, null, 'create_account=1'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/topbar.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreTopbar')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        $fields = [
            ['type' => 'text', 'label' => $this->l('Promo message'), 'name' => 'ITSTORE_TOPBAR_MSG'],
            ['type' => 'text', 'label' => $this->l('Track Order URL'), 'name' => 'ITSTORE_TOPBAR_TRACK', 'desc' => $this->l('Defaults to the IT Store order-tracking page.')],
            ['type' => 'text', 'label' => $this->l('Business Accounts URL'), 'name' => 'ITSTORE_TOPBAR_BIZ'],
        ];
        $form = ['form' => [
            'legend' => ['title' => $this->l('Top bar'), 'icon' => 'icon-minus'],
            'input' => $fields,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreTopbar'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreTopbar';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $output . $helper->generateForm([$form]);
    }
}
