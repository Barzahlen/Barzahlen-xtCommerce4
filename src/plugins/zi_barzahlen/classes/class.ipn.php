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

require_once dirname(__FILE__) . '/api/loader.php';

class barzahlen_ipn
{
    const PAYMENTSTATE_PAID = 'paid';
    const PAYMENTSTATE_EXPIRED = 'expired';
    const PAYMENTSTATE_REFUND_COMPLETED = 'refund_completed';
    const PAYMENTSTATE_REFUND_EXPIRED = 'refund_expired';

    protected $_receivedData = array(); //!< received data from the server
    protected $_barzahlen; //!< Barzahlen base model
    protected $_orderId; //!< corresponding order

    /**
     * Checks received data and validates hash.
     *
     * @param array $uncleanData received data
     * @return TRUE if received get array is valid and hash could be confirmed
     * @return FALSE if an error occurred
     */
    public function sendResponseHeader(array $receivedData)
    {
        $this->_receivedData = $receivedData;
        $notification = new Barzahlen_Notification(ZI_BARZAHLEN_SID, ZI_BARZAHLEN_NID, $receivedData);

        try {
            $notification->validate();
        } catch (Exception $e) {
            $this->_addErrorLog($e);
        }

        return $notification->isValid();
    }

    /**
     * Parent function to update the database with all information.
     */
    public function updateDatabase()
    {
        if (array_key_exists('order_id', $this->_receivedData)) {
            $this->_orderId = $this->_receivedData['order_id'];
        } elseif (array_key_exists('origin_order_id', $this->_receivedData)) {
            $this->_orderId = $this->_receivedData['origin_order_id'];
        } else {
            $this->_orderId = 0;
        }

        if ($this->_checkOrderInformation() && $this->_canChangeState()) {
            if ($this->_handleStateChange()) {
                $this->_updateOrderState();
            }
        }
    }

    /**
     * Checks that there's an valid order id for the requested order.
     *
     * @return TRUE if order id was found and validated
     * @return FALSE if order id was not found or could not be validated
     */
    protected function _checkOrderInformation()
    {
        global $db;

        if (array_key_exists('refund_transaction_id', $this->_receivedData)) {
            $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                    WHERE refund_transaction_id = '" . $this->_receivedData['refund_transaction_id'] . "'
                                    AND origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");

            if ($result->RecordCount() != 1) {
                $this->_addErrorLog('model/ipn: refund stats not correct');
                return false;
            }
            if ($this->_orderId == 0) {
                $result = $db->Execute("SELECT order_id FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                                        WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");

                $this->_orderId = $result->fields['order_id'];
            }
        } else {
            $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                                    WHERE transaction_id = '" . $this->_receivedData['transaction_id'] . "'");

            if ($result->RecordCount() != 1) {
                $this->_addErrorLog('model/ipn: transaction stats not correct');
                return false;
            }
            if ($this->_orderId != 0) {
                if ($this->_orderId != $result->fields['order_id']) {
                    $this->_addErrorLog('model/ipn: transaction stats not correct');
                    return false;
                }
            } else {
                $this->_orderId = $result->fields['order_id'];
            }
        }

        return true;
    }

    /**
     * Checks if transaction / refund is changeable. (Only pending ones are.)
     */
    protected function _canChangeState()
    {
        global $db;

        if (array_key_exists('refund_transaction_id', $this->_receivedData)) {
            $result = $db->Execute("SELECT zi_state FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                    WHERE refund_transaction_id = '" . $this->_receivedData['refund_transaction_id'] . "'");

            if ($result->fields['zi_state'] != 'TEXT_BARZAHLEN_REFUND_PENDING') {
                $this->_addErrorLog('model/ipn: unable to change state of the refund', $this->_receivedData);
                return false;
            }
        } else {
            $result = $db->Execute("SELECT orders_status FROM " . TABLE_ORDERS . "
                                    WHERE orders_id = '" . $this->_orderId . "'");

            if ($result->fields['orders_status'] != ZI_BARZAHLEN_PENDING) {
                $this->_addErrorLog('model/ipn: unable to change state of the order', $this->_receivedData);
                return false;
            }
        }

        return true;
    }

    /**
     * Calls the necessary method for the send state.
     */
    protected function _handleStateChange()
    {
        switch ($this->_receivedData['state']) {
            case self::PAYMENTSTATE_PAID:
                $this->_processTransactionPaid();
                return true;
            case self::PAYMENTSTATE_EXPIRED:
                $this->_processTransactionExpired();
                return true;
            case self::PAYMENTSTATE_REFUND_COMPLETED:
                $this->_processRefundCompleted();
                return true;
            case self::PAYMENTSTATE_REFUND_EXPIRED:
                $this->_processRefundExpired();
                return true;
            default:
                $this->_addErrorLog('controller/ipn: Cannot handle payment state', $this->_receivedData);
                return false;
        }
    }

    /**
     * Sets transaction state to paid.
     */
    protected function _processTransactionPaid()
    {
        global $db;

        // set transaction state to paid
        $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                      SET zi_state = 'TEXT_BARZAHLEN_PAID'
                      WHERE transaction_id = '" . $this->_receivedData['transaction_id'] . "'");
    }

    /**
     * Sets transaction state to expired.
     */
    protected function _processTransactionExpired()
    {
        global $db;

        // set transaction state to expired
        $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                      SET zi_state = 'TEXT_BARZAHLEN_EXPIRED'
                      WHERE transaction_id = '" . $this->_receivedData['transaction_id'] . "'");
    }

    /**
     * Sets refund state to completed. Checks if transaction update is necessary.
     */
    protected function _processRefundCompleted()
    {
        global $db;

        // set refund state to completed
        $db->Execute("UPDATE " . TABLE_BARZAHLEN_REFUNDS . "
                      SET zi_state = 'TEXT_BARZAHLEN_REFUND_COMPLETED'
                      WHERE origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'
                      AND refund_transaction_id = '" . $this->_receivedData['refund_transaction_id'] . "'");

        // entire amount was refunded and there are no more pending refunds ?
        // 2 times yes -> set transaction to complete
        $result1 = $db->Execute("SELECT amount, zi_refund FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                             WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");


        $result2 = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                 WHERE origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'
                                 AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");

        if ($result2->RecordCount() == 0 && $result1->fields['zi_refund'] == $result1->fields['amount']) {
            $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                        SET zi_state = 'TEXT_BARZAHLEN_REFUND_COMPLETED'
                        WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");
        }
    }

    /**
     * Sets refund state to expired. Reduce refunded amount.
     */
    protected function _processRefundExpired()
    {
        global $db;

        // set refund state to expired
        $db->Execute("UPDATE " . TABLE_BARZAHLEN_REFUNDS . "
                      SET zi_state = 'TEXT_BARZAHLEN_REFUND_EXPIRED'
                      WHERE origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'
                      AND refund_transaction_id = '" . $this->_receivedData['refund_transaction_id'] . "'");

        // reduce refunded amount
        $db->Execute("UPDATE " . TABLE_BARZAHLEN_TRANSACTIONS . "
                      SET zi_refund = zi_refund - '" . $this->_receivedData['amount'] . "'
                      WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");
    }

    /**
     * Updates status of the order.
     */
    protected function _updateOrderState()
    {
        $status = $this->_getNewStatus();
        $message = $this->_getIpnComment();
        $id = $this->_getIdMessage();
        $log_id = $this->_addSuccessLog();

        $order = new order($this->_orderId, -1);
        $order->_updateOrderStatus($status, 'Barzahlen: ' . $message . ' ' . $id, 'false', 'false', 'IPN', $log_id);
    }

    /**
     * Gets status id for new status.
     *
     * @return string with order status id
     */
    protected function _getNewStatus()
    {
        global $db;

        switch ($this->_receivedData['state']) {

            // transaction was paid -> order: paid
            case self::PAYMENTSTATE_PAID:
                return ZI_BARZAHLEN_PAID;

            // transaction expired -> order: canceled
            case self::PAYMENTSTATE_EXPIRED:
                return ZI_BARZAHLEN_EXPIRED;

            // refund completed
            case self::PAYMENTSTATE_REFUND_COMPLETED:
                // any other pending refunds ? yes -> order: refund pending
                $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                        WHERE origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'
                                        AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");
                if ($result->RecordCount() > 0) {
                    return ZI_BARZAHLEN_REFUND_PENDING;
                }
                // complete amount was refunded completely ? yes -> order: refund complete
                $result = $db->Execute("SELECT amount, zi_refund FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                                        WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");

                if ($result->fields['amount'] == $result->fields['zi_refund']) {
                    return ZI_BARZAHLEN_REFUND_COMPLETE;
                }
                // else -> order: refund partly
                return ZI_BARZAHLEN_REFUND_PARTLY;

            // refund expired
            case self::PAYMENTSTATE_REFUND_EXPIRED:
                // any other pending refunds ? yes -> order: refund pending
                $result = $db->Execute("SELECT * FROM " . TABLE_BARZAHLEN_REFUNDS . "
                                        WHERE origin_transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'
                                        AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");
                if ($result->RecordCount() > 0) {
                    return ZI_BARZAHLEN_REFUND_PENDING;
                }
                // was there any successful refunded money before ? yes -> order: refund partly
                $result = $db->Execute("SELECT zi_refund FROM " . TABLE_BARZAHLEN_TRANSACTIONS . "
                                        WHERE transaction_id = '" . $this->_receivedData['origin_transaction_id'] . "'");

                if ($result->fields['zi_refund'] > 0) {
                    return ZI_BARZAHLEN_REFUND_PARTLY;
                }
                // else -> order: paid
                return ZI_BARZAHLEN_PAID;
        }
    }

    /**
     * Checks store language and select right message for the order update comment.
     *
     * @return string with ipn comment
     */
    protected function _getIpnComment()
    {
        global $db;

        // get shop id
        $result = $db->Execute("SELECT shop_id FROM " . TABLE_ORDERS . "
                                WHERE orders_id = '" . $this->_orderId . "'");


        // get store language via shop id
        $result = $db->Execute("SELECT config_value FROM " . TABLE_CONFIGURATION_MULTI . $result->fields['shop_id'] . "
                                WHERE config_key = '_STORE_LANGUAGE'");


        // get ipn comment via store language
        $result = $db->Execute("SELECT language_value FROM " . TABLE_LANGUAGE_CONTENT . "
                                WHERE language_code = '" . $result->fields['config_value'] . "'
                                AND language_key = 'TEXT_BARZAHLEN_" . strtoupper($this->_receivedData['state']) . "'");


        return $result->fields['language_value'];
    }

    /**
     * Checks if refund_transaction_id is set, if yes -> refund, else transaction.
     *
     * @return string with refund or transaction id
     */
    protected function _getIdMessage()
    {
        if (array_key_exists('refund_transaction_id', $this->_receivedData)) {
            return '(Refund-ID: ' . $this->_receivedData['refund_transaction_id'] . ')';
        }
        return '(Transaction-ID: ' . $this->_receivedData['transaction_id'] . ')';
    }

    /**
     * This function is called, when an error occurred.
     *
     * @param string $error_msg explantion what kind of error occurred
     */
    protected function _addErrorLog($error_msg)
    {
        global $db;

        $transactionId = isset($this->_receivedData['transaction_id']) ? $this->_receivedData['transaction_id'] : $this->_receivedData['origin_transaction_id'];
        $module = 'zi_barzahlen';
        $class = 'error';
        $error_data = serialize($this->_receivedData);

        $db->Execute("INSERT INTO " . TABLE_CALLBACK_LOG . " (module, orders_id, transaction_id, class, error_msg, error_data)
                      VALUES ('$module','$this->_orderId','$transactionId','$class','$error_msg','$error_data')");
    }

    /**
     * This function is called, when a notifications was successfully verified and the status update
     * is about to happen.
     */
    protected function _addSuccessLog()
    {
        global $db;

        $transactionId = isset($this->_receivedData['transaction_id']) ? $this->_receivedData['transaction_id'] : $this->_receivedData['origin_transaction_id'];
        $module = 'zi_barzahlen';
        $class = 'success';
        $callback_data = serialize($this->_receivedData);

        $db->Execute("INSERT INTO " . TABLE_CALLBACK_LOG . " (module, orders_id, transaction_id, class, callback_data)
                      VALUES ('$module','$this->_orderId','$transactionId','$class','$callback_data')");

        return $db->Insert_ID();
    }
}
