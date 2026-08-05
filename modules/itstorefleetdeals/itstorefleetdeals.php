<?php
/**
 * IT Store — Business Fleet Deals band.
 *
 * The design's two-tile promo: a dark "Business Fleet Deals" card with a
 * Request-Quote CTA, and an amber "Clearance" card. Quote requests are captured
 * by the `quote` front controller into `itstore_quote` and listed in the BO.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorefleetdeals extends Module
{
    public function __construct()
    {
        $this->name = 'itstorefleetdeals';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Fleet Deals');
        $this->description = $this->l('Business Fleet Deals + Clearance promo band with a Request-Quote form.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_FD_EYEBROW' => $this->l('Business Fleet Deals'),
            'ITSTORE_FD_TITLE' => $this->l('Buy 10+ desktops, save up to 15%'),
            'ITSTORE_FD_SUB' => $this->l('Instant bulk pricing tiers on desktops, laptops & monitors.'),
            'ITSTORE_FD_CL_EYEBROW' => $this->l('Clearance'),
            'ITSTORE_FD_CL_TITLE' => $this->l('Up to 30% off networking gear'),
            'ITSTORE_FD_CL_LINK' => '',
        ];
    }

    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_quote` (
            `id_quote` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `name` VARCHAR(255) NOT NULL,
            `company` VARCHAR(255) NOT NULL DEFAULT "",
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(64) NOT NULL DEFAULT "",
            `details` TEXT,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_quote`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        if (!parent::install()
            || !Db::getInstance()->execute($sql)
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
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_quote`;');
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-fleetdeals',
            'modules/' . $this->name . '/views/css/fleetdeals.css',
            ['media' => 'all', 'priority' => 116]
        );
    }

    public function hookDisplayHome($params)
    {
        $clLink = Configuration::get('ITSTORE_FD_CL_LINK');
        $this->smarty->assign([
            'fd_eyebrow' => Configuration::get('ITSTORE_FD_EYEBROW'),
            'fd_title' => Configuration::get('ITSTORE_FD_TITLE'),
            'fd_sub' => Configuration::get('ITSTORE_FD_SUB'),
            'fd_quote_url' => $this->context->link->getModuleLink($this->name, 'quote', [], true),
            'fd_cl_eyebrow' => Configuration::get('ITSTORE_FD_CL_EYEBROW'),
            'fd_cl_title' => Configuration::get('ITSTORE_FD_CL_TITLE'),
            'fd_cl_link' => $clLink ?: $this->context->link->getPageLink('prices-drop', true),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/fleetdeals.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreFd')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderQuotes() . $this->renderForm();
    }

    protected function renderQuotes()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_quote` ORDER BY date_add DESC'
        ) ?: [];
        $body = '';
        foreach (array_slice($rows, 0, 100) as $r) {
            $body .= '<tr><td>' . htmlspecialchars($r['date_add']) . '</td>'
                . '<td>' . htmlspecialchars($r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['company']) . '</td>'
                . '<td>' . htmlspecialchars($r['email']) . '</td>'
                . '<td>' . htmlspecialchars(Tools::substr((string) $r['details'], 0, 80)) . '</td></tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="5">' . $this->l('No quote requests yet.') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-file-text"></i> '
            . $this->l('Quote requests') . '</div><table class="table"><thead><tr><th>'
            . $this->l('Date') . '</th><th>' . $this->l('Name') . '</th><th>' . $this->l('Company')
            . '</th><th>' . $this->l('Email') . '</th><th>' . $this->l('Details') . '</th></tr></thead><tbody>'
            . $body . '</tbody></table></div>';
    }

    protected function renderForm()
    {
        $fields = [];
        foreach ([
            'ITSTORE_FD_EYEBROW' => $this->l('Fleet — eyebrow'),
            'ITSTORE_FD_TITLE' => $this->l('Fleet — title'),
            'ITSTORE_FD_SUB' => $this->l('Fleet — subtitle'),
            'ITSTORE_FD_CL_EYEBROW' => $this->l('Clearance — eyebrow'),
            'ITSTORE_FD_CL_TITLE' => $this->l('Clearance — title'),
            'ITSTORE_FD_CL_LINK' => $this->l('Clearance — link'),
        ] as $name => $label) {
            $fields[] = ['type' => 'text', 'label' => $label, 'name' => $name];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->l('Fleet deals band'), 'icon' => 'icon-briefcase'],
            'input' => $fields,
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreFd'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreFd';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
