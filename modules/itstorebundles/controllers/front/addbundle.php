<?php
/**
 * IT Store — add a whole bundle (main product + its accessories) to the cart.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorebundlesAddbundleModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $token = (string) Tools::getValue('token');

        $backToProduct = $idProduct
            ? $this->context->link->getProductLink($idProduct)
            : $this->context->link->getPageLink('index');

        if (!$idProduct || !hash_equals(Tools::getToken('itstorebundles' . $idProduct), $token)) {
            $this->redirectWithError($backToProduct, $this->module->l('Invalid request.', 'addbundle'));
        }

        $idLang = (int) $this->context->language->id;
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product) || !$product->active) {
            $this->redirectWithError($backToProduct, $this->module->l('Product not available.', 'addbundle'));
        }

        // Main product first, then each accessory (deduplicated).
        $ids = [$idProduct];
        foreach ($product->getAccessories($idLang) as $a) {
            $ids[] = (int) $a['id_product'];
        }
        $ids = array_values(array_unique(array_filter($ids)));

        $cart = $this->context->cart;
        if (!$cart->id) {
            $cart->add();
            $this->context->cookie->id_cart = (int) $cart->id;
            $this->context->cart = $cart;
        }

        $added = 0;
        foreach ($ids as $id) {
            $p = new Product($id, false, $idLang);
            if (!Validate::isLoadedObject($p) || !$p->active) {
                continue;
            }
            // Respect stock unless out-of-stock ordering is allowed.
            $available = Product::getQuantity($id) > 0 || Product::isAvailableWhenOutOfStock((int) $p->out_of_stock);
            if (!$available) {
                continue;
            }
            try {
                if ($cart->updateQty(1, $id, null, false, 'up') !== false) {
                    ++$added;
                }
            } catch (Exception $e) {
                // Skip an item that cannot be added; keep going with the rest.
            }
        }

        CartRule::autoAddToCart($this->context);
        $this->context->cookie->write();

        $cartUrl = $this->context->link->getPageLink('cart', null, null, ['action' => 'show']);
        if ($added === 0) {
            $this->redirectWithError($backToProduct, $this->module->l('Sorry, none of the bundle items could be added.', 'addbundle'));
        }

        Tools::redirect($cartUrl);
    }

    protected function redirectWithError($url, $message)
    {
        $this->errors[] = $message;
        $this->context->cookie->itstore_bundle_error = $message;
        Tools::redirect($url);
    }
}
