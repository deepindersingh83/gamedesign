<?php
/**
 * IT Store — back-in-stock cron endpoint.
 *
 * Emails waiting customers when their product is back in stock. Protect with the
 * token shown on the module's configuration screen and call it from a server
 * cron, e.g. hourly.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstorestockCronModuleFrontController extends ModuleFrontController
{
    /** @var bool disable the normal template/layout */
    public $ajax = true;

    public function initContent()
    {
        parent::initContent();

        $token = (string) Tools::getValue('token');
        if (!hash_equals((string) Configuration::get('ITSTORE_STK_CRON_TOKEN'), $token)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        $sent = (int) $this->module->sendBackInStockAlerts();

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'sent' => $sent]);
        exit;
    }
}
