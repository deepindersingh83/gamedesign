<?php
/**
 * IT Store — comparison page controller.
 *
 * Builds a side-by-side feature table for up to four products passed as a
 * comma-separated `ids` query parameter.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorecompareCompareModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $idLang = (int) $this->context->language->id;
        $ids = array_filter(array_map('intval', explode(',', (string) Tools::getValue('ids'))));
        $ids = array_slice(array_unique($ids), 0, 4);

        $products = [];
        $featureNames = [];
        foreach ($ids as $id) {
            $product = new Product($id, false, $idLang);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $map = [];
            foreach (Product::getFrontFeaturesStatic($idLang, $id) as $f) {
                if (!isset($f['name'])) {
                    continue;
                }
                $map[$f['name']] = isset($f['value']) ? $f['value'] : '';
                $featureNames[$f['name']] = true;
            }

            $cover = Product::getCover($id);
            $coverId = is_array($cover) && isset($cover['id_image']) ? (int) $cover['id_image'] : 0;

            $products[] = [
                'id' => $id,
                'name' => $product->name,
                'url' => $this->context->link->getProductLink($id),
                'image' => $this->context->link->getImageLink($product->link_rewrite, $coverId, 'home_default'),
                'price' => $this->formatPrice((float) Product::getPriceStatic($id, true)),
                'features' => $map,
            ];
        }

        $this->context->smarty->assign([
            'compare_products' => $products,
            'compare_feature_names' => array_keys($featureNames),
        ]);

        $this->setTemplate('module:itstorecompare/views/templates/front/compare.tpl');
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
