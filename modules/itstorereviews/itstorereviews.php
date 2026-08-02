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
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IT Store Reviews');
        $this->description = $this->l('Verified-buyer product reviews with ratings and moderation.');
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
            && $this->registerHook('actionFrontControllerSetMedia');
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
                ['position' => 'bottom', 'priority' => 145]
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

        $title = $this->l('Reviews');
        if ($count) {
            $title .= ' (' . $count . ')';
        }

        $extra = new ProductExtraContent();
        $extra->setTitle($title)->setContent($html);

        return [$extra];
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
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        } elseif (Tools::isSubmit('approveReview')) {
            Db::getInstance()->update('itstore_review', ['approved' => 1], 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->l('Review approved.'));
        } elseif (Tools::isSubmit('unapproveReview')) {
            Db::getInstance()->update('itstore_review', ['approved' => 0], 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->l('Review hidden.'));
        } elseif (Tools::isSubmit('deleteReview')) {
            Db::getInstance()->delete('itstore_review', 'id_review = ' . (int) Tools::getValue('id_review'));
            $output .= $this->displayConfirmation($this->l('Review deleted.'));
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
                ? '<a class="btn btn-default btn-xs" href="' . $base . '&unapproveReview&id_review=' . (int) $r['id_review'] . '">' . $this->l('Hide') . '</a>'
                : '<a class="btn btn-success btn-xs" href="' . $base . '&approveReview&id_review=' . (int) $r['id_review'] . '">' . $this->l('Approve') . '</a>';
            $body .= '<tr>'
                . '<td>' . str_repeat('★', (int) $r['rating']) . '</td>'
                . '<td>' . htmlspecialchars((string) $r['name']) . '</td>'
                . '<td>' . htmlspecialchars($r['customer_name']) . ((int) $r['verified'] ? ' <span class="badge badge-success">' . $this->l('Verified') . '</span>' : '') . '</td>'
                . '<td>' . htmlspecialchars(Tools::substr((string) $r['content'], 0, 90)) . '</td>'
                . '<td>' . ((int) $r['approved'] ? $this->l('Live') : '<strong>' . $this->l('Pending') . '</strong>') . '</td>'
                . '<td class="text-right">' . $action
                . ' <a class="btn btn-danger btn-xs" onclick="return confirm(\'' . $this->l('Delete?') . '\')" href="' . $base . '&deleteReview&id_review=' . (int) $r['id_review'] . '">' . $this->l('Delete') . '</a></td>'
                . '</tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="6">' . $this->l('No reviews yet.') . '</td></tr>';
        }

        return '<div class="panel"><div class="panel-heading"><i class="icon-star"></i> ' . $this->l('Moderate reviews') . '</div>'
            . '<table class="table"><thead><tr><th>' . $this->l('Rating') . '</th><th>' . $this->l('Product') . '</th><th>' . $this->l('Author')
            . '</th><th>' . $this->l('Extract') . '</th><th>' . $this->l('Status') . '</th><th class="text-right">' . $this->l('Actions') . '</th></tr></thead><tbody>'
            . $body . '</tbody></table></div>';
    }

    protected function renderForm()
    {
        $form = ['form' => [
            'legend' => ['title' => $this->l('Review settings'), 'icon' => 'icon-cogs'],
            'input' => [[
                'type' => 'switch', 'label' => $this->l('Auto-approve new reviews'), 'name' => 'ITSTORE_RV_AUTOAPPROVE', 'is_bool' => true,
                'values' => [
                    ['id' => 'aa_on', 'value' => 1, 'label' => $this->l('Yes')],
                    ['id' => 'aa_off', 'value' => 0, 'label' => $this->l('No')],
                ],
                'desc' => $this->l('Off = new reviews wait for moderation.'),
            ]],
            'submit' => ['title' => $this->l('Save'), 'name' => 'submitItstoreRv'],
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
