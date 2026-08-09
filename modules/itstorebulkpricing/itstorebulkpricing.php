<?php
/**
 * IT Store — Bulk / Business pricing.
 *
 * The design's product-page "Bulk / Business Pricing" card: quantity tiers with
 * a per-unit price. Tiers are configured as quantity thresholds + a percentage
 * off the unit price, so they apply to every product without per-product data.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstorebulkpricing extends Module
{
    const T = 3;

    public function __construct()
    {
        $this->name = 'itstorebulkpricing';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Bulk Pricing', [], 'Modules.Itstorebulkpricing.Admin');
        $this->description = $this->trans('Bulk / business quantity pricing tiers on the product page.', [], 'Modules.Itstorebulkpricing.Admin');
    }

    protected function defaults()
    {
        return [
            'ITSTORE_BP_ENABLED' => 1,
            'ITSTORE_BP_1_QTY' => 5, 'ITSTORE_BP_1_DISC' => 5,
            'ITSTORE_BP_2_QTY' => 10, 'ITSTORE_BP_2_DISC' => 10,
            'ITSTORE_BP_3_QTY' => 25, 'ITSTORE_BP_3_DISC' => 15,
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('actionFrontControllerSetMedia')) {
            return false;
        }
        foreach ($this->defaults() as $k => $v) {
            Configuration::updateValue($k, $v);
        }

        return true;
    }

    public function uninstall()
    {
        foreach (array_keys($this->defaults()) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-bulkpricing',
                'modules/' . $this->name . '/views/css/bulkpricing.css',
                ['media' => 'all', 'priority' => 146]
            );
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!(int) Configuration::get('ITSTORE_BP_ENABLED')) {
            return '';
        }

        $price = $this->extractPrice($params);
        if ($price <= 0) {
            return '';
        }

        $rows = [[
            'qty' => '1–' . (max(2, (int) Configuration::get('ITSTORE_BP_1_QTY')) - 1),
            'price' => $this->formatPrice($price),
        ]];
        for ($i = 1; $i <= self::T; $i++) {
            $qty = (int) Configuration::get('ITSTORE_BP_' . $i . '_QTY');
            $disc = (float) Configuration::get('ITSTORE_BP_' . $i . '_DISC');
            if ($qty <= 0) {
                continue;
            }
            $next = ($i < self::T) ? (int) Configuration::get('ITSTORE_BP_' . ($i + 1) . '_QTY') : 0;
            $label = $next > $qty ? ($qty . '–' . ($next - 1)) : ($qty . '+');
            $rows[] = ['qty' => $label, 'price' => $this->formatPrice($price * (1 - $disc / 100))];
        }

        $this->smarty->assign('itstore_bp_rows', $rows);

        return $this->display(__FILE__, 'views/templates/hook/bulkpricing.tpl');
    }

    protected function extractPrice($params)
    {
        if (!isset($params['product'])) {
            return 0.0;
        }
        $p = $params['product'];
        if (is_array($p)) {
            if (isset($p['price_amount'])) {
                return (float) $p['price_amount'];
            }
            if (isset($p['id_product'])) {
                return (float) Product::getPriceStatic((int) $p['id_product'], true);
            }
        } elseif (is_object($p) && isset($p->id)) {
            return (float) Product::getPriceStatic((int) $p->id, true);
        }

        return 0.0;
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

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitItstoreBp')) {
            foreach (array_keys($this->defaults()) as $k) {
                Configuration::updateValue($k, (int) Tools::getValue($k));
            }
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorebulkpricing.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [[
            'type' => 'switch', 'label' => $this->trans('Enabled', [], 'Modules.Itstorebulkpricing.Admin'), 'name' => 'ITSTORE_BP_ENABLED', 'is_bool' => true,
            'values' => [
                ['id' => 'bp_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstorebulkpricing.Admin')],
                ['id' => 'bp_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstorebulkpricing.Admin')],
            ],
        ]];
        for ($i = 1; $i <= self::T; $i++) {
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Tier %d — min qty', [], 'Modules.Itstorebulkpricing.Admin'), $i), 'name' => 'ITSTORE_BP_' . $i . '_QTY', 'class' => 'fixed-width-sm'];
            $fields[] = ['type' => 'text', 'label' => sprintf($this->trans('Tier %d — %% off', [], 'Modules.Itstorebulkpricing.Admin'), $i), 'name' => 'ITSTORE_BP_' . $i . '_DISC', 'class' => 'fixed-width-sm'];
        }

        $form = ['form' => [
            'legend' => ['title' => $this->trans('Bulk pricing tiers', [], 'Modules.Itstorebulkpricing.Admin'), 'icon' => 'icon-table'],
            'input' => $fields,
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorebulkpricing.Admin'), 'name' => 'submitItstoreBp'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreBp';
        $values = [];
        foreach (array_keys($this->defaults()) as $k) {
            $values[$k] = Configuration::get($k);
        }
        $helper->fields_value = $values;

        return $helper->generateForm([$form]);
    }
}
