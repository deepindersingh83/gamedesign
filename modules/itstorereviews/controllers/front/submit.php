<?php
/**
 * IT Store — review submission endpoint.
 *
 * Validates and stores a product review. Marks it "verified" when the logged-in
 * customer has a valid order containing the product, and auto-approves only if
 * the shop has enabled it.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorereviewsSubmitModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $rating = (int) Tools::getValue('rating');
        $title = trim((string) Tools::getValue('title'));
        $content = trim((string) Tools::getValue('content'));
        $token = (string) Tools::getValue('token');

        if (!$idProduct || $token !== Tools::getToken('itstorereviews' . $idProduct)) {
            $this->ajaxJson(false, $this->module->l('Invalid request.', 'submit'));
        }
        // Silent-drop spam bots: acknowledge without storing.
        if ($this->isLikelySpam()) {
            $this->ajaxJson(true, $this->module->l('Thanks for your review!', 'submit'));
        }

        $customer = $this->context->customer;
        if (!$customer || !$customer->isLogged()) {
            $this->ajaxJson(false, $this->module->l('Please sign in to leave a review.', 'submit'));
        }
        if ($rating < 1 || $rating > 5) {
            $this->ajaxJson(false, $this->module->l('Please choose a rating from 1 to 5.', 'submit'));
        }
        if (Tools::strlen($content) < 5) {
            $this->ajaxJson(false, $this->module->l('Please write a little more in your review.', 'submit'));
        }
        if ($title !== '' && !Validate::isCleanHtml($title)) {
            $this->ajaxJson(false, $this->module->l('Your title contains invalid characters.', 'submit'));
        }
        if (!Validate::isCleanHtml($content)) {
            $this->ajaxJson(false, $this->module->l('Your review contains invalid characters.', 'submit'));
        }

        $idShop = (int) $this->context->shop->id;
        $already = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'itstore_review`
             WHERE id_product = ' . $idProduct . ' AND id_customer = ' . (int) $customer->id . ' AND id_shop = ' . $idShop
        );
        if ($already) {
            $this->ajaxJson(false, $this->module->l('You have already reviewed this product.', 'submit'));
        }

        $verified = $this->module->customerHasPurchased((int) $customer->id, $idProduct) ? 1 : 0;
        $approved = (int) Configuration::get('ITSTORE_RV_AUTOAPPROVE') ? 1 : 0;
        $name = trim($customer->firstname . ' ' . Tools::substr($customer->lastname, 0, 1) . '.');

        Db::getInstance()->insert('itstore_review', [
            'id_product' => $idProduct,
            'id_shop' => $idShop,
            'id_customer' => (int) $customer->id,
            'customer_name' => pSQL($name),
            'rating' => $rating,
            'title' => pSQL($title),
            'content' => pSQL($content, true),
            'verified' => $verified,
            'approved' => $approved,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $msg = $approved
            ? $this->module->l('Thanks for your review!', 'submit')
            : $this->module->l('Thanks! Your review will appear once approved.', 'submit');

        $this->ajaxJson(true, $msg);
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
