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
 * @author      Alexander Diebler (alexander.diebler@barzahlen.de)
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

require_once('src/plugins/zi_barzahlen/callback/class.callback.php');

class ClassCallbackTest extends PHPUnit_Framework_TestCase {

  /**
   * Set everything that is needed for the testing up.
   */
  public function setUp() {
    $this->db = new db_handler;

    $this->object = $this->getMock('callback_zi_barzahlen', array('_sendHeader'));
    $this->object->expects($this->any())
                 ->method('_sendHeader')
                 ->will($this->returnValue(''));
  }

  /**
   * Tests valid paid notification against an unpaid order.
   */
  public function testValidPaidAgainstUnpaidOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '1',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '3b77b6a9222b3c3edefe5793fbdd18da95c2973164589658a49719cb87340094af70afaa6522db76c5e36ea1d082c9ec5666e0bda78692467ab3b3a2712c257c'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PAID, $result['orders_status']);
  }

  /**
   * Tests valid paid notification against an paid order.
   */
  public function testValidExpiredAgainstPaidOrder() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '2',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '2',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '0ab5aa3eee21b222e8fb50d313c4e04482ec3bb3bfee31df4ae6df7910f54603ef56b04517149312259af63323cbd696716194957e4092e92ca6366139bee572'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '2'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PAID, $result['orders_status']);
  }

  /**
   * Tests valid expired notification against an unpaid order.
   */
  public function testValidExpiredAgainstUnpaidOrder() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '1',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'c212dfc36b0238bd32e8f751af71edc03011d999c97641f74d5ef2cec05c7da6113c61743853227080d8c06a1872d3e1487db1bbdccc2705afeb7a7c19f73a82'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_EXPIRED, $result['orders_status']);
  }

  /**
   * Tests valid refund_completed notification against a refunded order.
   */
  public function testValidRefundCompletedAgainstRefundOrder() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '4',
                  'origin_transaction_id' => '4',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '4',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '07e3ac3c6f14cd3a295319bf8eba4bfee5ad9826d95692f9c002a9eea90ec70d123062b603f2d052646b1c2a58979d25f892602732c31b468469c07eca56b384'
                 );

    $this->object->process();
    $query = mysql_query("SELECT zi_state FROM ". TABLE_BARZAHLEN_REFUNDS ." WHERE refund_transaction_id = '4'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('TEXT_BARZAHLEN_REFUND_COMPLETED', $result['zi_state']);
  }

  /**
   * Tests valid refund_expired notification against a refunded order.
   */
  public function testValidRefundExpiredAgainstRefundOrder() {

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '4',
                  'origin_transaction_id' => '4',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '4',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '296fd6921f7dff4bf61acf2574d4d09910e0d912d6b5d4d74a902d06fa44db5402fc42bd1fd6443d9f2855d419ebca962a0f2fa815839c7e0bc9e14a663122aa'
                 );

    $this->object->process();
    $query = mysql_query("SELECT zi_state FROM ". TABLE_BARZAHLEN_REFUNDS ." WHERE refund_transaction_id = '4'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('TEXT_BARZAHLEN_REFUND_EXPIRED', $result['zi_state']);
  }

  /**
   * Tests valid refund_completed notification against a expired refund.
   */
  public function testValidRefundCompletedAgainstExpiredRefund() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '5',
                  'origin_transaction_id' => '5',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '5.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '5',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '651108744543d866d1ade6b2dce75368f2f25d1ccdf288f59ce6b51c9b217c7d8bfd2eeb9bb8dbbbed39d1d2f069aa884fe5c56521937b701f62ebee4137da5c'
                 );

    $this->object->process();
    $query = mysql_query("SELECT zi_state FROM ". TABLE_BARZAHLEN_REFUNDS ." WHERE refund_transaction_id = '5'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('TEXT_BARZAHLEN_REFUND_EXPIRED', $result['zi_state']);
  }

  /**
   * Tests valid refund_completed notification against a twice refunded order.
   */
  public function testValidRefundCompletedAgainstTwiceRefundOrder() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '61',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '14.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '59593e698953d9abb0db1041cab09f9d0d55d315f022013c2a5071cca44be5582b7636ccb8dbf5c4c1f2d9a92c985c644759881161b61c1f102fe4b4c00c302e'
                 );

    $this->object->process();
    $query = mysql_query("SELECT zi_state FROM ". TABLE_BARZAHLEN_REFUNDS ." WHERE refund_transaction_id = '61'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('TEXT_BARZAHLEN_REFUND_COMPLETED', $result['zi_state']);
  }

  /**
   * Tests valid refund_expired notification against a twice refunded order.
   */
  public function testValidRefundExpiredAgainstTwiceRefundOrder() {

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '62',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '10.00',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'b31ba23433cf54bbb518b579c556a2ff4f5e95c0646aac896e6ff3be5752bc28be1e433fa7b2329445ac480b39e8c41ad362294d950f88eb62ec82c08b45e536'
                 );

    $this->object->process();

    $query = mysql_query("SELECT zi_state FROM ". TABLE_BARZAHLEN_REFUNDS ." WHERE refund_transaction_id = '62'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('TEXT_BARZAHLEN_REFUND_EXPIRED', $result['zi_state']);
  }

  /**
   * Tests valid refund_expired and refund_completed notification against a twice refunded order.
   */
  public function testValidRefundExpiredAndCompletedAgainstTwiceRefundOrder() {

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '62',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '10.00',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'b31ba23433cf54bbb518b579c556a2ff4f5e95c0646aac896e6ff3be5752bc28be1e433fa7b2329445ac480b39e8c41ad362294d950f88eb62ec82c08b45e536'
                 );

    $this->object->process();

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '61',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '14.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '59593e698953d9abb0db1041cab09f9d0d55d315f022013c2a5071cca44be5582b7636ccb8dbf5c4c1f2d9a92c985c644759881161b61c1f102fe4b4c00c302e'
                   );

    $this->object->process();

    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '6'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('22', $result['orders_status']);
  }

  /**
   * Tests valid refund_completed and refund_expired notification against a twice refunded order.
   */
  public function testValidRefundCompletedAndExpiredAgainstTwiceRefundOrder() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '61',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '14.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '59593e698953d9abb0db1041cab09f9d0d55d315f022013c2a5071cca44be5582b7636ccb8dbf5c4c1f2d9a92c985c644759881161b61c1f102fe4b4c00c302e'
                   );

    $this->object->process();

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '62',
                  'origin_transaction_id' => '6',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '10.00',
                  'currency' => 'EUR',
                  'origin_order_id' => '6',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'b31ba23433cf54bbb518b579c556a2ff4f5e95c0646aac896e6ff3be5752bc28be1e433fa7b2329445ac480b39e8c41ad362294d950f88eb62ec82c08b45e536'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '6'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals('22', $result['orders_status']);
  }

  /**
   * Tests valid paid notification against an already paid order.
   */
  public function testValidPaidAgainstAlreadyPaidOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '1',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '2',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'b5f28680ed6e60dd31848393e189bce26ffe70a7626bc1af20b165225f2f55eeb93e918ffc7a930dbcb3dfd59a29e8547f32cd835a084875d5a532169021ce37'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '2'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PAID, $result['orders_status']);
  }

  /**
   * Tests invalid expired notification against an unpaid order. (Transaction ID not correct.)
   */
  public function testInvalidExpiredAgainstUnpaidOrder() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '2',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'ab7c94c214232cf1c23c48e8ae40a0e780c489b904426c431dfdd1fe1931652516c3ad122b4747ba78e8e0455a9f35f502827587c4f943caf66e8fbc65544b08'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PENDING, $result['orders_status']);
  }

  /**
   * Tests invalid paid notification against an unpaid order. (Invalid Hash.)
   */
  public function testInvalidPaidAgainstUnpaidOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '1',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '3b77b6a9222b3c3edefe5793fbdd18da95c2973164589658a49719cb87340094af70afaa6522db76c5e36ea1d082c9ec5666e0bda78692467ab3b3a2712c257d'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PENDING, $result['orders_status']);
  }

  /**
   * Tests incomplete expired notification against an unpaid order. (Shop ID is missing.)
   */
  public function testIncompleteExpiredAgainstUnpaidOrder() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => 'f0ab893dfd1790b686f522d9c08c94f9c68d1e60173a09ca916f960abc42df294c281a40a071dcbd93f9acef841572392392cb3e02e8dca6e11f95fa956dd115'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PENDING, $result['orders_status']);
  }

  /**
   * Tests invalid state notification against an unpaid order.
   */
  public function testInvalidStateAgainstUnpaidOrder() {

    $_GET = array('state' => 'paid_expired',
                  'transaction_id' => '1',
                  'shop_id' => '2',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '24.95',
                  'currency' => 'EUR',
                  'order_id' => '1',
                  'custom_var_0' => '',
                  'custom_var_1' => '',
                  'custom_var_2' => '',
                  'hash' => '19db202b487a0221f16371eb59c4200bea261eb3d245f857f54faa8e373ddd10b4bcfdc4147a3f646a080d5e3e75cb7702b32353232c1cb4e1940e913e6236e5'
                 );

    $this->object->process();
    $query = mysql_query("SELECT orders_status FROM ". TABLE_ORDERS ." WHERE orders_id = '1'");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);

    $this->assertEquals(ZI_BARZAHLEN_PENDING, $result['orders_status']);
  }

  /**
   * Unset everything before the next test.
   */
  protected function tearDown() {

    unset($this->db);
    unset($this->object);
  }
}
?>
