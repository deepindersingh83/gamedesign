<?php
/**
 * IT Store — Trust / USP bar.
 *
 * A site-wide strip of reassurance points (free shipping, warranty, secure
 * payment, expert support). Rendered on `displayWrapperTop` so it appears just
 * under the header on every page. Items are configurable in the back office.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoretrustbar extends Module
{
    /** Number of configurable items. */
    const ITEMS = 4;

    public function __construct()
    {
        $this->name = 'itstoretrustbar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Trust Bar', [], 'Modules.Itstoretrustbar.Admin');
        $this->description = $this->trans('Site-wide bar of trust / USP points (shipping, warranty, payment, support).', [], 'Modules.Itstoretrustbar.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_TB_ENABLED' => 1,
            'ITSTORE_TB_1_ICON' => 'local_shipping', 'ITSTORE_TB_1_TITLE' => $this->trans('Free & fast delivery', [], 'Modules.Itstoretrustbar.Admin'), 'ITSTORE_TB_1_TEXT' => $this->trans('On orders over $99', [], 'Modules.Itstoretrustbar.Admin'),
            'ITSTORE_TB_2_ICON' => 'verified_user', 'ITSTORE_TB_2_TITLE' => $this->trans('Genuine warranty', [], 'Modules.Itstoretrustbar.Admin'), 'ITSTORE_TB_2_TEXT' => $this->trans('Full manufacturer cover', [], 'Modules.Itstoretrustbar.Admin'),
            'ITSTORE_TB_3_ICON' => 'lock', 'ITSTORE_TB_3_TITLE' => $this->trans('Secure payment', [], 'Modules.Itstoretrustbar.Admin'), 'ITSTORE_TB_3_TEXT' => $this->trans('Encrypted checkout', [], 'Modules.Itstoretrustbar.Admin'),
            'ITSTORE_TB_4_ICON' => 'support_agent', 'ITSTORE_TB_4_TITLE' => $this->trans('Expert support', [], 'Modules.Itstoretrustbar.Admin'), 'ITSTORE_TB_4_TEXT' => $this->trans('Real techs, local team', [], 'Modules.Itstoretrustbar.Admin'),
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayWrapperTop')
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
            'itstore-trustbar',
            'modules/' . $this->name . '/views/css/trustbar.css',
            ['media' => 'all', 'priority' => 110]
        );
    }

    public function hookDisplayWrapperTop($params)
    {
        if (!(int) Configuration::get('ITSTORE_TB_ENABLED')) {
            return '';
        }

        $items = [];
        for ($i = 1; $i <= self::ITEMS; $i++) {
            $title = Configuration::get('ITSTORE_TB_' . $i . '_TITLE');
            if ($title === false || $title === '') {
                continue;
            }
            $items[] = [
                'icon' => Configuration::get('ITSTORE_TB_' . $i . '_ICON'),
                'title' => $title,
                'text' => Configuration::get('ITSTORE_TB_' . $i . '_TEXT'),
            ];
        }

        if (empty($items)) {
            return '';
        }

        $this->smarty->assign('itstore_tb_items', $items);

        return $this->display(__FILE__, 'views/templates/hook/trustbar.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreTrustbar')) {
            Configuration::updateValue('ITSTORE_TB_ENABLED', (int) Tools::getValue('ITSTORE_TB_ENABLED'));
            for ($i = 1; $i <= self::ITEMS; $i++) {
                Configuration::updateValue('ITSTORE_TB_' . $i . '_ICON', Tools::getValue('ITSTORE_TB_' . $i . '_ICON'));
                Configuration::updateValue('ITSTORE_TB_' . $i . '_TITLE', Tools::getValue('ITSTORE_TB_' . $i . '_TITLE'));
                Configuration::updateValue('ITSTORE_TB_' . $i . '_TEXT', Tools::getValue('ITSTORE_TB_' . $i . '_TEXT'));
            }
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoretrustbar.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $inputs = [[
            'type' => 'switch', 'label' => $this->trans('Enable trust bar', [], 'Modules.Itstoretrustbar.Admin'), 'name' => 'ITSTORE_TB_ENABLED', 'is_bool' => true,
            'values' => [
                ['id' => 'tb_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoretrustbar.Admin')],
                ['id' => 'tb_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoretrustbar.Admin')],
            ],
        ]];
        for ($i = 1; $i <= self::ITEMS; $i++) {
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->trans('Item %d — icon', [], 'Modules.Itstoretrustbar.Admin'), $i), 'name' => 'ITSTORE_TB_' . $i . '_ICON', 'desc' => $this->trans('Material Symbols name, e.g. local_shipping, lock, support_agent.', [], 'Modules.Itstoretrustbar.Admin'), 'class' => 'fixed-width-lg'];
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->trans('Item %d — title', [], 'Modules.Itstoretrustbar.Admin'), $i), 'name' => 'ITSTORE_TB_' . $i . '_TITLE'];
            $inputs[] = ['type' => 'text', 'label' => sprintf($this->trans('Item %d — text', [], 'Modules.Itstoretrustbar.Admin'), $i), 'name' => 'ITSTORE_TB_' . $i . '_TEXT'];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Trust bar items', [], 'Modules.Itstoretrustbar.Admin'), 'icon' => 'icon-shield'],
            'input' => $inputs,
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoretrustbar.Admin'), 'name' => 'submitItstoreTrustbar'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreTrustbar';

        $values = ['ITSTORE_TB_ENABLED' => (int) Configuration::get('ITSTORE_TB_ENABLED')];
        for ($i = 1; $i <= self::ITEMS; $i++) {
            $values['ITSTORE_TB_' . $i . '_ICON'] = Configuration::get('ITSTORE_TB_' . $i . '_ICON');
            $values['ITSTORE_TB_' . $i . '_TITLE'] = Configuration::get('ITSTORE_TB_' . $i . '_TITLE');
            $values['ITSTORE_TB_' . $i . '_TEXT'] = Configuration::get('ITSTORE_TB_' . $i . '_TEXT');
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
