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

require_once('src/plugins/zi_barzahlen/classes/class.zi_refund.php');

class ClassZiRefundTest extends PHPUnit_Framework_TestCase {

  /**
   * Set everything that is needed for the testing up.
   */
  public function setUp() {
    $this->db = new db_handler;
    $this->object = new zi_refund;
    $this->object->url_data = array('edit_id' => '', 'get_data' => '');
    $this->object->table_lang = '';
    $this->object->table_seo = '';


    $_GET['new'] = '';
    $_GET['transaction_id'] = '';
  }

  /**
   * Test that new positions are set correctly.
   */
  public function testPositionSetting() {
    $this->object->setPosition('admin');
    $this->assertEquals('admin', $this->object->position);
  }

  /**
   * Test the getParam method should fail as non-admin.
   */
  public function testRefundGetParamAsUser() {

    $this->object->setPosition('user');
    $this->assertFalse($this->object->_getParams());
  }

  /**
   * Test the getParam method for the refund table with $_GET['new'] unset.
   */
  public function testRefundGetParamAsAdminWithoutGetNew() {

    $this->object->setPosition('admin');
    $_GET['transaction_id'] = '2';

    $param = $this->object->_getParams();
    $this->assertEquals(array('transaction_id','initial','already'), $param['exclude']);
    $this->assertEquals('true', $param['header']['origin_transaction_id']['disabled']);
    $this->assertEquals('2', $param['header']['origin_transaction_id']['value']);
    $this->assertEquals('hidden', $param['header']['refund_transaction_id']['type']);
    $this->assertEquals('hidden', $param['header']['zi_state']['type']);
    $this->assertEquals('refund_transaction_id', $param['master_key']);
    $this->assertEquals('refund_transaction_id', $param['default_sort']);
    $this->assertEquals('refund_transaction_id', $param['SortField']);
    $this->assertEquals('DESC', $param['SortDir']);
    $this->assertFalse($param['display_checkCol']);
    $this->assertFalse($param['display_statusTrueBtn']);
    $this->assertFalse($param['display_statusFalseBtn']);
    $this->assertFalse($param['display_editBtn']);
    $this->assertTrue($param['display_newBtn']);
  }

  /**
   * Test the getParam method for the refund table with with $_GET['new'] = 'true'.
   */
  public function testRefundGetParamAsAdminWithGetNew() {

    $this->object->setPosition('admin');
    $_GET['transaction_id'] = '2';

    $_GET['new'] = 'true';
    $param = $this->object->_getParams();
    $this->assertEquals('true', $param['header']['origin_transaction_id']['disabled']);
    $this->assertEquals('2', $param['header']['origin_transaction_id']['value']);
    $this->assertEquals('hidden', $param['header']['refund_transaction_id']['type']);
    $this->assertEquals('hidden', $param['header']['zi_state']['type']);
    $this->assertEquals('Rückzahlung (max. 24.95)', $param['header']['amount']['text']);
    $this->assertEquals('true', $param['header']['initial']['disabled']);
    $this->assertEquals('24.95', $param['header']['initial']['value']);
    $this->assertEquals('true', $param['header']['already']['disabled']);
    $this->assertEquals('0.00', $param['header']['already']['value']);
    $this->assertEquals('refund_transaction_id', $param['master_key']);
    $this->assertEquals('refund_transaction_id', $param['default_sort']);
    $this->assertEquals('refund_transaction_id', $param['SortField']);
    $this->assertEquals('DESC', $param['SortDir']);
    $this->assertFalse($param['display_checkCol']);
    $this->assertFalse($param['display_statusTrueBtn']);
    $this->assertFalse($param['display_statusFalseBtn']);
    $this->assertTrue($param['display_editBtn']);
    $this->assertTrue($param['display_newBtn']);
  }

  /**
   * Test the getParam method for the refund table with edit id.
   */
  public function testRefundGetParamAsAdminWithEditId() {

    $this->object->setPosition('admin');
    $this->object->url_data['edit_id'] = 1;
    $param = $this->object->_getParams();
    $this->assertNotEquals(null, $param['rowActionsFunctions']['zi_resend']);
  }

  /**
   * Test the get method which should fail as non-admin.
   */
  public function testRefundGetAsUser() {

    $this->object->setPosition('user');
    $this->assertFalse($this->object->_get(1));
  }

  /**
   * Test the get method for the refund table with arguement 'new'.
   */
  public function testRefundGetAsNewRefund() {

    $this->object->setPosition('admin');

    $obj = $this->object->_get('new');
    $this->assertEquals(1, $obj->totalCount);
    $result = array();
    $result[] = array('initial' => '',
                      'already' => ''
                     );
    $this->assertEquals($result, $obj->data);
  }

  /**
   * Test the get method for the refund table without further information.
   */
  public function testRefundGetWithoutFurtherInformation() {

    $this->object->setPosition('admin');

    $obj = $this->object->_get();
    $this->assertEquals(1, $obj->totalCount);
    $result = array();
    $result[] = array('initial' => '',
                      'already' => ''
                     );
    $this->assertEquals($result, $obj->data);
  }

  /**
   * Test the get method for the refund table with exact id.
   */
  public function testRefundGetWithExactId() {

    $this->object->setPosition('admin');

    $obj = $this->object->_get(4);
    $this->assertEquals(1, $obj->totalCount);
    $result = array();
    $result[] = array('refund_transaction_id' => '4',
                      'origin_transaction_id' => '4',
                      'amount' => '24.95',
                      'zi_state' => 'TEXT_BARZAHLEN_REFUND_PENDING'
                     );
    $this->assertEquals($result, $obj->data);
  }

  /**
   * Test the get method for the refund table with get_data.
   */
  public function testRefundGetWithGetData() {

    $this->object->setPosition('admin');

    $this->object->url_data['get_data'] = 1;
    $obj = $this->object->_get();
    $this->assertEquals(4, $obj->totalCount);
  }

  /**
   * Test set method with valid data and expects database changes.
   */
  public function testSetRefundIdWithValidData() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => 24.95);

    $this->object = $this->getMock('zi_refund', array('_connectBarzahlen'));
    $this->object->expects($this->any())
                 ->method('_connectBarzahlen')
                 ->will($this->returnCallback('successApiCallRefund'));

    $this->object->_set($data);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_TRANSACTIONS ." WHERE transaction_id = 2");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);
    $this->assertEquals(24.95, $result['zi_refund']);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_REFUNDS ."
                          WHERE origin_transaction_id = '2' AND refund_transaction_id = '123'
                          AND amount = '24.95' AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");
    $this->assertEquals(1, mysql_num_rows($query));
  }

  /**
   * Test set method with valid data and error response.
   */
  public function testSetRefundIdWithValidDataButErrorResponse() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => 24.95);

    $this->object = $this->getMock('zi_refund', array('_connectBarzahlen'));
    $this->object->expects($this->any())
                 ->method('_connectBarzahlen')
                 ->will($this->returnCallback('failureApiCallRefund'));

    $this->object->_set($data);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_TRANSACTIONS ." WHERE transaction_id = 2");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);
    $this->assertEquals(0, $result['zi_refund']);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_REFUNDS ."
                          WHERE origin_transaction_id = '2' AND refund_transaction_id = '123'
                          AND amount = '24.95' AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");
    $this->assertEquals(0, mysql_num_rows($query));
  }

  /**
   * Test set method with valid data and wrong origin transaction id.
   */
  public function testSetRefundIdWithValidDataWithWrongOriginId() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => 24.95);

    $this->object = $this->getMock('zi_refund', array('_connectBarzahlen'));
    $this->object->expects($this->any())
                 ->method('_connectBarzahlen')
                 ->will($this->returnCallback('wrongIdApiCallRefund'));

    $this->object->_set($data);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_TRANSACTIONS ." WHERE transaction_id = 2");
    $result = mysql_fetch_array($query, MYSQL_ASSOC);
    $this->assertEquals(0, $result['zi_refund']);

    $query = mysql_query("SELECT * FROM ". TABLE_BARZAHLEN_REFUNDS ."
                          WHERE origin_transaction_id = '2' AND refund_transaction_id = '123'
                          AND amount = '24.95' AND zi_state = 'TEXT_BARZAHLEN_REFUND_PENDING'");
    $this->assertEquals(0, mysql_num_rows($query));
  }

  /**
   * Test set method with valid data against a pending transaction.
   */
  public function testSetRefundIdWithValidDataAgainstPendingTransaction() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '1');
    $data = array('amount' => 10);

    $this->assertFalse($this->object->_set($data));
  }

  /**
   * Test set method with an invalid amount. (Too high amount.)
   */
  public function testSetRefundIdWithInvalidAmount1() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => 30);

    $this->assertFalse($this->object->_set($data));
  }

  /**
   * Test set method with an invalid amount. (Amount is zero.)
   */
  public function testSetRefundIdWithInvalidAmount2() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => 0);

    $this->assertFalse($this->object->_set($data));
  }

  /**
   * Test set method with an invalid amount. (Amount is no numeric value.)
   */
  public function testSetRefundIdWithInvalidAmount3() {

    $this->object->setPosition('admin');
    $_GET = array('transaction_id' => '2');
    $data = array('amount' => '2zwei');

    $this->assertFalse($this->object->_set($data));
  }

  /**
   * Unset everything before the next test.
   */
  protected function tearDown() {

    unset($this->db);
    unset($this->object);
  }
}

function successApiCallRefund() {

  $refund = new Barzahlen_Request_Refund('2', '24.95');
  $response = '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                <origin-transaction-id>2</origin-transaction-id>
                <refund-transaction-id>123</refund-transaction-id>
                <result>0</result>
                <hash>58c75b0f1baa8e064df07bb3f8023d4dc666615c9cc4463642c795838ff6afd012a81fa7520cd5021b58ffd71c817923ecd64d15ac5b2b2e9045d3ec6fc201c8</hash>
              </response>';
  $refund->parseXml($response, 'de74310368a4718a48e0e244fbf3e22e2ae117f2');

  return $refund;
}

function wrongIdApiCallRefund() {

  $refund = new Barzahlen_Request_Refund('2', '24.95');
  $response = '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                <origin-transaction-id>1</origin-transaction-id>
                <refund-transaction-id>123</refund-transaction-id>
                <result>0</result>
                <hash>17212056d3711f79655931380bc076c28ad5875f5491d2fc0bca3a006c2d18addc4880142b23228a34cf0b1b26401a9306297ff2e4caec74a3cb7bf0b46ff8da</hash>
              </response>';
  $refund->parseXml($response, 'de74310368a4718a48e0e244fbf3e22e2ae117f2');

  return $refund;
}

function failureApiCallRefund() {

  $payment = new Barzahlen_Request_Payment('foo@bar.com', '42', '24.95');
  return $payment;
}

?>