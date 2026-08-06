<?php
/**
 * IT Store — Order tracking.
 *
 * A guest-friendly order status lookup: enter the order reference + the email
 * on the order to see its current status and key dates. Powers the header
 * "Track Order" link.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Itstoreordertrack extends Module
{
    public function __construct()
    {
        $this->name = 'itstoreordertrack';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Syber Info';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => '9.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('IT Store Order Tracking', [], 'Modules.Itstoreordertrack.Admin');
        $this->description = $this->trans('Guest order status lookup by reference + email.', [], 'Modules.Itstoreordertrack.Admin');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function hookActionFrontControllerSetMedia()
    {
        if (Tools::getValue('module') === $this->name) {
            $this->context->controller->registerStylesheet(
                'itstore-ordertrack',
                'modules/' . $this->name . '/views/css/ordertrack.css',
                ['media' => 'all', 'priority' => 150]
            );
        }
    }
}
