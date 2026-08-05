<?php
/**
 * IT Store — Testimonials.
 *
 * The design's "What Our Customers Say" 3-card grid: star row, quote, avatar +
 * name + role. Up to six configurable testimonials.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoretestimonials extends Module
{
    const N = 6;

    public function __construct()
    {
        $this->name = 'itstoretestimonials';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Testimonials');
        $this->description = $this->l('“What Our Customers Say” testimonials grid.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_TS_TITLE' => $this->l('What Our Customers Say'),
            'ITSTORE_TS_1_TEXT' => $this->l('Kitted out our whole new office — 40 desktops delivered and set up in two days. Faultless.'),
            'ITSTORE_TS_1_NAME' => 'Priya N.', 'ITSTORE_TS_1_ROLE' => $this->l('IT Manager, Meridian Group'), 'ITSTORE_TS_1_IMG' => '',
            'ITSTORE_TS_2_TEXT' => $this->l('My gaming build arrived cable-managed and stress-tested. Runs cool and quiet under load.'),
            'ITSTORE_TS_2_NAME' => 'Jordan R.', 'ITSTORE_TS_2_ROLE' => $this->l('Verified buyer'), 'ITSTORE_TS_2_IMG' => '',
            'ITSTORE_TS_3_TEXT' => $this->l('The bulk pricing and same-day dispatch make these the only supplier we use now.'),
            'ITSTORE_TS_3_NAME' => 'Sam K.', 'ITSTORE_TS_3_ROLE' => $this->l('Operations, Northwind IT'), 'ITSTORE_TS_3_IMG' => '',
            'ITSTORE_TS_4_TEXT' => '', 'ITSTORE_TS_4_NAME' => '', 'ITSTORE_TS_4_ROLE' => '', 'ITSTORE_TS_4_IMG' => '',
            'ITSTORE_TS_5_TEXT' => '', 'ITSTORE_TS_5_NAME' => '', 'ITSTORE_TS_5_ROLE' => '', 'ITSTORE_TS_5_IMG' => '',
            'ITSTORE_TS_6_TEXT' => '', 'ITSTORE_TS_6_NAME' => '', 'ITSTORE_TS_6_ROLE' => '', 'ITSTORE_TS_6_IMG' => '',
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
            'itstore-testimonials',
            'modules/' . $this->name . '/views/css/testimonials.css',
            ['media' => 'all', 'priority' => 121]
        );
    }

    public function hookDisplayHome($params)
    {
        $items = [];
        for ($i = 1; $i <= self::N; $i++) {
            $text = Configuration::get('ITSTORE_TS_' . $i . '_TEXT');
            if ($text === false || trim($text) === '') {
                continue;
            }
            $name = (string) Configuration::get('ITSTORE_TS_' . $i . '_NAME');
            $items[] = [
                'text' => $text,
                'name' => $name,
                'role' => Configuration::get('ITSTORE_TS_' . $i . '_ROLE'),
                'img' => Configuration::get('ITSTORE_TS_' . $i . '_IMG'),
                'initial' => $name !== '' ? Tools::strtoupper(Tools::substr($name, 0, 1)) : '★',
            ];
        }
        if (empty($items)) {
            return '';
        }
        $this->smarty->assign([
            'ts_title' => Configuration::get('ITSTORE_TS_TITLE'),
            'ts_items' => $items,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/testimonials.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreTs')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [['type' => 'text', 'label' => $this->l('Block title'), 'name' => 'ITSTORE_TS_TITLE']];
        for ($i = 1; $i <= self::N; $i++) {
            $fields[] = ['type' => 'textarea', 'label' => sprintf($this->l('Testimonial %d — quote'), $i), 'name' => 'ITSTORE_TS_' . $i . '_TEXT'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->l('Testimonial %d — name'), $i), 'name' => 'ITSTORE_TS_' . $i . '_NAME'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->l('Testimonial %d — role'), $i), 'name' => 'ITSTORE_TS_' . $i . '_ROLE'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->l('Testimonial %d — avatar URL'), $i), 'name' => 'ITSTORE_TS_' . $i . '_IMG'];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('Testimonials'), 'icon' => 'icon-quote-left'],
            'input' => $fields,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreTs'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreTs';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
