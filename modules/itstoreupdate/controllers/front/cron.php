<?php
/**
 * IT Store — auto-update cron endpoint.
 *
 * Token-protected. Checks GitHub for a newer version and, when automatic
 * updates are enabled, applies it. Intended to be called from the server's
 * scheduler (e.g. daily). Responds with JSON.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'itstoreupdate/classes/ItstoreUpdater.php';

class ItstoreupdateCronModuleFrontController extends ModuleFrontController
{
    public $auth = false;

    public function initContent()
    {
        parent::initContent();
        $this->ajaxResponse();
    }

    /** Also handle direct GET without full FC render. */
    public function init()
    {
        parent::init();
    }

    protected function ajaxResponse()
    {
        header('Content-Type: application/json');

        $token = (string) Tools::getValue('token');
        $expected = (string) Configuration::get('ITSTORE_UPD_CRON_TOKEN');
        if ($expected === '' || !hash_equals($expected, $token)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'invalid_token']);
            exit;
        }

        $updater = new ItstoreUpdater();
        $check = $updater->check();
        if ($check === false) {
            echo json_encode(['ok' => false, 'error' => 'check_failed', 'log' => $updater->log]);
            exit;
        }

        $response = [
            'ok' => true,
            'current' => $check['current'],
            'latest' => $check['latest'],
            'update_available' => $check['update'],
            'auto' => (bool) Configuration::get('ITSTORE_UPD_AUTO'),
            'applied' => false,
        ];

        if ($check['update'] && (int) Configuration::get('ITSTORE_UPD_AUTO') === 1) {
            $ok = $updater->run(false);
            Configuration::updateValue('ITSTORE_UPD_LAST_LOG', implode("\n", $updater->log));
            Configuration::updateValue('ITSTORE_UPD_LAST_RUN', date('Y-m-d H:i:s'));
            $response['applied'] = $ok;
            $response['log'] = $updater->log;
        }

        echo json_encode($response);
        exit;
    }
}
