<?php
/**
 * IT Store — Product reviews.
 *
 * Verified-buyer product reviews with a star-rating summary, a moderated review
 * list and a submission form. Reviews are stored in `itstore_review` and
 * moderated from the module's back office.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

class Itstorereviews extends Module
{
    public function __construct()
    {
        $this->name = 'itstorereviews';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Reviews', [], 'Modules.Itstorereviews.Admin');
        $this->description = $this->trans('Verified-buyer product reviews with ratings and moderation.', [], 'Modules.Itstorereviews.Admin');
    }

    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'itstore_review` (
            `id_review` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(10) UNSIGNED NOT NULL DEFAULT 1,
            `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `customer_name` VARCHAR(255) NOT NULL,
            `rating` TINYINT(1) UNSIGNED NOT NULL DEFAULT 5,
            `title` VARCHAR(255) NOT NULL DEFAULT "",
            `content` TEXT,
            `verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `approved` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_review`),
            KEY `id_product` (`id_product`, `approved`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        Configuration::updateValue('ITSTORE_RV_AUTOAPPROVE', 0);

        return parent::install()
            && Db::getInstance()->execute($sql)
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('actionDeleteGDPRData');
    }

    public function uninstall()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itstore_review`;');
        Configuration::deleteByName('ITSTORE_RV_AUTOAPPROVE');

        return parent::uninstall();
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'itstore-reviews',
                'modules/' . $this->name . '/views/css/reviews.css',
                ['media' => 'all', 'priority' => 145]
            );
            $this->context->controller->registerJavascript(
                'itstore-reviews',
                'modules/' . $this->name . '/views/js/reviews.js',
                ['position' => 'bottom', 'priority' => 145, 'attribute' => 'defer']
            );
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = 0;
        if (isset($params['product'])) {
            $p = $params['product'];
            $idProduct = (int) (is_array($p) ? (isset($p['id_product']) ? $p['id_product'] : 0) : $p->id);
        }
        if ($idProduct <= 0) {
            return [];
        }

        $idShop = (int) $this->context->shop->id;
        $reviews = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'itstore_review`
             WHERE id_product = ' . $idProduct . ' AND id_shop = ' . $idShop . ' AND approved = 1
             ORDER BY date_add DESC'
        ) ?: [];

        $count = count($reviews);
        $avg = 0;
        if ($count) {
            $sum = 0;
            foreach ($reviews as $r) {
                $sum += (int) $r['rating'];
            }
            $avg = round($sum / $count, 1);
        }

        $customer = $this->context->customer;
        $canReview = $customer && $customer->isLogged();

        $this->smarty->assign([
            'itstore_rv_jsonld' => $count ? $this->buildJsonLd($idProduct, $reviews, $avg, $count) : '',
            'itstore_rv_reviews' => $reviews,
            'itstore_rv_count' => $count,
            'itstore_rv_avg' => $avg,
            'itstore_rv_id_product' => $idProduct,
            'itstore_rv_can_review' => $canReview,
            'itstore_rv_customer_name' => $canReview ? trim($customer->firstname . ' ' . Tools::substr($customer->lastname, 0, 1) . '.') : '',
            'itstore_rv_submit_url' => $this->context->link->getModuleLink($this->name, 'submit', [], true),
            'itstore_rv_token' => Tools::getToken('itstorereviews' . $idProduct),
        ]);

        $html = $this->fetch('module:' . $this->name . '/views/templates/hook/reviews.tpl');

        $title = $this->trans('Reviews', [], 'Modules.Itstorereviews.Admin');
        if ($count) {
            $title .= ' (' . $count . ')';
        }

        $extra = new ProductExtraContent();
        $extra->setTitle($title)->setContent($html);

        return [$extra];
    }

    /**
     * Build AggregateRating + Review JSON-LD for rich snippets.
     */
    protected function buildJsonLd($idProduct, array $reviews, $avg, $count)
    {
        $idLang = (int) $this->context->language->id;
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product)) {
            return '';
        }

        $reviewNodes = [];
        foreach (array_slice($reviews, 0, 10) as $r) {
            $reviewNodes[] = [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $r['rating'],
                    'bestRating' => 5,
                ],
                'author' => ['@type' => 'Person', 'name' => $r['customer_name']],
                'datePublished' => date('Y-m-d', strtotime($r['date_add'])),
                'reviewBody' => Tools::substr(strip_tags((string) $r['content']), 0, 500),
            ];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => is_array($product->name) ? reset($product->name) : $product->name,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $avg,
                'reviewCount' => (int) $count,
                'bestRating' => 5,
                'worstRating' => 1,
            ],
            'review' => $reviewNodes,
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * GDPR (psgdpr): export the reviews written by this customer.
     */
    public function hookActionExportGDPRData($params)
    {
        $idCustomer = $this->gdprCustomerId($params);
        if (!$idCustomer) {
            return '';
        }
        $rows = Db::getInstance()->executeS(
            'SELECT id_review, id_product, customer_name, rating, title, content, date_add
             FROM `' . _DB_PREFIX_ . 'itstore_review` WHERE id_customer = ' . (int) $idCustomer
        ) ?: [];

        return json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * GDPR (psgdpr): erase the reviews written by this customer.
     */
    public function hookActionDeleteGDPRData($params)
    {
        $idCustomer = $this->gdprCustomerId($params);
        if (!$idCustomer) {
            return '';
        }

        return json_encode((bool) Db::getInstance()->delete('itstore_review', 'id_customer = ' . (int) $idCustomer));
    }

    /**
     * Resolve the customer id from the psgdpr hook payload (version-tolerant).
     */
    protected function gdprCustomerId($params)
    {
        if (isset($params['id'])) {
            return (int) $params['id'];
        }
        if (isset($params['customer']['id'])) {
            return (int) $params['customer']['id'];
        }
        if (isset($params['customer']) && $params['customer'] instanceof Customer) {
            return (int) $params['customer']->id;
        }

        return 0;
    }

    /**
     * Whether a customer has a valid order containing the product.
     */
    public function customerHasPurchased($idCustomer, $idProduct)
    {
        if (!$idCustomer) {
            return false;
        }

        return (bool) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` o
             INNER JOIN `' . _DB_PREFIX_ . 'order_detail` od ON (od.id_order = o.id_order)
             WHERE o.id_customer = ' . (int) $idCustomer . '
               AND od.product_id = ' . (int) $idProduct . '
               AND o.valid = 1'
        );
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitItstoreRv')) {
            Configuration::updateValue('ITSTORE_RV_AUTOAPPROVE', (int) Tools::getValue('ITSTORE_RV_AUTOAPPROVE'));
            $output .= $this->displayConfirmation($this->trans('Settings saved.', [], 'Modules.Itstorereviews.Admin'));
        } elseif (Tools::isSubmit('approveReview')) {
            Db::getInstance()->update('itstore_review', ['approved' => 1], 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->trans('Review approved.', [], 'Modules.Itstorereviews.Admin'));
        } elseif (Tools::isSubmit('unapproveReview')) {
            Db::getInstance()->update('itstore_review', ['approved' => 0], 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->trans('Review hidden.', [], 'Modules.Itstorereviews.Admin'));
        } elseif (Tools::isSubmit('deleteReview')) {
            Db::getInstance()->delete('itstore_review', 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->trans('Review deleted.', [], 'Modules.Itstorereviews.Admin'));
        }

        return $output . $this->renderModeration() . $this->renderForm();
    }

    protected function renderModeration()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT r.*, pl.name FROM `' . _DB_PREFIX_ . 'itstore_review` r
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
               ON (pl.id_product = r.id_product AND pl.id_lang = ' . (int) $this->context->language->id . '
                   AND pl.id_shop = r.id_shop)
             ORDER BY r.approved ASC, r.date_add DESC'
        ) ?: [];

        $token = Tools::getAdminTokenLite('AdminModules');
        $base = $this->context->link->getAdminLink('AdminModules', false)
            . '&token=' . $token
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $body = '';
        foreach (array_slice($rows, 0, 200) as $r) {
            $action = (int) $r['approved']
                ? '<a class="btn btn-default btn-xs" href="' . $base . '&unapproveReview&id_review=' . (int) $r['id_review'] . '">' . $this->trans('Hide', [], 'Modules.Itstorereviews.Admin') . '</a>'
                : '<a class="btn btn-success btn-xs" href="' . $base . '&approveReview&id_review=' . (int) $r['id_review'] . '">' . $this->trans('Approve', [], 'Modules.Itstorereviews.Admin') . '</a>';
            $body .= '<tr>'
                . '<td>' . str_repeat('★', (int) $r['rating']) . '</td>'
                . '<td>' . htmlspecialchars((string) $r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['customer_name']) . ((int) $r['verified'] ? ' <span class="badge badge-success">' . $this->trans('Verified', [], 'Modules.Itstorereviews.Admin') . '</span>' : '') . '</td>'
                . '<td>' . htmlspecialchars(Tools::substr((string) $r['content'], 0, 90)) . '</td>'
                . '<td>' . ((int) $r['approved'] ? $this->trans('Live', [], 'Modules.Itstorereviews.Admin') : '<strong>' . $this->trans('Pending', [], 'Modules.Itstorereviews.Admin') . '</strong>') . '</td>'
                . '<td class="text-right">' . $action
                . ' <a class="btn btn-danger btn-xs" onclick="return confirm(\'' . $this->trans('Delete?', [], 'Modules.Itstorereviews.Admin') . '\')" href="' . $base . '&deleteReview&id_review=' . (int) $r['id_review'] . '">' . $this->trans('Delete', [], 'Modules.Itstorereviews.Admin') . '</a></td>'
                . '</tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="6">' . $this->trans('No reviews yet.', [], 'Modules.Itstorereviews.Admin') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-star"></i> ' . $this->trans('Moderate reviews', [], 'Modules.Itstorereviews.Admin') . '</div>'
            . '<table class="table"><thead><tr><th>' . $this->trans('Rating', [], 'Modules.Itstorereviews.Admin') . '</th><th>' . $this->trans('Product', [], 'Modules.Itstorereviews.Admin') . '</th><th>' . $this->trans('Author', [], 'Modules.Itstorereviews.Admin')
            . '</th><th>' . $this->trans('Extract', [], 'Modules.Itstorereviews.Admin') . '</th><th>' . $this->trans('Status', [], 'Modules.Itstorereviews.Admin') . '</th><th class="text-right">' . $this->trans('Actions', [], 'Modules.Itstorereviews.Admin') . '</th></tr></thead><tbody>'
            . $body . '</tbody></table></div>';
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->trans('Review settings', [], 'Modules.Itstorereviews.Admin'), 'icon' => 'icon-cogs'],
            'input' => [[
                'type' => 'switch', 'label' => $this->trans('Auto-approve new reviews', [], 'Modules.Itstorereviews.Admin'), 'name' => 'ITSTORE_RV_AUTOAPPROVE', 'is_bool' => true,
                'values' => [
                    ['id' => 'aa_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.Itstorereviews.Admin')],
                    ['id' => 'aa_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.Itstorereviews.Admin')],
                ],
                'desc' => $this->trans('Off = new reviews wait for moderation.', [], 'Modules.Itstorereviews.Admin'),
            ]],
            'submit' => ['title' => $this->trans('Save', [], 'Modules.Itstorereviews.Admin'), 'name' => 'submitItstoreRv'],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitItstoreRv';
        $helper->fields_value = ['ITSTORE_RV_AUTOAPPROVE' => (int) Configuration::get('ITSTORE_RV_AUTOAPPROVE')];

        return $helper->generateForm([$form]);
    }
}
