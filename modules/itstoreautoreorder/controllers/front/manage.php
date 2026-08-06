<?php
/**
 * IT Store — My subscriptions (customer account page).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreautoreorderManageModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $customer = $this->context->customer;
        if (!$customer || !$customer->isLogged()) {
            Tools::redirect($this->context->link->getPageLink('authentication', true));
        }

        if (Tools::isSubmit('cancel')) {
            $id = (int) Tools::getValue('id_subscription');
            if ($id && Tools::getValue('token') === Tools::getToken('itstoreautoreordercancel' . $id)) {
                Db::getInstance()->update(
                    'itstore_subscription',
                    ['active' => 0],
                    'id_subscription = ' . $id . ' AND id_customer = ' . (int) $customer->id
                );
            }
        }

        $idLang = (int) $this->context->language->id;
        $rows = Db::getInstance()->executeS(
            'SELECT s.*, pl.name FROM `' . _DB_PREFIX_ . 'itstore_subscription` s
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
               ON (pl.id_product = s.id_product AND pl.id_lang = ' . $idLang . ' AND pl.id_shop = s.id_shop)
             WHERE s.id_customer = ' . (int) $customer->id . ' AND s.active = 1
             ORDER BY s.next_date ASC'
        ) ?: [];

        $subs = [];
        foreach ($rows as $r) {
            $subs[] = [
                'name' => $r['name'],
                'url' => $this->context->link->getProductLink((int) $r['id_product']),
                'next' => Tools::displayDate($r['next_date']),
                'interval' => (int) $r['interval_days'],
                'cancel_url' => $this->context->link->getModuleLink('itstoreautoreorder', 'manage', [
                    'cancel' => 1,
                    'id_subscription' => (int) $r['id_subscription'],
                    'token' => Tools::getToken('itstoreautoreordercancel' . (int) $r['id_subscription']),
                ], true),
            ];
        }

        $this->context->smarty->assign('ar_subs', $subs);
        $this->setTemplate('module:itstoreautoreorder/views/templates/front/manage.tpl');
    }
}
