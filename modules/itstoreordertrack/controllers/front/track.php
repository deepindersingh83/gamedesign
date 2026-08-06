<?php
/**
 * IT Store — order tracking page.
 *
 * Looks up an order by reference and verifies the supplied email matches the
 * order's customer before revealing status. Rate-agnostic, guest-friendly.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreordertrackTrackModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $result = null;
        $error = null;

        if (Tools::isSubmit('submitTrack')) {
            $reference = trim((string) Tools::getValue('reference'));
            $email = trim((string) Tools::getValue('email'));

            if ($reference === '' || !Validate::isEmail($email)) {
                $error = $this->module->l('Enter your order reference and the email on the order.', 'track');
            } else {
                $result = $this->lookup($reference, $email);
                if ($result === null) {
                    $error = $this->module->l('No order matches that reference and email.', 'track');
                }
            }
        }

        $this->context->smarty->assign([
            'track_action' => $this->context->link->getModuleLink('itstoreordertrack', 'track', [], true),
            'track_result' => $result,
            'track_error' => $error,
        ]);

        $this->setTemplate('module:itstoreordertrack/views/templates/front/track.tpl');
    }

    /**
     * @return array|null
     */
    protected function lookup($reference, $email)
    {
        $orders = Order::getByReference($reference);
        if (!$orders || !$orders->count()) {
            return null;
        }

        /** @var Order $order */
        $order = $orders->getFirst();
        if (!Validate::isLoadedObject($order)) {
            return null;
        }

        $customer = new Customer((int) $order->id_customer);
        if (!Validate::isLoadedObject($customer) || Tools::strtolower($customer->email) !== Tools::strtolower($email)) {
            return null;
        }

        $idLang = (int) $this->context->language->id;
        $state = $order->getCurrentStateFull($idLang);

        $steps = [];
        foreach ($order->getHistory($idLang) as $h) {
            if (!empty($h['id_order_state']) && !empty($h['ostate_name'])) {
                $steps[] = ['name' => $h['ostate_name'], 'date' => $h['date_add']];
            }
        }

        return [
            'reference' => $order->reference,
            'status' => isset($state['name']) ? $state['name'] : '',
            'date' => $order->date_add,
            'total' => $this->context->currency
                ? $this->context->getCurrentLocale()->formatPrice((float) $order->total_paid, $this->context->currency->iso_code)
                : $order->total_paid,
            'steps' => $steps,
        ];
    }
}
