<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/
 *
 * @copyright   Copyright (c) 2012 Zerebro Internet GmbH (http://www.barzahlen.de)
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
            $ipn->updateDatabase();
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
