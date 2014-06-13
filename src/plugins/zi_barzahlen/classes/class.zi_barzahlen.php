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

require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.log.php';
require_once dirname(__FILE__) . '/api/loader.php';

class zi_barzahlen
{
    // payment settings
    public $data = array(); //!< data for payment method
    public $external = false; //!< no external call within the shop
    public $version = '1.1.4'; //!< version of the payment module
    public $subpayments = false; //!< no subpayments (e.g. credit card) required
    public $iframe = false; //!< no iframe required
    private $_xmlArray; //!< xml response array

    /**
     * Attempts to get and save transaction id.
     *
     * @return boolean
     */
    public function registerTransactionId(array $order_data)
    {
        global $xtLink, $language;

        $customerEmail = $_SESSION['customer']->customer_info['customers_email_address'];
        $customerStreetNr = $order_data['billing']['customers_street_address'];
        $customerZipcode = $order_data['billing']['customers_postcode'];
        $customerCity = $order_data['billing']['customers_city'];
        $customerCountry = $order_data['billing']['customers_country_code'];
        $amount = round($_SESSION['cart']->total['plain'], 2);
        $currency = $order_data['currency_code'];
        $request = new Barzahlen_Request_Payment($customerEmail, $customerStreetNr, $customerZipcode, $customerCity, $customerCountry, $amount, $currency);

        $sandbox = ZI_BARZAHLEN_SANDBOX == 'true' ? true : false;
        $api = new Barzahlen_Api(ZI_BARZAHLEN_SID, ZI_BARZAHLEN_PID, $sandbox);
        $api->setLanguage($language->environment_language);

        $payment = $this->_connectBarzahlen($api, $request);

        if ($payment->isValid()) {
            $this->_xmlArray = $payment->getXmlArray();
        } else {
            $cancelUrl = $xtLink->_link(array('page' => 'checkout', 'paction' => 'payment', 'params' => 'error=ERROR_PAYMENT'));
            $xtLink->_redirect($cancelUrl);
        }
    }

    /**
     * Attempts to get and save transaction id.
     *
     * @return boolean
     */
    public function updateOrderId()
    {
        global $order;

        $this->_saveTransaction();

        $transactionId = $this->_xmlArray['transaction-id'];
        $orderId = $order->oID;
        $request = new Barzahlen_Request_Update($transactionId, $orderId);

        $sandbox = ZI_BARZAHLEN_SANDBOX == 'true' ? true : false;
        $api = new Barzahlen_Api(ZI_BARZAHLEN_SID, ZI_BARZAHLEN_PID, $sandbox);
        $this->_connectBarzahlen($api, $request);
    }

    /**
     * Connects to Barzahlen API.
     *
     * @param Barzahlen_Api $api
     * @param Barzahlen_Request_Payment $request
     */
    protected function _connectBarzahlen($api, $request)
    {
        try {
            $api->handleRequest($request);
        } catch (Exception $e) {
            barzahlen_log::error($e);
        }

        return $request;
    }

    /**
     * Saves transaction id to database and sets session variables for success page.
     * Furthermore the history comment for the successful payment request is set.
     */
    protected function _saveTransaction()
    {
        global $db, $order;

        $transaction_id = $this->_xmlArray['transaction-id'];
        $order_id = $order->oID;
        $amount = round($order->order_total['total']['plain'], 2);
        $currency = $order->order_data['currency_code'];

        $db->Execute("INSERT INTO " . TABLE_BARZAHLEN_TRANSACTIONS . "
                  (transaction_id, order_id, amount, currency, zi_state)
                  VALUES
                  ('" . $transaction_id . "','" . $order_id . "','" . $amount . "','" . $currency . "','TEXT_BARZAHLEN_PENDING')");

        $result = $db->Execute("SELECT orders_status_history_id FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                            WHERE orders_id = '" . $order_id . "'
                            ORDER BY orders_status_history_id DESC LIMIT 1");

        $comment = 'Barzahlen: Zahlschein versendet (Transaction-ID: ' . $transaction_id . ')';
        $status = ZI_BARZAHLEN_PENDING;
        $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . "
                      (`comments`, `orders_status_id`, `orders_id`, `callback_id`, `callback_message`)
                      VALUES ('" . $comment . "', '" . $status . "', '" . $order_id . "', '0', '');");
        $db->Execute("UPDATE " . TABLE_ORDERS . "
                  SET orders_status = '" . $status . "'
                  WHERE orders_id = '" . $order_id . "'");

        $_SESSION['infotext-1'] = $this->_xmlArray['infotext-1'];
    }

    // Barzahlen table which contains the transactions with their ID as primary key
    protected $_table = TABLE_BARZAHLEN_TRANSACTIONS; //!< database table with all transactions
    protected $_master_key = 'transaction_id'; //!< primary key of transactions table

    /**
     * With this function the position (e.g. 'admin' for admin area) is set.
     *
     * @param string $position name of the position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Defines how information of the table are shown in the backend. Since none of the information
     * should be changeable all are hidden. transaction_id as master_key is used to sort the table.
     * All buttons but the delete button are disabled and a new button is build -> refund button.
     *
     * @return array with display options
     */
    public function _getParams()
    {
        if ($this->position != 'admin') {
            return false;
        }

        $params = array();
        $header['transaction_id'] = array('type' => 'hidden');
        $header['order_id'] = array('type' => 'hidden');
        $header['amount'] = array('type' => 'hidden');
        $header['currency'] = array('type' => 'hidden');
        $header['zi_state'] = array('type' => 'hidden');
        $params['header'] = $header;

        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;
        $params['SortField'] = 'order_id';
        $params['SortDir'] = "DESC";

        $params['display_checkCol'] = false;
        $params['display_statusTrueBtn'] = false;
        $params['display_statusFalseBtn'] = false;
        $params['display_editBtn'] = false;
        $params['display_newBtn'] = false;
        $params['display_deleteBtn'] = false;

        // refund button
        $rowActions[] = array('iconCls' => 'zi_refund', 'qtipIndex' => 'qtip1', 'tooltip' => 'Rückzahlung');

        if ($this->url_data['edit_id']) {
            $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
        } else {
            $js = "var edit_id = record.id;";
        }

        $js .= "addTab('adminHandler.php?load_section=zi_refund&plugin=zi_barzahlen&transaction_id='+edit_id,'" . TEXT_ZI_REFUND . " '+edit_id, 'transaction_id'+edit_id)";

        $rowActionsFunctions['zi_refund'] = $js;

        // resend button
        $rowActions[] = array('iconCls' => 'zi_resend', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_BARZAHLEN_RESEND_PAYMENT);

        if ($this->url_data['edit_id']) {
            $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
        } else {
            $js = "var edit_id = record.id;";
        }

        $js .= "resendEmail(edit_id);";

        $rowActionsFunctions['zi_resend'] = $js;

        $js = "function resendEmail(edit_id){

             var shop_id = '" . ZI_BARZAHLEN_SID . "';
             var transaction_id = edit_id;
             var payment_key = '" . ZI_BARZAHLEN_PID . "';
             var sandbox = '" . ZI_BARZAHLEN_SANDBOX . "';

             var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'zi.resend.php',
                 method:'GET',
                 params: {'shop_id': shop_id, 'transaction_id': transaction_id, 'payment_key': payment_key, 'sandbox': sandbox},
                 success: function(responseObject) {
                           Ext.Msg.alert('', '" . TEXT_BARZAHLEN_RESEND_SUCCESS . "');
                          },
                 failure: function() {
                           Ext.Msg.alert('', '" . TEXT_BARZAHLEN_RESEND_FAILURE . "');
                          }
                 });
           }";

        $params['rowActionsJavascript'] = $js;

        // cancel button
        $rowActions[] = array('iconCls' => 'zi_cancel', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_BARZAHLEN_CANCEL_PAYMENT);

        if ($this->url_data['edit_id']) {
            $js = "var edit_id = " . $this->url_data['edit_id'] . ";";
        } else {
            $js = "var edit_id = record.id;";
        }

        $js .= "cancel(edit_id);";

        $rowActionsFunctions['zi_cancel'] = $js;

        $js = "function cancel(edit_id){

             var shop_id = '" . ZI_BARZAHLEN_SID . "';
             var transaction_id = edit_id;
             var payment_key = '" . ZI_BARZAHLEN_PID . "';
             var sandbox = '" . ZI_BARZAHLEN_SANDBOX . "';

             var conn = new Ext.data.Connection();
                 conn.request({
                 url: 'zi.cancel.php',
                 method:'GET',
                 params: {'shop_id': shop_id, 'transaction_id': transaction_id, 'payment_key': payment_key, 'sandbox': sandbox},
                 success: function(responseObject) {
                           Ext.Msg.alert('', '" . TEXT_BARZAHLEN_CANCEL_SUCCESS . "');
                          },
                 failure: function() {
                           Ext.Msg.alert('', '" . TEXT_BARZAHLEN_CANCEL_FAILURE . "');
                          }
                 });
           }";

        $params['rowActionsJavascript'] .= $js;

        $params['rowActions'] = $rowActions;
        $params['rowActionsFunctions'] = $rowActionsFunctions;

        return $params;
    }

    /**
     * Defines which information from the transactions table are requiered. Since all transactions
     * should be listed, there are no limitations. The status is shown depending of the choosen language.
     *
     * @param integer $id id of the transaction dataset which shall be get
     * @return list with all transactions
     */
    public function _get($id = 0)
    {
        global $db;

        if ($this->position != 'admin') {
            return false;
        }

        $table_data = new adminDB_DataRead($this->_table, '', '', $this->_master_key);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
            foreach ($data as $key => $array) {
                $result = $db->Execute("SELECT language_value FROM " . TABLE_LANGUAGE_CONTENT . "
                                WHERE language_code = '" . $_SESSION['selected_language'] . "'
                                AND language_key = '" . $array['zi_state'] . "'");

                $data[$key]['zi_state'] = $result->fields['language_value'];

                $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                WHERE origin_transaction_id = '" . $array['transaction_id'] . "'");
                if ($result->RecordCount() > 0 && ($array['zi_state'] == 'TEXT_ZI_REFUNDS' || $array['zi_state'] == 'TEXT_ZI_REFUND')) {
                    $data[$key]['zi_state'] = $result->RecordCount() . ' ' . $data[$key]['zi_state'];
                }
            }
        } elseif ($id) {
            $data = $table_data->getData($id);
        } else {
            $data = $table_data->getHeader();
        }

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;
        return $obj;
    }
}
