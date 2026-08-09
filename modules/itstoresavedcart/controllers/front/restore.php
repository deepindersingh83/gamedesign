<?php
/**
 * IT Store — restore a saved cart into the current cart.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoresavedcartRestoreModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->restore();
        Tools::redirect($this->context->link->getPageLink('cart', true, null, ['action' => 'show']));
    }

    protected function restore()
    {
        $customer = $this->context->customer;
        if (!$customer || !$customer->isLogged()) {
            return;
        }

        $idSaved = (int) Tools::getValue('id_cart');
        $token = (string) Tools::getValue('token');
        if (!$idSaved || $token !== Tools::getToken('itstoresavedcart' . $idSaved)) {
            return;
        }

        $saved = new Cart($idSaved);
        if (!Validate::isLoadedObject($saved) || (int) $saved->id_customer !== (int) $customer->id) {
            return;
        }

        $current = $this->context->cart;
        if (!$current || !$current->id) {
            $current = new Cart();
            $current->id_customer = (int) $customer->id;
            $current->id_lang = (int) $this->context->language->id;
            $current->id_currency = (int) $this->context->currency->id;
            $current->add();
            $this->context->cookie->id_cart = (int) $current->id;
            $this->context->cart = $current;
        }

        foreach ($saved->getProducts() as $p) {
            $current->updateQty(
                (int) $p['cart_quantity'],
                (int) $p['id_product'],
                (int) $p['id_product_attribute']
            );
        }
        $current->update();
    }
}
