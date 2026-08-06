<?php
/**
 * IT Store — Subscribe & Save (auto-reorder).
 *
 * The design's "Subscribe & Save — auto-reorder every N months" feature.
 * A logged-in customer can subscribe to a product; subscriptions are stored in
 * `itstore_subscription`, managed from a "My subscriptions" account page, and a
 * cron emails a reorder reminder when one is due.
 *
 * Scope note: this is a REMINDER-based auto-reorder (one-click reorder link),
 * not stored-card auto-charging — recurring billing requires a payment module
 * that supports card tokenisation, which is out of scope for a theme add-on.
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
        $this->version = '1.1.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Subscribe & Save');
        $this->description = $this->l('Subscribe to products for reminder-based auto-reorder.');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_AR_ENABLED' => 1,
            'ITSTORE_AR_DISCOUNT' => 10,
            'ITSTORE_AR_INTERVAL' => $this->l('every 3 months, cancel anytime'),
            'ITSTORE_AR_DAYS' => 90,
        ];
    }

    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_subscription` (
            `id_subscription` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer` INT(10) UNSIGNED NOT NULL,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_product_attribute` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `qty` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `interval_days` INT(10) UNSIGNED NOT NULL DEFAULT 90,
            `next_date` DATE NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_subscription`),
            KEY `id_customer` (`id_customer`, `active`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        if (!parent::install()
            || !Db::getInstance()->execute($sql)
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }
        Configuration::updateValue('ITSTORE_AR_CRON_TOKEN', Tools::passwdGen(24));

        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_subscription`;');
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }
        Configuration::deleteByName('ITSTORE_AR_CRON_TOKEN');

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
            $this->context->controller->registerJavascript(
                'itstore-autoreorder',
                'modules/' . $this->name . '/views/js/autoreorder.js',
                ['position' => 'bottom', 'priority' => 147]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int) Configuration::get('ITSTORE_AR_ENABLED')) {
            return '';
        }
        $idProduct = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            $idProduct = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
        }
        $customer = $this->context->customer;

        $this->smarty->assign([
            'ar_discount' => (int) Configuration::get('ITSTORE_AR_DISCOUNT'),
            'ar_interval' => Configuration::get('ITSTORE_AR_INTERVAL'),
            'ar_logged' => $customer && $customer->isLogged(),
            'ar_id_product' => $idProduct,
            'ar_login_url' => $this->context->link->getPageLink('authentication', true),
            'ar_subscribe_url' => $this->context->link->getModuleLink($this->name, 'subscribe', [], true),
            'ar_token' => Tools::getToken('itstoreautoreorder' . $idProduct),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/autoreorder.tpl');
    }

    public function hookDisplayCustomerAccount($params)
    {
        $this->smarty->assign('ar_manage_url', $this->context->link->getModuleLink($this->name, 'manage', [], true));

        return $this->display(__FILE__, 'views/templates/hook/account-link.tpl');
    }

    /** Create/refresh a subscription for the logged-in customer. */
    public function subscribe($idCustomer, $idProduct, $idAttr = 0)
    {
        $days = max(1, (int) Configuration::get('ITSTORE_AR_DAYS'));
        $exists = (int) Db::getInstance()->getValue(
            'SELECT id_subscription FROM `' . _DB_PREFIX_ . 'itstore_subscription`
             WHERE id_customer = ' . (int) $idCustomer . ' AND id_product = ' . (int) $idProduct . '
               AND id_product_attribute = ' . (int) $idAttr . ' AND id_shop = ' . (int) $this->context->shop->id
        );
        $next = date('Y-m-d', strtotime('+' . $days . ' days'));
        if ($exists) {
            Db::getInstance()->update('itstore_subscription', ['active' => 1, 'next_date' => $next], 'id_subscription = ' . $exists);

            return true;
        }

        return Db::getInstance()->insert('itstore_subscription', [
            'id_customer' => (int) $idCustomer,
            'id_product' => (int) $idProduct,
            'id_product_attribute' => (int) $idAttr,
            'id_shop' => (int) $this->context->shop->id,
            'qty' => 1,
            'interval_days' => $days,
            'next_date' => $next,
            'active' => 1,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Email reminders for due subscriptions and advance their next date. */
    public function sendDueReminders()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_subscription`
             WHERE active = 1 AND next_date <= "' . pSQL(date('Y-m-d')) . '"'
        ) ?: [];

        $sent = 0;
        foreach ($rows as $r) {
            $customer = new Customer((int) $r['id_customer']);
            $product = new Product((int) $r['id_product'], false, (int) Configuration::get('PS_LANG_DEFAULT'));
            if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($product)) {
                continue;
            }
            Mail::Send(
                (int) Configuration::get('PS_LANG_DEFAULT'),
                'reorder',
                $this->l('Time to reorder'),
                [
                    '{firstname}' => $customer->firstname,
                    '{product}' => is_array($product->name) ? reset($product->name) : $product->name,
                    '{url}' => $this->context->link->getProductLink((int) $r['id_product']),
                    '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                ],
                $customer->email,
                null, null, null, null, null, null,
                dirname(__FILE__) . '/mails/'
            );
            Db::getInstance()->update(
                'itstore_subscription',
                ['next_date' => date('Y-m-d', strtotime('+' . (int) $r['interval_days'] . ' days'))],
                'id_subscription = ' . (int) $r['id_subscription']
            );
            $sent++;
        }

        return $sent;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreAr')) {
            Configuration::updateValue('ITSTORE_AR_ENABLED', (int) Tools::getValue('ITSTORE_AR_ENABLED'));
            Configuration::updateValue('ITSTORE_AR_DISCOUNT', (int) Tools::getValue('ITSTORE_AR_DISCOUNT'));
            Configuration::updateValue('ITSTORE_AR_INTERVAL', Tools::getValue('ITSTORE_AR_INTERVAL'));
            Configuration::updateValue('ITSTORE_AR_DAYS', (int) Tools::getValue('ITSTORE_AR_DAYS'));
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        $cron = $this->context->link->getModuleLink($this->name, 'cron', ['token' => Configuration::get('ITSTORE_AR_CRON_TOKEN')], true);
        $output .= '<div class="panel"><div class="panel-heading"><i class="icon-time"></i> ' . $this->l('Reorder-reminder cron') . '</div>'
            . '<p>' . $this->l('Call daily from your server cron:') . '</p><pre style="white-space:normal;word-break:break-all">'
            . htmlspecialchars($cron) . '</pre></div>';

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
                ['type' => 'text', 'label' => $this->l('Interval (days)'), 'name' => 'ITSTORE_AR_DAYS', 'class' => 'fixed-width-sm'],
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
            'ITSTORE_AR_DAYS' => (int) Configuration::get('ITSTORE_AR_DAYS'),
            'ITSTORE_AR_INTERVAL' => Configuration::get('ITSTORE_AR_INTERVAL'),
        ];

        return $helper->generateForm([$form]);
    }
}
