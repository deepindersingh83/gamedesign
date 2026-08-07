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
        $this->version = '1.1.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Fleet Deals', [], 'Modules.Itstorefleetdeals.Admin');
        $this->description = $this->trans('Business Fleet Deals + Clearance promo band with a Request-Quote form.', [], 'Modules.Itstorefleetdeals.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_FD_EYEBROW' => $this->trans('Business Fleet Deals', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_TITLE' => $this->trans('Buy 10+ desktops, save up to 15%', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_SUB' => $this->trans('Instant bulk pricing tiers on desktops, laptops & monitors.', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_CL_EYEBROW' => $this->trans('Clearance', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_CL_TITLE' => $this->trans('Up to 30% off networking gear', [], 'Modules.Itstorefleetdeals.Admin'),
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
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('actionExportGDPRData')
            || !$this->registerHook('actionDeleteGDPRData')) {
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

    /**
     * GDPR (psgdpr): export the quote requests this email address submitted.
     */
    public function hookActionExportGDPRData($params)
    {
        $email = $this->gdprCustomerEmail($params);
        if (!Validate::isEmail($email)) {
            return '';
        }
        $rows = Db::getInstance()->executeS(
            'SELECT id_quote, name, company, email, phone, details, date_add
             FROM `' . _DB_PREFIX_ . 'itstore_quote` WHERE email = "' . pSQL($email) . '"'
        ) ?: [];

        return json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * GDPR (psgdpr): erase the quote requests this email address submitted.
     */
    public function hookActionDeleteGDPRData($params)
    {
        $email = $this->gdprCustomerEmail($params);
        if (!Validate::isEmail($email)) {
            return '';
        }

        return json_encode((bool) Db::getInstance()->delete('itstore_quote', 'email = "' . pSQL($email) . '"'));
    }

    /**
     * Resolve the customer email from the psgdpr hook payload (version-tolerant).
     */
    protected function gdprCustomerEmail($params)
    {
        if (isset($params['email'])) {
            return (string) $params['email'];
        }
        if (isset($params['customer']['email'])) {
            return (string) $params['customer']['email'];
        }
        if (isset($params['customer']) && $params['customer'] instanceof Customer) {
            return (string) $params['customer']->email;
        }

        return '';
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
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorefleetdeals.Admin'));
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
            $body = '<tr><td colspan="5">' . $this->trans('No quote requests yet.', [], 'Modules.Itstorefleetdeals.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-file-text"></i> '
            . $this->trans('Quote requests', [], 'Modules.Itstorefleetdeals.Admin') . '</div><table class="table"><thead><tr><th>'
            . $this->trans('Date', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Name', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Company', [], 'Modules.Itstorefleetdeals.Admin')
            . '</th><th>' . $this->trans('Email', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Details', [], 'Modules.Itstorefleetdeals.Admin') . '</th></tr></thead><tbody>'
            . $body . '</tbody></table></div>';
    }

    protected function renderForm()
    {
        $fields = [];
        foreach ([
            'ITSTORE_FD_EYEBROW' => $this->trans('Fleet — eyebrow', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_TITLE' => $this->trans('Fleet — title', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_SUB' => $this->trans('Fleet — subtitle', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_CL_EYEBROW' => $this->trans('Clearance — eyebrow', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_CL_TITLE' => $this->trans('Clearance — title', [], 'Modules.Itstorefleetdeals.Admin'),
            'ITSTORE_FD_CL_LINK' => $this->trans('Clearance — link', [], 'Modules.Itstorefleetdeals.Admin'),
        ] as $name => $label) {
            $fields[] = ['type' => 'text', 'label' => $label, 'name' => $name];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Fleet deals band', [], 'Modules.Itstorefleetdeals.Admin'), 'icon' => 'icon-briefcase'],
            'input' => $fields,
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorefleetdeals.Admin'), 'name' => 'submitItstoreFd'],
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
