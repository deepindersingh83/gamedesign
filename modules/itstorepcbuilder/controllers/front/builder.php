<?php
/**
 * IT Store — PC builder page controller.
 *
 * Lists products per component slot and, on submit, adds every selected product
 * to the cart before redirecting to it.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorepcbuilderBuilderModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Tools::isSubmit('addBuild')) {
            return;
        }

        $ids = Tools::getValue('build');
        if (!is_array($ids)) {
            return;
        }

        $cart = $this->context->cart;
        if (!$cart->id) {
            $cart->add();
            $this->context->cookie->id_cart = (int) $cart->id;
            $this->context->cart = $cart;
        }

        foreach ($ids as $idProduct) {
            $idProduct = (int) $idProduct;
            if ($idProduct <= 0) {
                continue;
            }
            $product = new Product($idProduct);
            if (!Validate::isLoadedObject($product) || !$product->active) {
                continue;
            }
            $cart->updateQty(1, $idProduct);
        }
        $cart->update();

        Tools::redirect($this->context->link->getPageLink('cart', true, null, ['action' => 'show']));
    }

    public function initContent()
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $compatFeature = trim((string) Configuration::get('ITSTORE_PB_COMPAT'));
        $slots = [];

        foreach ($this->module->slots() as $key => $label) {
            $catId = (int) Configuration::get('ITSTORE_PB_' . $key);
            $products = [];
            if ($catId > 0) {
                $category = new Category($catId, $idLang);
                if (Validate::isLoadedObject($category)) {
                    $rows = $category->getProducts($idLang, 1, 60, 'name', 'asc');
                    foreach ((is_array($rows) ? $rows : []) as $row) {
                        $id = (int) $row['id_product'];
                        $priceRaw = (float) Product::getPriceStatic($id, true);
                        $products[] = [
                            'id' => $id,
                            'name' => isset($row['name']) ? $row['name'] : '',
                            'price_raw' => $priceRaw,
                            'price' => $this->formatPrice($priceRaw),
                            'compat' => $compatFeature !== '' ? $this->featureValue($id, $idLang, $compatFeature) : '',
                        ];
                    }
                }
            }
            $slots[] = [
                'key' => $key,
                'label' => $label,
                'products' => $products,
                // Only CPU and Motherboard participate in the compatibility check.
                'compat_check' => in_array($key, ['CPU', 'MB'], true) && $compatFeature !== '',
            ];
        }

        $this->context->smarty->assign([
            'pb_slots' => $slots,
            'pb_compat_feature' => $compatFeature,
            'pb_action' => $this->context->link->getModuleLink('itstorepcbuilder', 'builder', [], true),
            'pb_currency_sign' => $this->context->currency ? $this->context->currency->sign : '',
        ]);

        $this->setTemplate('module:itstorepcbuilder/views/templates/front/builder.tpl');
    }

    /**
     * Value of a named feature for a product (empty string if absent).
     */
    protected function featureValue($idProduct, $idLang, $featureName)
    {
        foreach (Product::getFrontFeaturesStatic($idLang, $idProduct) as $f) {
            if (isset($f['name']) && strcasecmp($f['name'], $featureName) === 0) {
                return isset($f['value']) ? $f['value'] : '';
            }
        }

        return '';
    }

    protected function formatPrice($price)
    {
        $iso = $this->context->currency ? $this->context->currency->iso_code : null;
        if (method_exists($this->context, 'getCurrentLocale')) {
            $locale = $this->context->getCurrentLocale();
            if ($locale) {
                return $locale->formatPrice($price, $iso);
            }
        }

        return Tools::displayPrice($price);
    }
}
