<?php
/**
 * IT Store — subscription reminder cron.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreautoreorderCronModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function initContent()
    {
        parent::initContent();

        if (!hash_equals((string) Configuration::get('ITSTORE_AR_CRON_TOKEN'), (string) Tools::getValue('token'))) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        $sent = (int) $this->module->sendDueReminders();
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'reminders' => $sent]);
        exit;
    }
}
