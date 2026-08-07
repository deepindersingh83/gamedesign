<?php
/**
 * IT Store — Security headers.
 *
 * Emits a conservative, configurable set of HTTP security headers on the front
 * office (X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
 * Permissions-Policy, optional HSTS and an optional Content-Security-Policy).
 *
 * The CSP defaults to OFF and, when enabled, to Report-Only, because a strict
 * policy easily breaks a PrestaShop storefront; shop owners can tighten it from
 * the module's back office once they have vetted their inline scripts.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoresecurity extends Module
{
    public function __construct()
    {
        $this->name = 'itstoresecurity';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Security Headers', [], 'Modules.Itstoresecurity.Admin');
        $this->description = $this->trans('Adds hardening HTTP headers (nosniff, frame options, referrer & permissions policy, optional HSTS and CSP) to the storefront.', [], 'Modules.Itstoresecurity.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_SEC_NOSNIFF' => 1,
            'ITSTORE_SEC_FRAME' => 'SAMEORIGIN',
            'ITSTORE_SEC_REFERRER' => 'strict-origin-when-cross-origin',
            'ITSTORE_SEC_PERMISSIONS' => 'camera=(), microphone=(), geolocation=(self), payment=(self)',
            'ITSTORE_SEC_HSTS' => 0,
            'ITSTORE_SEC_HSTS_MAXAGE' => 15552000,
            'ITSTORE_SEC_CSP' => 0,
            'ITSTORE_SEC_CSP_REPORT_ONLY' => 1,
            'ITSTORE_SEC_CSP_VALUE' => "default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; font-src 'self' data:; frame-ancestors 'self'; base-uri 'self'; object-src 'none'",
        ];
    }

    public function install()
    {
        if (!parent::install()
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

    /**
     * Send the headers on every front-office controller. Runs during setMedia,
     * before the page body is flushed, so header() is still effective.
     */
    public function hookActionFrontControllerSetMedia()
    {
        if (headers_sent()) {
            return;
        }

        if ((int) Configuration::get('ITSTORE_SEC_NOSNIFF')) {
            header('X-Content-Type-Options: nosniff');
        }

        $frame = (string) Configuration::get('ITSTORE_SEC_FRAME');
        if ($frame !== '' && $frame !== 'OFF') {
            header('X-Frame-Options: ' . $frame);
        }

        $referrer = (string) Configuration::get('ITSTORE_SEC_REFERRER');
        if ($referrer !== '') {
            header('Referrer-Policy: ' . $referrer);
        }

        $permissions = (string) Configuration::get('ITSTORE_SEC_PERMISSIONS');
        if ($permissions !== '') {
            header('Permissions-Policy: ' . $permissions);
        }

        if ((int) Configuration::get('ITSTORE_SEC_HSTS') && Tools::usingSecureMode()) {
            $maxAge = (int) Configuration::get('ITSTORE_SEC_HSTS_MAXAGE') ?: 15552000;
            header('Strict-Transport-Security: max-age=' . $maxAge . '; includeSubDomains');
        }

        if ((int) Configuration::get('ITSTORE_SEC_CSP')) {
            $csp = trim((string) Configuration::get('ITSTORE_SEC_CSP_VALUE'));
            if ($csp !== '') {
                $headerName = (int) Configuration::get('ITSTORE_SEC_CSP_REPORT_ONLY')
                    ? 'Content-Security-Policy-Report-Only'
                    : 'Content-Security-Policy';
                header($headerName . ': ' . $csp);
            }
        }
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitItstoreSec')) {
            Configuration::updateValue('ITSTORE_SEC_NOSNIFF', (int) Tools::getValue('ITSTORE_SEC_NOSNIFF'));
            Configuration::updateValue('ITSTORE_SEC_FRAME', pSQL(Tools::getValue('ITSTORE_SEC_FRAME')));
            Configuration::updateValue('ITSTORE_SEC_REFERRER', pSQL(Tools::getValue('ITSTORE_SEC_REFERRER')));
            Configuration::updateValue('ITSTORE_SEC_PERMISSIONS', pSQL(Tools::getValue('ITSTORE_SEC_PERMISSIONS')));
            Configuration::updateValue('ITSTORE_SEC_HSTS', (int) Tools::getValue('ITSTORE_SEC_HSTS'));
            Configuration::updateValue('ITSTORE_SEC_HSTS_MAXAGE', (int) Tools::getValue('ITSTORE_SEC_HSTS_MAXAGE'));
            Configuration::updateValue('ITSTORE_SEC_CSP', (int) Tools::getValue('ITSTORE_SEC_CSP'));
            Configuration::updateValue('ITSTORE_SEC_CSP_REPORT_ONLY', (int) Tools::getValue('ITSTORE_SEC_CSP_REPORT_ONLY'));
            Configuration::updateValue('ITSTORE_SEC_CSP_VALUE', Tools::getValue('ITSTORE_SEC_CSP_VALUE'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoresecurity.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $yesNo = function ($name, $label, $desc) {
            return [
                'type' => 'switch', 'label' => $label, 'name' => $name, 'is_bool' => true, 'desc' => $desc,
                'values' => [
                    ['id' => $name . '_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoresecurity.Admin')],
                    ['id' => $name . '_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoresecurity.Admin')],
                ],
            ];
        };

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Security headers', [], 'Modules.Itstoresecurity.Admin'), 'icon' => 'icon-lock'],
            'input' => [
                $yesNo('ITSTORE_SEC_NOSNIFF', $this->trans('X-Content-Type-Options: nosniff', [], 'Modules.Itstoresecurity.Admin'), $this->trans('Stop browsers from MIME-sniffing responses.', [], 'Modules.Itstoresecurity.Admin')),
                [
                    'type' => 'select', 'label' => $this->trans('X-Frame-Options', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'ITSTORE_SEC_FRAME',
                    'desc' => $this->trans('Clickjacking protection.', [], 'Modules.Itstoresecurity.Admin'),
                    'options' => ['query' => [
                        ['id' => 'SAMEORIGIN', 'name' => 'SAMEORIGIN'],
                        ['id' => 'DENY', 'name' => 'DENY'],
                        ['id' => 'OFF', 'name' => $this->trans('Off', [], 'Modules.Itstoresecurity.Admin')],
                    ], 'id' => 'id', 'name' => 'name'],
                ],
                ['type' => 'text', 'label' => $this->trans('Referrer-Policy', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'ITSTORE_SEC_REFERRER'],
                ['type' => 'text', 'label' => $this->trans('Permissions-Policy', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'ITSTORE_SEC_PERMISSIONS'],
                $yesNo('ITSTORE_SEC_HSTS', $this->trans('Strict-Transport-Security (HSTS)', [], 'Modules.Itstoresecurity.Admin'), $this->trans('Only sent over HTTPS. Enable once you are certain the whole shop is HTTPS-only.', [], 'Modules.Itstoresecurity.Admin')),
                ['type' => 'text', 'label' => $this->trans('HSTS max-age (seconds)', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'ITSTORE_SEC_HSTS_MAXAGE'],
                $yesNo('ITSTORE_SEC_CSP', $this->trans('Content-Security-Policy', [], 'Modules.Itstoresecurity.Admin'), $this->trans('A strict CSP can break the storefront — test with Report-Only first.', [], 'Modules.Itstoresecurity.Admin')),
                $yesNo('ITSTORE_SEC_CSP_REPORT_ONLY', $this->trans('CSP in Report-Only mode', [], 'Modules.Itstoresecurity.Admin'), $this->trans('Report violations without enforcing them.', [], 'Modules.Itstoresecurity.Admin')),
                ['type' => 'textarea', 'label' => $this->trans('CSP value', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'ITSTORE_SEC_CSP_VALUE', 'rows' => 4],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoresecurity.Admin'), 'name' => 'submitItstoreSec'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreSec';

        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
