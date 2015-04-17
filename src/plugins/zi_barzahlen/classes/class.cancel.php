<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.log.php';
require_once dirname(__FILE__) . '/api/loader.php';

class barzahlen_cancel
{
    /**
     * Connects to the Barzahlen API and returns the response for the cancel request.
     *
     * @return boolean TRUE or FALSE depending on xml response
     */
    public static function tryCancel()
    {
        $sandbox = $_GET['sandbox'] == 'true' ? true : false;
        $api = new Barzahlen_Api($_GET['shop_id'], $_GET['payment_key'], $sandbox);
        $api->setUserAgent('xt:Commerce 4.2 / Plugin v1.2.0');
        $cancel = new Barzahlen_Request_Cancel($_GET['transaction_id']);

        try {
            $api->handleRequest($cancel);
        } catch (Exception $e) {
            barzahlen_log::error('api/cancel: ' . $e, $_GET);
        }

        if ($cancel->isValid()) {
            global $db;

            $transaction_id = filter_var($_GET['transaction_id'], FILTER_SANITIZE_NUMBER_INT);

            $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                      SET zi_state = 'TEXT_ZI_CANCELED'
                      WHERE transaction_id = '" . $transaction_id . "'");

            $rs = $db->Execute("SELECT order_id FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                             WHERE transaction_id = '" . $transaction_id . "'");

            $order = new order($rs->fields['order_id'], -1);
            $order->_updateOrderStatus(ZI_BARZAHLEN_EXPIRED, 'Barzahlen: Zahlschein storniert', 'false', 'false', 'admin', 0);
        }

        return $cancel->isValid();
    }
}
