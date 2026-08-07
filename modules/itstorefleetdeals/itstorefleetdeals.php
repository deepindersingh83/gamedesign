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
        $this->version = '1.2.0';
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
            `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_cart` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `status` VARCHAR(32) NOT NULL DEFAULT "new",
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
        } elseif (Tools::isSubmit('convertQuote')) {
            $output .= $this->convertQuote((int) Tools::getValue('id_quote'));
        }

        return $output . $this->renderQuotes() . $this->renderForm();
    }

    /**
     * Turn a quote request into a draft cart owned by the requester and jump
     * the admin into PrestaShop's native "create order" screen, pre-scoped to
     * that customer + cart. Staff add the quoted lines and validate — producing
     * a real order — while the free-text request stays attached as a cart note.
     */
    protected function convertQuote($idQuote)
    {
        $quote = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_quote` WHERE id_quote = ' . (int) $idQuote
        );
        if (!$quote) {
            return $this->displayError($this->trans('Quote not found.', [], 'Modules.Itstorefleetdeals.Admin'));
        }
        if (!Validate::isEmail($quote['email'])) {
            return $this->displayError($this->trans('This quote has no valid email to attach an order to.', [], 'Modules.Itstorefleetdeals.Admin'));
        }

        $idShop = (int) $this->context->shop->id;

        // If already converted and the cart still exists, jump straight back to it.
        if ((int) $quote['id_cart'] > 0 && Validate::isLoadedObject(new Cart((int) $quote['id_cart']))) {
            Tools::redirectAdmin($this->orderCreateLink((int) $quote['id_customer'], (int) $quote['id_cart']));
        }

        try {
            $customer = $this->resolveCustomer($quote, $idShop);
            if (!Validate::isLoadedObject($customer)) {
                return $this->displayError($this->trans('Could not create or find a customer for this quote.', [], 'Modules.Itstorefleetdeals.Admin'));
            }

            $cart = new Cart();
            $cart->id_customer = (int) $customer->id;
            $cart->id_shop = $idShop;
            $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
            $cart->id_lang = (int) $this->context->language->id;
            $cart->id_currency = (int) $this->context->currency->id;
            $cart->id_carrier = 0;
            $cart->recyclable = 0;
            $cart->gift = 0;
            $cart->secure_key = $customer->secure_key ?: md5(uniqid((string) mt_rand(), true));
            if (!$cart->add()) {
                return $this->displayError($this->trans('Could not create a draft cart.', [], 'Modules.Itstorefleetdeals.Admin'));
            }

            // Keep the request text visible to staff during order creation.
            $this->attachCartMessage($cart->id, $quote);

            Db::getInstance()->update('itstore_quote', [
                'id_customer' => (int) $customer->id,
                'id_cart' => (int) $cart->id,
                'status' => 'converted',
            ], 'id_quote = ' . (int) $idQuote);

            Tools::redirectAdmin($this->orderCreateLink((int) $customer->id, (int) $cart->id));
        } catch (Exception $e) {
            return $this->displayError($this->trans('Conversion failed:', [], 'Modules.Itstorefleetdeals.Admin') . ' ' . htmlspecialchars($e->getMessage()));
        }

        return '';
    }

    /**
     * Find the customer for the quote email (in this shop) or create one.
     */
    protected function resolveCustomer(array $quote, $idShop)
    {
        $existingId = (int) Customer::customerExists($quote['email'], true, true);
        if ($existingId) {
            return new Customer($existingId);
        }

        $parts = preg_split('/\s+/', trim((string) $quote['name']), 2);
        $first = isset($parts[0]) && Validate::isName($parts[0]) ? $parts[0] : 'Business';
        $last = isset($parts[1]) && Validate::isName($parts[1]) ? $parts[1] : ($quote['company'] !== '' && Validate::isName($quote['company']) ? $quote['company'] : 'Customer');

        $customer = new Customer();
        $customer->firstname = $first;
        $customer->lastname = $last;
        $customer->email = $quote['email'];
        $customer->company = Validate::isGenericName($quote['company']) ? $quote['company'] : '';
        $customer->passwd = Tools::hash(Tools::passwdGen(16));
        $customer->id_shop = $idShop;
        $customer->id_shop_group = (int) $this->context->shop->id_shop_group;
        $customer->newsletter = 0;
        $customer->optin = 0;
        $customer->is_guest = 0;
        $customer->active = 1;
        $customer->add();

        return $customer;
    }

    /**
     * Store the quote's free-text request as a private cart message.
     */
    protected function attachCartMessage($idCart, array $quote)
    {
        $text = 'Fleet quote #' . (int) $quote['id_quote'] . "\n"
            . 'Company: ' . $quote['company'] . "\n"
            . 'Phone: ' . $quote['phone'] . "\n\n"
            . $quote['details'];

        try {
            Db::getInstance()->insert('message', [
                'id_cart' => (int) $idCart,
                'message' => pSQL(Tools::substr($text, 0, 1600), true),
                'private' => 1,
                'date_add' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            // Non-fatal: the order can still be created without the note.
        }
    }

    protected function orderCreateLink($idCustomer, $idCart)
    {
        return $this->context->link->getAdminLink('AdminOrders', true, [], [
            'addorder' => '',
            'id_cart' => (int) $idCart,
            'id_customer' => (int) $idCustomer,
        ]);
    }

    protected function renderQuotes()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_quote` ORDER BY date_add DESC'
        ) ?: [];

        $token = Tools::getAdminTokenLite('AdminModules');
        $base = $this->context->link->getAdminLink('AdminModules', false)
            . '&token=' . $token . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $body = '';
        foreach (array_slice($rows, 0, 100) as $r) {
            $converted = isset($r['status']) && $r['status'] === 'converted';
            $status = $converted
                ? '<span class="badge badge-success">' . $this->trans('Converted', [], 'Modules.Itstorefleetdeals.Admin') . '</span>'
                : '<span class="badge">' . $this->trans('New', [], 'Modules.Itstorefleetdeals.Admin') . '</span>';
            $action = $converted && (int) $r['id_cart']
                ? '<a class="btn btn-default btn-xs" href="' . htmlspecialchars($this->orderCreateLink((int) $r['id_customer'], (int) $r['id_cart'])) . '">' . $this->trans('Open order', [], 'Modules.Itstorefleetdeals.Admin') . '</a>'
                : '<a class="btn btn-primary btn-xs" href="' . $base . '&convertQuote&id_quote=' . (int) $r['id_quote'] . '">' . $this->trans('Convert to order', [], 'Modules.Itstorefleetdeals.Admin') . '</a>';
            $body .= '<tr><td>' . htmlspecialchars($r['date_add']) . '</td>'
                . '<td>' . htmlspecialchars($r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['company']) . '</td>'
                . '<td>' . htmlspecialchars($r['email']) . '</td>'
                . '<td>' . htmlspecialchars(Tools::substr((string) $r['details'], 0, 80)) . '</td>'
                . '<td>' . $status . '</td>'
                . '<td class="text-right">' . $action . '</td></tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="7">' . $this->trans('No quote requests yet.', [], 'Modules.Itstorefleetdeals.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-file-text"></i> '
            . $this->trans('Quote requests', [], 'Modules.Itstorefleetdeals.Admin') . '</div><table class="table"><thead><tr><th>'
            . $this->trans('Date', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Name', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Company', [], 'Modules.Itstorefleetdeals.Admin')
            . '</th><th>' . $this->trans('Email', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th>' . $this->trans('Details', [], 'Modules.Itstorefleetdeals.Admin')
            . '</th><th>' . $this->trans('Status', [], 'Modules.Itstorefleetdeals.Admin') . '</th><th class="text-right">' . $this->trans('Action', [], 'Modules.Itstorefleetdeals.Admin') . '</th></tr></thead><tbody>'
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
