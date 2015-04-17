<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.ipn.php';
require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.log.php';

class callback_zi_barzahlen
{
    /**
     * This main callback function is called automatically by the class.callback.php from the
     * xtFramework. It validates the received data. If the validation is successful the HTTP
     * status 200 is send and the database is updated, otherwise 400 is send.
     */
    public function process()
    {
        $ipn = new barzahlen_ipn;

        if ($ipn->sendResponseHeader($_GET)) {
            $this->_sendHeader(200);
        } else {
            $this->_sendHeader(400);
        }
    }

    /**
     * Sends the header for a successful / failed hash comparision.
     *
     * @param integer $code http status code
     */
    protected function _sendHeader($code)
    {
        switch ($code) {
            case 200:
                header("HTTP/1.1 200 OK");
                header("Status: 200 OK");
                break;
            case 400:
                header("HTTP/1.1 400 Bad Request");
                header("Status: 400 Bad Request");
                break;
        }
    }
}
