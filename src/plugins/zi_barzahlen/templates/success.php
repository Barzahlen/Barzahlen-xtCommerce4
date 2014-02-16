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

global $xtLink;
$orderId = $_SESSION['success_order_id'];
$customerId = $_SESSION['customer']->customers_id;
$lastOrder = new order($orderId, $customerId);
$paymentCode = $lastOrder->order_data['payment_code'];

if($paymentCode === 'zi_barzahlen') {

  if(isset($_SESSION['infotext-1'])){
    echo $_SESSION['infotext-1'];
    unset($_SESSION['infotext-1']);
  }

  else {
    $xtLink->_redirect('index.php?page=customer&page_action=order_info&oid=' . $orderId);
  }
}
?>