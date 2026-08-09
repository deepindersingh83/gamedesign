<?php
/**
 * IT Store — create a product subscription (AJAX).
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreautoreorderSubscribeModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $customer = $this->context->customer;
        if (!$customer || !$customer->isLogged()) {
            $this->json(false, $this->module->l('Please sign in to subscribe.', 'subscribe'));
        }

        $idProduct = (int) Tools::getValue('id_product');
        $idAttr = (int) Tools::getValue('id_product_attribute');
        $token = (string) Tools::getValue('token');
        if (!$idProduct || $token !== Tools::getToken('itstoreautoreorder' . $idProduct)) {
            $this->json(false, $this->module->l('Invalid request.', 'subscribe'));
        }

        $ok = $this->module->subscribe((int) $customer->id, $idProduct, $idAttr);
        $this->json((bool) $ok, $ok
            ? $this->module->l('Subscribed — we’ll remind you when it’s time to reorder.', 'subscribe')
            : $this->module->l('Could not create the subscription.', 'subscribe'));
    }

    protected function json($success, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool) $success, 'message' => $message]);
        exit;
    }
}
