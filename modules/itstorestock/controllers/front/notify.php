<?php
/**
 * IT Store — back-in-stock alert endpoint.
 *
 * Records a customer's email against a product so they can be notified when it
 * is back in stock. Responds with JSON for the front-end AJAX form.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorestockNotifyModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $idAttr = (int) Tools::getValue('id_product_attribute');
        $email = trim((string) Tools::getValue('email'));
        $token = (string) Tools::getValue('token');

        if (!$idProduct || $token !== Tools::getToken('itstorestock' . $idProduct)) {
            $this->ajaxJson(false, $this->module->l('Invalid request.', 'notify'));
        }
        // Silent-drop spam bots: acknowledge without storing.
        if ($this->isLikelySpam()) {
            $this->ajaxJson(true, $this->module->l('Thanks! We’ll email you when it’s back in stock.', 'notify'));
        }
        if (!Validate::isEmail($email)) {
            $this->ajaxJson(false, $this->module->l('Please enter a valid email address.', 'notify'));
        }

        $idShop = (int) $this->context->shop->id;
        $exists = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_stock_alert`
             WHERE id_product = ' . $idProduct . ' AND id_product_attribute = ' . $idAttr . '
               AND id_shop = ' . $idShop . ' AND email = "' . pSQL($email) . '" AND notified = 0'
        );

        if (!$exists) {
            Db::getInstance()->insert('itstore_stock_alert', [
                'id_product' => $idProduct,
                'id_product_attribute' => $idAttr,
                'id_shop' => $idShop,
                'email' => pSQL($email),
                'notified' => 0,
                'date_add' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->ajaxJson(true, $this->module->l('Thanks! We’ll email you when it’s back in stock.', 'notify'));
    }

    /**
     * Dependency-free anti-spam: a non-empty honeypot field (hidden from humans)
     * or a submission that arrived faster than a person could realistically type.
     */
    protected function isLikelySpam()
    {
        if (trim((string) Tools::getValue('itstore_hp')) !== '') {
            return true;
        }
        $ts = (int) Tools::getValue('itstore_ts');

        return $ts > 0 && (time() - $ts) < 2;
    }

    protected function ajaxJson($success, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool) $success, 'message' => $message]);
        exit;
    }
}
