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

class barzahlen_resend
{
    /**
     * Connects to the Barzahlen API and returns the response for the resend request.
     *
     * @return boolean TRUE or FALSE depending on xml response
     */
    public static function tryResend()
    {
        $sandbox = $_GET['sandbox'] == 'true' ? true : false;
        $api = new Barzahlen_Api($_GET['shop_id'], $_GET['payment_key'], $sandbox);
        $api->setUserAgent('xt:Commerce 4.0-4.1 / Plugin v1.2.1');
        $resend = new Barzahlen_Request_Resend($_GET['transaction_id']);

        try {
            $api->handleRequest($resend);
        } catch (Exception $e) {
            barzahlen_log::error('api/resend: ' . $e, $_GET);
        }

        return $resend->isValid();
    }
}
