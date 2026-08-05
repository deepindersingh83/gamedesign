<?php
/**
 * IT Store — Request-a-Quote page.
 *
 * Renders a bulk/business quote form and stores submissions in `itstore_quote`,
 * notifying the shop email.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorefleetdealsQuoteModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Tools::isSubmit('submitQuote')) {
            return;
        }

        $name = trim((string) Tools::getValue('name'));
        $email = trim((string) Tools::getValue('email'));
        $company = trim((string) Tools::getValue('company'));
        $phone = trim((string) Tools::getValue('phone'));
        $details = trim((string) Tools::getValue('details'));

        if ($name === '' || !Validate::isEmail($email) || $details === ''
            || !Validate::isCleanHtml($details) || !Validate::isCleanHtml($name)) {
            $this->errors[] = $this->module->l('Please fill in your name, a valid email and some details.', 'quote');

            return;
        }

        Db::getInstance()->insert('itstore_quote', [
            'id_shop' => (int) $this->context->shop->id,
            'name' => pSQL($name),
            'company' => pSQL($company),
            'email' => pSQL($email),
            'phone' => pSQL($phone),
            'details' => pSQL($details, true),
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $to = Configuration::get('PS_SHOP_EMAIL');
        if (Validate::isEmail($to)) {
            Mail::Send(
                (int) $this->context->language->id,
                'contact',
                $this->module->l('New bulk quote request', 'quote'),
                [
                    '{email}' => $email,
                    '{message}' => "Name: $name\nCompany: $company\nPhone: $phone\n\n$details",
                ],
                $to
            );
        }

        $this->context->smarty->assign('quote_sent', true);
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign([
            'quote_action' => $this->context->link->getModuleLink('itstorefleetdeals', 'quote', [], true),
        ]);
        $this->setTemplate('module:itstorefleetdeals/views/templates/front/quote.tpl');
    }
}
