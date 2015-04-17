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

class zi_refund
{
    // Barzahlen table which contains the refund with their ID as primary key
    protected $_table = TABLE_BARZAHLEN_REFUNDS; //!< database table with all refunds
    protected $_master_key = 'refund_transaction_id'; //!< primary key for refund table

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
     * should be changeable all are hidden. refund_id as master_key is used to sort the table.
     * All buttons but the delete button are disabled and a edit button is only shown when creating
     * new refunds to enable the save button. For a new refund the initial amount as well as the
     * already refunded money is displayed.
     *
     * @return array with display options
     */
    public function _getParams()
    {
        global $db;

        if ($this->position != 'admin') {
            return false;
        }

        $transaction_id = filter_var($_GET['transaction_id'], FILTER_SANITIZE_NUMBER_INT);
        $result = $db->Execute("SELECT amount, currency, zi_refund FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                            WHERE transaction_id = '$transaction_id'");

        $params = array();
        $header['origin_transaction_id'] = array('disabled' => 'true', 'value' => $transaction_id);
        $header['refund_transaction_id'] = array('type' => 'hidden');
        $header['zi_state'] = array('type' => 'hidden');
        if ($_GET['new'] != 'true') {
            $params['exclude'] = array('transaction_id', 'initial', 'already');
        } else {
            $header['amount'] = array('text' => TEXT_ZI_REFUND . ' (max. ' . ($result->fields['amount'] - $result->fields['zi_refund']) . ')');
            $header['initial'] = array('disabled' => 'true', 'value' => $result->fields['amount']);
            $header['already'] = array('disabled' => 'true', 'value' => $result->fields['zi_refund']);
        }
        $params['header'] = $header;

        $params['master_key'] = $this->_master_key;
        $params['default_sort'] = $this->_master_key;
        $params['SortField'] = $this->_master_key;
        $params['SortDir'] = "DESC";

        $params['display_checkCol'] = false;
        $params['display_statusTrueBtn'] = false;
        $params['display_statusFalseBtn'] = false;
        if ($_GET['new'] == 'true') {
            $params['display_editBtn'] = true;
        } else {
            $params['display_editBtn'] = false;
        }
        $params['display_newBtn'] = true;
        $params['display_deleteBtn'] = false;

        // resend button -> only while listing refunds, not on new refund form
        if ($_GET['new'] != 'true') {
            $rowActions[] = array('iconCls' => 'zi_resend', 'qtipIndex' => 'qtip1', 'tooltip' => TEXT_BARZAHLEN_RESEND_REFUND);

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
            $params['rowActions'] = $rowActions;
            $params['rowActionsFunctions'] = $rowActionsFunctions;
        }

        return $params;
    }

    /**
     * Defines which information from the refund table are requiered. All refunds from the choosen
     * transaction should be listed. The status is shown depending of the choosen language.
     *
     * @param integer $id id of the refund dataset which shall be get
     * @return list with all refund which are related to the choosen transaction
     */
    public function _get($id = 0)
    {
        global $db;

        if ($this->position != 'admin') {
            return false;
        }

        if ($id === 'new') {
            $obj = $this->_set(array(), 'new');
            $id = $obj->new_id;
        }

        $transaction_id = filter_var($_GET['transaction_id'], FILTER_SANITIZE_NUMBER_INT);
        $sql_where = 'origin_transaction_id = ' . $transaction_id;
        $table_data = new adminDB_DataRead($this->_table, $this->table_lang, $this->table_seo, $this->_master_key, $sql_where);

        if ($this->url_data['get_data']) {
            $data = $table_data->getData();
            foreach ($data as $key => $array) {
                $result = $db->Execute("SELECT language_value FROM " . TABLE_LANGUAGE_CONTENT . "
                                WHERE language_code = '" . $_SESSION['selected_language'] . "'
                                AND language_key = '" . $array['zi_state'] . "'");

                $data[$key]['zi_state'] = $result->fields['language_value'];
            }
        } elseif ($id) {
            $data = $table_data->getData($id);
        } else {
            $data = $table_data->getHeader();
            $data[0]['initial'] = '';
            $data[0]['already'] = '';
        }

        $obj = new stdClass;
        $obj->totalCount = count($data);
        $obj->data = $data;
        return $obj;
    }

    /**
     * This functions tests the entered amount for the choosen transaction and sends the curl request
     * to the Barzahlen API to start the refund process. For this purpose an new refund object is
     * created and with the helper the request is send, the answer xml is parsed and the hash
     * confirmed. When everything is ok, the new refund is written to the database and the
     * transaction status is updated.
     *
     * @param array $data array with the information which the user entered
     * @param string $set_type type of set request (can be 'edit' or 'new')
     * @return refund dataset if everything is fine
     * @return FALSE if an error occurres (e.g. invalid hash)
     */
    public function _set($data, $set_type = 'new')
    {
        $transaction_id = filter_var($_GET['transaction_id'], FILTER_SANITIZE_NUMBER_INT);

        if (!isset($data['amount'])) {
            $data['amount'] = 0;
            $obj = new stdClass;
            $obj->new_id = 0;
            return $obj;
        }

        if (!$this->_refundableState($transaction_id) || !$this->_checkRefundAmount($transaction_id, $data['amount'])) {
            return false;
        }

        $refund_id = $this->_registerRefundId($transaction_id, $data['amount']);
        if ($refund_id === false) {
            return false;
        }

        $data['zi_state'] = 'TEXT_BARZAHLEN_REFUND_PENDING';
        $data['origin_transaction_id'] = $transaction_id;
        $data['refund_transaction_id'] = $refund_id;

        $this->_updateDatabase($data);

        $obj = new stdClass;
        $o = new adminDB_DataSave($this->_table, $data, false, __CLASS__);
        $obj = $o->saveDataSet();

        return $obj;
    }

    /**
     * Checks if the transaction can be refunded.
     *
     * @param integer $transaction_id id of transaction which shall be refunded
     * @return FALSE if transaction state is pending or expired
     * @return TURE if transaction is refundable
     */
    protected function _refundableState($transaction_id)
    {
        global $db;

        $result = $db->Execute("SELECT zi_state FROM " . TABLE_BARZAHLEN_TRANSACTIONS . " WHERE transaction_id = '" . $transaction_id . "'");

        if ($result->fields['zi_state'] == 'TEXT_BARZAHLEN_PENDING' || $result->fields['zi_state'] == 'TEXT_BARZAHLEN_EXPIRED') {
            barzahlen_log::error('class/refund: unable to refund pending or expired transactions', array($transaction_id));
            return false;
        }

        return true;
    }

    /**
     * Checks if the amount for this refund is valid.
     *
     * @param integer $transaction_id id of transaction which shall be refunded
     * @param string $amount amount that shall be refunded
     * @return FALSE if amount is too high, smaller 0 or a non-numeric value
     * @return TURE if amount is ok
     */
    protected function _checkRefundAmount($transaction_id, $amount)
    {
        global $db;

        $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_TRANSACTIONS . " WHERE transaction_id = '" . $transaction_id . "'");

        if (round($result->fields['amount'] - $result->fields['zi_refund'] - $amount, 2) < 0) {
            barzahlen_log::error('class/refund: requested refund amount too high', array($transaction_id, $amount));
            return false;
        }

        if ($amount <= 0) {
            barzahlen_log::error('class/refund: requested refund amount equal or smaller 0', array($transaction_id, $amount));
            return false;
        }

        if (!is_numeric($amount)) {
            barzahlen_log::error('class/refund: requested refund amount is no numeric value', array($transaction_id, $amount));
            return false;
        }

        return true;
    }

    /**
     * Requests refund transaction id from Barzahlen.
     *
     * @param integer $transaction_id id of transaction which shall be refunded
     * @param string $amount amount that shall be refunded
     * @return FALSE if on error occurred (can be read in system log)
     * @return refund transaction id if everything went well
     */
    protected function _registerRefundId($transaction_id, $amount)
    {
        global $db;

        $result = $db->Execute("SELECT currency FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                            WHERE transaction_id = '" . $transaction_id . "'");

        $sandbox = ZI_BARZAHLEN_SANDBOX == 'true' ? true : false;
        $api = new Barzahlen_Api(ZI_BARZAHLEN_SID, ZI_BARZAHLEN_PID, $sandbox);
        $api->setUserAgent('xt:Commerce 4.2 / Plugin v1.2.0');
        $request = new Barzahlen_Request_Refund($transaction_id, $amount, $result->fields['currency']);

        $refund = $this->_connectBarzahlen($api, $request);

        if (!$refund->isValid()) {
            return false;
        }

        if ($refund->getOriginTransactionId() != $transaction_id) {
            barzahlen_log::error('class/refund: origin transaction id not equal transaction id', array($transaction_id, $amount, $refund->getXmlArray()));
            return false;
        }

        return $refund->getRefundTransactionId();
    }

    /**
     * Connects to Barzahlen API.
     *
     * @param Barzahlen_Api $api
     * @param Barzahlen_Request_Refund $request
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
     * Updates order and transaction table.
     *
     * @param array $data refund data
     */
    protected function _updateDatabase($data)
    {
        global $db;

        $result = $db->Execute("SELECT order_id, zi_refund FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                            WHERE transaction_id = '" . $data['origin_transaction_id'] . "'");

        $zi_refund = $result->fields['zi_refund'];
        $order_id = $result->fields['order_id'];

        $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                            WHERE origin_transaction_id = '" . $data['origin_transaction_id'] . "'");
        $zi_state = $result->RecordCount() > 0 ? 'TEXT_ZI_REFUNDS' : 'TEXT_ZI_REFUND';

        $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                  SET zi_refund = '" . ($zi_refund + $data['amount']) . "', zi_state = '$zi_state'
                  WHERE transaction_id = '" . $data['origin_transaction_id'] . "'");

        $status = ZI_BARZAHLEN_REFUND_PENDING;

        $order = new order($order_id, -1);
        $order->_updateOrderStatus($status, 'Barzahlen: Rückzahlung beantragt (Refund-ID: ' . $data['refund_transaction_id'] . ')', 'false', 'false', 'admin');
    }
}
