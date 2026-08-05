<?php
/**
 * IT Store — question submission endpoint.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreaskquestionAskModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $question = trim((string) Tools::getValue('question'));
        $email = trim((string) Tools::getValue('email'));
        $token = (string) Tools::getValue('token');

        if (!$idProduct || $token !== Tools::getToken('itstoreaskquestion' . $idProduct)) {
            $this->ajaxJson(false, $this->module->l('Invalid request.', 'ask'));
        }
        if (Tools::strlen($question) < 5 || !Validate::isCleanHtml($question)) {
            $this->ajaxJson(false, $this->module->l('Please enter your question.', 'ask'));
        }
        if ($email !== '' && !Validate::isEmail($email)) {
            $this->ajaxJson(false, $this->module->l('Please enter a valid email or leave it blank.', 'ask'));
        }

        Db::getInstance()->insert('itstore_question', [
            'id_product' => $idProduct,
            'id_shop' => (int) $this->context->shop->id,
            'email' => pSQL($email),
            'question' => pSQL($question, true),
            'answer' => '',
            'approved' => 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $to = Configuration::get('PS_SHOP_EMAIL');
        if (Validate::isEmail($to)) {
            Mail::Send(
                (int) $this->context->language->id,
                'contact',
                $this->module->l('New product question', 'ask'),
                ['{email}' => $email ?: $to, '{message}' => $question],
                $to
            );
        }

        $this->ajaxJson(true, $this->module->l('Thanks — your question has been sent to our product team; we reply by email within 1 business day.', 'ask'));
    }

    protected function ajaxJson($success, $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool) $success, 'message' => $message]);
        exit;
    }
}
