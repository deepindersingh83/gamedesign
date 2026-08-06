<?php
/**
 * IT Store — Support widget module.
 *
 * Adds a floating support / help widget to the storefront with quick access
 * to live chat, phone, email, a searchable FAQ and a contact shortcut.
 * Hydrated by the theme's `support.js` component.
 *
 * @author    Syber Info <admin@syberinfo.com.au>
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoresupport extends Module
{
    public function __construct()
    {
        $this->name = 'itstoresupport';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Support Widget', [], 'Modules.Itstoresupport.Admin');
        $this->description = $this->trans('Floating support / help widget with chat, phone, email and FAQ.', [], 'Modules.Itstoresupport.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall the Support Widget?', [], 'Modules.Itstoresupport.Admin');
    }

    /** Configuration keys with their default values. */
    protected function defaults()
    {
        return [
            'ITSTORE_SUP_PHONE' => '+61 2 0000 0000',
            'ITSTORE_SUP_EMAIL' => 'support@syberinfo.com.au',
            'ITSTORE_SUP_HOURS' => $this->trans('Mon–Fri, 9am–6pm', [], 'Modules.Itstoresupport.Admin'),
            'ITSTORE_SUP_CHAT_URL' => '',
            'ITSTORE_SUP_ENABLED' => 1,
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayFooterAfter')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }

        foreach ($this->defaults() as $key => $value) {
            Configuration::updateValue($key, $value);
        }

        return true;
    }

    public function uninstall()
    {
        foreach (array_keys($this->defaults()) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (!(int) Configuration::get('ITSTORE_SUP_ENABLED')) {
            return;
        }

        $this->context->controller->registerStylesheet(
            'itstore-support',
            'modules/' . $this->name . '/views/css/support.css',
            ['media' => 'all', 'priority' => 130]
        );
        $this->context->controller->registerJavascript(
            'itstore-support',
            'modules/' . $this->name . '/views/js/support.js',
            ['position' => 'bottom', 'priority' => 130]
        );
    }

    public function hookDisplayFooterAfter($params)
    {
        if (!(int) Configuration::get('ITSTORE_SUP_ENABLED')) {
            return '';
        }

        $this->smarty->assign([
            'sup_phone' => Configuration::get('ITSTORE_SUP_PHONE'),
            'sup_email' => Configuration::get('ITSTORE_SUP_EMAIL'),
            'sup_hours' => Configuration::get('ITSTORE_SUP_HOURS'),
            'sup_chat_url' => Configuration::get('ITSTORE_SUP_CHAT_URL'),
            'sup_contact_url' => $this->context->link->getPageLink('contact', true),
            'sup_faqs' => $this->getFaqs(),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/support.tpl');
    }

    /** Default FAQ entries surfaced in the widget. */
    protected function getFaqs()
    {
        return [
            [
                'q' => $this->trans('How long does delivery take?', [], 'Modules.Itstoresupport.Admin'),
                'a' => $this->trans('In-stock items ship same day with next-business-day delivery in metro areas.', [], 'Modules.Itstoresupport.Admin'),
            ],
            [
                'q' => $this->trans('Do you offer a warranty?', [], 'Modules.Itstoresupport.Admin'),
                'a' => $this->trans('Every product includes the manufacturer warranty, plus optional extended cover at checkout.', [], 'Modules.Itstoresupport.Admin'),
            ],
            [
                'q' => $this->trans('Can you build a custom PC for me?', [], 'Modules.Itstoresupport.Admin'),
                'a' => $this->trans('Yes — pick your parts and add the custom build service, or contact us for a tailored quote.', [], 'Modules.Itstoresupport.Admin'),
            ],
            [
                'q' => $this->trans('What is your returns policy?', [], 'Modules.Itstoresupport.Admin'),
                'a' => $this->trans('Unopened items can be returned within 30 days. Faulty items are covered under warranty.', [], 'Modules.Itstoresupport.Admin'),
            ],
        ];
    }

    /**
     * Back-office configuration form.
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitItstoreSupport')) {
            $email = trim(Tools::getValue('ITSTORE_SUP_EMAIL'));
            $chat = trim(Tools::getValue('ITSTORE_SUP_CHAT_URL'));

            if ($email !== '' && !Validate::isEmail($email)) {
                $output .= $this->displayError($this->trans('The support email is not valid.', [], 'Modules.Itstoresupport.Admin'));
            } elseif ($chat !== '' && !Validate::isUrlOrEmpty($chat)) {
                $output .= $this->displayError($this->trans('The live chat URL is not valid.', [], 'Modules.Itstoresupport.Admin'));
            } else {
                Configuration::updateValue('ITSTORE_SUP_PHONE', Tools::getValue('ITSTORE_SUP_PHONE'));
                Configuration::updateValue('ITSTORE_SUP_EMAIL', $email);
                Configuration::updateValue('ITSTORE_SUP_HOURS', Tools::getValue('ITSTORE_SUP_HOURS'));
                Configuration::updateValue('ITSTORE_SUP_CHAT_URL', $chat);
                Configuration::updateValue('ITSTORE_SUP_ENABLED', (int) Tools::getValue('ITSTORE_SUP_ENABLED'));
                $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstoresupport.Admin'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $form = [
            'form' => [
                'legend' => ['title' => $this->trans('Support widget settings', [], 'Modules.Itstoresupport.Admin'), 'icon' => 'icon-life-ring'],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Enable widget', [], 'Modules.Itstoresupport.Admin'),
                        'name' => 'ITSTORE_SUP_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'en_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstoresupport.Admin')],
                            ['id' => 'en_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstoresupport.Admin')],
                        ],
                    ],
                    ['type' => 'text', 'label' => $this->trans('Phone number', [], 'Modules.Itstoresupport.Admin'), 'name' => 'ITSTORE_SUP_PHONE'],
                    ['type' => 'text', 'label' => $this->trans('Support email', [], 'Modules.Itstoresupport.Admin'), 'name' => 'ITSTORE_SUP_EMAIL'],
                    ['type' => 'text', 'label' => $this->trans('Opening hours', [], 'Modules.Itstoresupport.Admin'), 'name' => 'ITSTORE_SUP_HOURS'],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Live chat URL', [], 'Modules.Itstoresupport.Admin'),
                        'name' => 'ITSTORE_SUP_CHAT_URL',
                        'desc' => $this->trans('Optional. Link to your live chat / help desk. Leave empty to hide the chat button.', [], 'Modules.Itstoresupport.Admin'),
                    ],
                ],
                'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstoresupport.Admin'), 'name' => 'submitItstoreSupport'],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreSupport';
        $helper->fields_value = [
            'ITSTORE_SUP_ENABLED' => (int) Configuration::get('ITSTORE_SUP_ENABLED'),
            'ITSTORE_SUP_PHONE' => Configuration::get('ITSTORE_SUP_PHONE'),
            'ITSTORE_SUP_EMAIL' => Configuration::get('ITSTORE_SUP_EMAIL'),
            'ITSTORE_SUP_HOURS' => Configuration::get('ITSTORE_SUP_HOURS'),
            'ITSTORE_SUP_CHAT_URL' => Configuration::get('ITSTORE_SUP_CHAT_URL'),
        ];

        return $helper->generateForm([$form]);
    }
}
