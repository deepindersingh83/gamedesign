<?php
/**
 * IT Store — Stats band.
 *
 * The design's proof-point strip: four big figures + labels (reviews, customers
 * supplied, dispatch, warranty), with an on-scroll count-up for numeric values.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorestats extends Module
{
    const N = 4;

    public function __construct()
    {
        $this->name = 'itstorestats';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Stats');
        $this->description = $this->l('Trust / proof-point stats band for the home page.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_ST_1_VALUE' => '4.9★', 'ITSTORE_ST_1_LABEL' => $this->l('from 3,200+ verified reviews'),
            'ITSTORE_ST_2_VALUE' => '18k+', 'ITSTORE_ST_2_LABEL' => $this->l('businesses & gamers supplied'),
            'ITSTORE_ST_3_VALUE' => $this->l('Same-Day'), 'ITSTORE_ST_3_LABEL' => $this->l('dispatch before 1pm AEST'),
            'ITSTORE_ST_4_VALUE' => $this->l('3-Year'), 'ITSTORE_ST_4_LABEL' => $this->l('warranty on desktops & servers'),
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
            'itstore-stats',
            'modules/' . $this->name . '/views/css/stats.css',
            ['media' => 'all', 'priority' => 117]
        );
        $this->context->controller->registerJavascript(
            'itstore-stats',
            'modules/' . $this->name . '/views/js/stats.js',
            ['position' => 'bottom', 'priority' => 117]
        );
    }

    public function hookDisplayHome($params)
    {
        $stats = [];
        for ($i = 1; $i <= self::N; $i++) {
            $value = Configuration::get('ITSTORE_ST_' . $i . '_VALUE');
            if ($value === false || $value === '') {
                continue;
            }
            $stats[] = ['value' => $value, 'label' => Configuration::get('ITSTORE_ST_' . $i . '_LABEL')];
        }
        if (empty($stats)) {
            return '';
        }
        $this->smarty->assign('itstore_stats', $stats);

        return $this->display(__FILE__, 'views/templates/hook/stats.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreStats')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [];
        for ($i = 1; $i <= self::N; $i++) {
            $fields[] = ['type' => 'text', 'label' => sprintf($this->l('Stat %d — value'), $i), 'name' => 'ITSTORE_ST_' . $i . '_VALUE', 'class' => 'fixed-width-sm'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->l('Stat %d — label'), $i), 'name' => 'ITSTORE_ST_' . $i . '_LABEL'];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('Stats band'), 'icon' => 'icon-bar-chart'],
            'input' => $fields,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreStats'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreStats';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
