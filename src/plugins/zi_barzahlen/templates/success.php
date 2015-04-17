<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

global $xtLink;
$orderId = $_SESSION['success_order_id'];
$customerId = $_SESSION['customer']->customers_id;
$lastOrder = new order($orderId, $customerId);
$paymentCode = $lastOrder->order_data['payment_code'];

if ($paymentCode === 'zi_barzahlen') {
    if (isset($_SESSION['infotext-1'])) {
        echo $_SESSION['infotext-1'];
        unset($_SESSION['infotext-1']);
    } else {
        $xtLink->_redirect('index.php?page=customer&page_action=order_info&oid=' . $orderId);
    }
}
