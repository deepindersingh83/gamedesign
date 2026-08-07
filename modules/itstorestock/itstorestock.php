<?php
/**
 * IT Store — Stock indicator & back-in-stock alerts.
 *
 * Shows a live stock indicator on the product page ("In stock", "Only N left",
 * "Out of stock") and, when unavailable, a "notify me" form that records an
 * email against the product. Alerts are stored in `itstore_stock_alert` and
 * listed in the module's back office (sending can be wired to a cron/hook).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorestock extends Module
{
    public function __construct()
    {
        $this->name = 'itstorestock';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Stock & Alerts', [], 'Modules.Itstorestock.Admin');
        $this->description = $this->trans('Stock indicator plus back-in-stock email alerts on the product page.', [], 'Modules.Itstorestock.Admin');
    }

    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_stock_alert` (
            `id_alert` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_product_attribute` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `email` VARCHAR(255) NOT NULL,
            `notified` TINYINT(1) NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_alert`),
            KEY `id_product` (`id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        Configuration::updateValue('ITSTORE_STK_LOW', 5);
        Configuration::updateValue('ITSTORE_STK_CRON_TOKEN', Tools::passwdGen(24));

        return parent::install()
            && Db::getInstance()->execute($sql)
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('actionDeleteGDPRData');
    }

    public function uninstall()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_stock_alert`;');
        Configuration::deleteByName('ITSTORE_STK_LOW');
        Configuration::deleteByName('ITSTORE_STK_CRON_TOKEN');

        return parent::uninstall();
    }

    /**
     * GDPR (psgdpr): export the back-in-stock alerts for this email address.
     */
    public function hookActionExportGDPRData($params)
    {
        $email = $this->gdprCustomerEmail($params);
        if (!Validate::isEmail($email)) {
            return '';
        }
        $rows = Db::getInstance()->executeS(
            'SELECT id_alert, id_product, id_product_attribute, email, notified, date_add
             FROM `' . _DB_PREFIX_ . 'itstore_stock_alert` WHERE email = "' . pSQL($email) . '"'
        ) ?: [];

        return json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * GDPR (psgdpr): erase the back-in-stock alerts for this email address.
     */
    public function hookActionDeleteGDPRData($params)
    {
        $email = $this->gdprCustomerEmail($params);
        if (!Validate::isEmail($email)) {
            return '';
        }

        return json_encode((bool) Db::getInstance()->delete('itstore_stock_alert', 'email = "' . pSQL($email) . '"'));
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
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-stock',
                'modules/' . $this->name . '/views/css/stock.css',
                ['media' => 'all', 'priority' => 143]
            );
            $this->context->controller->registerJavascript(
                'itstore-stock',
                'modules/' . $this->name . '/views/js/stock.js',
                ['position' => 'bottom', 'priority' => 143]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        $idProduct = 0;
        $idAttr = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            if (is_array($p)) {
                $idProduct = isset($p['id_product']) ? (int) $p['id_product'] : 0;
                $idAttr = isset($p['id_product_attribute']) ? (int) $p['id_product_attribute'] : 0;
            } elseif (is_object($p)) {
                $idProduct = (int) $p->id;
            }
        }
        if ($idProduct <= 0) {
            return '';
        }

        $qty = (int) StockAvailable::getQuantityAvailableByProduct($idProduct, $idAttr ?: null);
        $low = (int) Configuration::get('ITSTORE_STK_LOW');

        $state = 'in';
        if ($qty <= 0) {
            $state = 'out';
        } elseif ($qty <= $low) {
            $state = 'low';
        }

        $this->smarty->assign([
            'itstore_stk_state' => $state,
            'itstore_stk_qty' => $qty,
            'itstore_stk_id_product' => $idProduct,
            'itstore_stk_id_attr' => $idAttr,
            'itstore_stk_notify_url' => $this->context->link->getModuleLink($this->name, 'notify', [], true),
            'itstore_stk_token' => Tools::getToken('itstorestock' . $idProduct),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/stock.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreStock')) {
            Configuration::updateValue('ITSTORE_STK_LOW', (int) Tools::getValue('ITSTORE_STK_LOW'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorestock.Admin'));
        }

        return $output . $this->renderCronInfo() . $this->renderAlerts() . $this->renderForm();
    }

    protected function renderCronInfo()
    {
        $url = $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['token' => Configuration::get('ITSTORE_STK_CRON_TOKEN')],
            true
        );

        return '<div class="panel"><div class="panel-heading"><i class="icon-time"></i> '
            . $this->trans('Back-in-stock cron', [], 'Modules.Itstorestock.Admin') . '</div>'
            . '<p>' . $this->trans('Call this URL from your server cron (e.g. hourly) to email waiting customers when their product is back in stock:', [], 'Modules.Itstorestock.Admin') . '</p>'
            . '<pre style="white-space:normal;word-break:break-all">' . htmlspecialchars($url) . '</pre></div>';
    }

    /**
     * Notify waiting customers for products that are back in stock.
     * Returns the number of emails sent. Safe to call repeatedly.
     */
    public function sendBackInStockAlerts()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_stock_alert` WHERE notified = 0'
        ) ?: [];

        $sent = 0;
        foreach ($rows as $r) {
            $idProduct = (int) $r['id_product'];
            $idAttr = (int) $r['id_product_attribute'];
            $qty = (int) StockAvailable::getQuantityAvailableByProduct($idProduct, $idAttr ?: null);
            if ($qty <= 0) {
                continue;
            }

            $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
            $product = new Product($idProduct, false, $idLang);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            Mail::Send(
                $idLang,
                'backinstock',
                $this->trans('Back in stock', [], 'Modules.Itstorestock.Admin'),
                [
                    '{product}' => is_array($product->name) ? reset($product->name) : $product->name,
                    '{url}' => $this->context->link->getProductLink($idProduct),
                    '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                ],
                $r['email'],
                null,
                null,
                null,
                null,
                null,
                dirname(__FILE__) . '/mails/'
            );

            Db::getInstance()->update('itstore_stock_alert', ['notified' => 1], 'id_alert = ' . (int) $r['id_alert']);
            $sent++;
        }

        return $sent;
    }

    protected function renderAlerts()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT a.*, pl.name FROM `' . _DB_PREFIX_ . 'itstore_stock_alert` a
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
               ON (pl.id_product = a.id_product AND pl.id_lang = ' . (int) $this->context->language->id . '
                   AND pl.id_shop = a.id_shop)
             ORDER BY a.date_add DESC'
        ) ?: [];

        $body = '';
        foreach (array_slice($rows, 0, 100) as $r) {
            $body .= '<tr><td>' . htmlspecialchars($r['email']) . '</td>'
                . '<td>' . htmlspecialchars((string) $r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['date_add']) . '</td>'
                . '<td>' . ((int) $r['notified'] ? $this->trans('Notified', [], 'Modules.Itstorestock.Admin') : $this->trans('Waiting', [], 'Modules.Itstorestock.Admin')) . '</td></tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="4">' . $this->trans('No alerts yet.', [], 'Modules.Itstorestock.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-bell"></i> '
            . $this->trans('Back-in-stock alerts', [], 'Modules.Itstorestock.Admin') . '</div>'
            . '<table class="table"><thead><tr><th>' . $this->trans('Email', [], 'Modules.Itstorestock.Admin') . '</th><th>' . $this->trans('Product', [], 'Modules.Itstorestock.Admin')
            . '</th><th>' . $this->trans('Requested', [], 'Modules.Itstorestock.Admin') . '</th><th>' . $this->trans('Status', [], 'Modules.Itstorestock.Admin') . '</th></tr></thead><tbody>'
            . $body . '</tbody></table></div>';
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Stock settings', [], 'Modules.Itstorestock.Admin'), 'icon' => 'icon-cubes'],
            'input' => [
                ['type' => 'text', 'label' => $this->trans('Low-stock threshold', [], 'Modules.Itstorestock.Admin'), 'name' => 'ITSTORE_STK_LOW', 'class' => 'fixed-width-sm', 'desc' => $this->trans('Show “Only N left” at or below this quantity.', [], 'Modules.Itstorestock.Admin')],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorestock.Admin'), 'name' => 'submitItstoreStock'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreStock';
        $helper->fields_value = ['ITSTORE_STK_LOW' => (int) Configuration::get('ITSTORE_STK_LOW')];

        return $helper->generateForm([$form]);
    }
}
