<?php
/**
 * IT Store — Saved cart.
 *
 * When a logged-in customer returns with an empty cart but has an earlier,
 * un-ordered cart with items, show a "restore your saved cart" banner. The
 * restore controller copies those items into the current cart.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoresavedcart extends Module
{
    public function __construct()
    {
        $this->name = 'itstoresavedcart';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Saved Cart');
        $this->description = $this->l('Offer returning customers to restore a previously saved cart.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'itstore-savedcart',
            'modules/' . $this->name . '/views/css/savedcart.css',
            ['media' => 'all', 'priority' => 150]
        );
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        $customer = $this->context->customer;
        if (!$customer || !$customer->isLogged()) {
            return '';
        }

        $currentCart = $this->context->cart;
        // Only prompt when the active cart is empty.
        if ($currentCart && (int) $currentCart->nbProducts() > 0) {
            return '';
        }

        $saved = $this->findSavedCart((int) $customer->id, (int) ($currentCart ? $currentCart->id : 0));
        if (!$saved) {
            return '';
        }

        $this->smarty->assign([
            'sc_date' => Tools::displayDate($saved['date_upd']),
            'sc_count' => (int) $saved['items'],
            'sc_restore_url' => $this->context->link->getModuleLink(
                $this->name,
                'restore',
                ['id_cart' => (int) $saved['id_cart'], 'token' => Tools::getToken('itstoresavedcart' . (int) $saved['id_cart'])],
                true
            ),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/savedcart.tpl');
    }

    /**
     * Most recent un-ordered cart (with items) for the customer, excluding the
     * active one.
     */
    protected function findSavedCart($idCustomer, $idCurrentCart)
    {
        $idShop = (int) $this->context->shop->id;

        return Db::getInstance()->getRow(
            'SELECT c.id_cart, c.date_upd, SUM(cp.quantity) AS items
             FROM `' . _DB_PREFIX_ . 'cart` c
             INNER JOIN `' . _DB_PREFIX_ . 'cart_product` cp ON (cp.id_cart = c.id_cart)
             WHERE c.id_customer = ' . (int) $idCustomer . '
               AND c.id_shop = ' . $idShop . '
               AND c.id_cart <> ' . (int) $idCurrentCart . '
               AND NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'orders` o WHERE o.id_cart = c.id_cart)
             GROUP BY c.id_cart
             ORDER BY c.date_upd DESC'
        ) ?: null;
    }
}
