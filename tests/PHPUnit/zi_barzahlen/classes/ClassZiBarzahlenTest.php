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

require_once('src/plugins/zi_barzahlen/classes/class.zi_barzahlen.php');

class ClassZiBarzahlenTest extends PHPUnit_Framework_TestCase {

  /**
   * Set everything that is needed for the testing up.
   */
  public function setUp() {
    $this->db = new db_handler;
    $this->object = new zi_barzahlen;
    $this->object->url_data = array('edit_id' => '', 'get_data' => '');
  }

  /**
   * Test answer after a valid response.
   */
  public function testTransactionRegistrationWithValidResponse() {

    $this->object = $this->getMock('zi_barzahlen', array('_connectBarzahlen'));
    $this->object->expects($this->any())
                 ->method('_connectBarzahlen')
                 ->will($this->returnCallback('successApiCall'));

    $this->assertTrue($this->object->registerTransactionId());
  }

  /**
   * Test answer after an invalid response. (Empty array - e.g. after hash failure.)
   */
  public function testTransactionRegistrationWithInvalidResponse() {

    $xml = array();

    $this->object = $this->getMock('zi_barzahlen', array('_connectBarzahlen'));
    $this->object->expects($this->any())
                 ->method('_connectBarzahlen')
                 ->will($this->returnCallback('failureApiCall'));

    $this->assertFalse($this->object->registerTransactionId());
  }

  /**
   * Test that new positions are set correctly.
   */
  public function testPositionSetting() {
    $this->object->setPosition('admin');
    $this->assertEquals('admin', $this->object->position);
  }

  /**
   * Test the getParam method for the transaction table as user.
   */
  public function testTransactionGetParamAsUser() {

    $this->object->setPosition('user');
    $this->assertFalse($this->object->_getParams());
  }

  /**
   * Test the getParam method for the transaction table without edit id.
   */
  public function testTransactionGetParamWithoutEditId() {

    $this->object->setPosition('admin');
    $param = $this->object->_getParams();
    $this->assertEquals('hidden', $param['header']['transaction_id']['type']);
    $this->assertEquals('hidden', $param['header']['order_id']['type']);
    $this->assertEquals('hidden', $param['header']['amount']['type']);
    $this->assertEquals('hidden', $param['header']['currency']['type']);
    $this->assertEquals('hidden', $param['header']['zi_state']['type']);
    $this->assertEquals('transaction_id', $param['master_key']);
    $this->assertEquals('transaction_id', $param['default_sort']);
    $this->assertEquals('order_id', $param['SortField']);
    $this->assertEquals('DESC', $param['SortDir']);
    $this->assertFalse($param['display_checkCol']);
    $this->assertFalse($param['display_statusTrueBtn']);
    $this->assertFalse($param['display_statusFalseBtn']);
    $this->assertFalse($param['display_editBtn']);
    $this->assertFalse($param['display_newBtn']);
    $js = "var edit_id = record.id;addTab('adminHandler.php?load_section=zi_refund&plugin=zi_barzahlen&".
          "transaction_id='+edit_id,'Rückzahlung '+edit_id, 'transaction_id'+edit_id)";
    $this->assertEquals($js, $param['rowActionsFunctions']['zi_refund']);
  }

  /**
   * Test the getParam method for the transaction table with edit id.
   */
  public function testTransactionGetParamWithEditId() {

    $this->object->setPosition('admin');
    $this->object->url_data['edit_id'] = 1;
    $param = $this->object->_getParams();
    $js = "var edit_id = 1;addTab('adminHandler.php?load_section=zi_refund&plugin=zi_barzahlen&".
          "transaction_id='+edit_id,'Rückzahlung '+edit_id, 'transaction_id'+edit_id)";
    $this->assertEquals($js, $param['rowActionsFunctions']['zi_refund']);
  }

  /**
   * Test the get method for the transaction table as user.
   */
  public function testTransactionGetAsUser() {

    $this->object->setPosition('user');
    $this->assertFalse($this->object->_get(1));
  }

  /**
   * Test the get method for the transaction table without information.
   */
  public function testTransactionGetWithoutInformation() {

    $this->object->setPosition('admin');

    $obj = $this->object->_get();
    $this->assertEquals(0, $obj->totalCount);
    $this->assertEquals(array(), $obj->data);
  }

  /**
   * Test the get method for the transaction table with exact id.
   */
  public function testTransactionGetWithExactId() {

    $this->object->setPosition('admin');

    $obj = $this->object->_get(1);
    $this->assertEquals(1, $obj->totalCount);
    $result = array();
    $result[] = array('transaction_id' => '1',
                      'order_id' => '1',
                      'amount' => '24.95',
                      'currency' => 'EUR',
                      'zi_refund' => '0.00',
                      'zi_state' => 'TEXT_BARZAHLEN_PENDING'
                     );
    $this->assertEquals($result, $obj->data);
  }

  /**
   * Test the get method for the transaction table with get_data.
   */
  public function testTransactionGetWithGetData() {

    $this->object->setPosition('admin');

    $this->object->url_data['get_data'] = 1;
    $obj = $this->object->_get();
    $this->assertEquals(6, $obj->totalCount);
  }

  /**
   * Test the debug method.
   */
  public function testBarzahlenDebug() {

    require_once('src/plugins/zi_barzahlen/classes/class.log.php');
    barzahlen_log::debug('debug test');
  }

  /**
   * Unset everything before the next test.
   */
  protected function tearDown() {

    unset($this->db);
    unset($this->object);
  }
}

function successApiCall() {

  $payment = new Barzahlen_Request_Payment('foo@bar.com', '42', '24.95');
  $response = '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                  <transaction-id>16966517</transaction-id>
                  <payment-slip-link>https://paymentsb.barzahlen.de/download/4053361104830/3f35e2849f1d368c028591a4ef611b9e544d9ce49fe7050d50d9d0ca3e5aaa9e/Zahlschein_Barzahlen.pdf</payment-slip-link>
                  <expiration-notice>Ihr Zahlschein ist 14 Tage gültig. Bitte bezahlen Sie Ihre Bestellung innerhalb der nächsten 14 Tage.</expiration-notice>
                  <infotext-1>Drucken Sie diesen Zahlschein aus und nehmen Sie ihn mit zum Barzahlen-Partner in Ihrer Nähe. Den nächsten Barzahlen-Partner finden Sie unter &lt;a href="http://www.barzahlen.de/filialfinder" target="_blank"&gt;www.barzahlen.de/filialfinder&lt;/a&gt; oder unterwegs mit unserer App.&lt;br /&gt;Wir haben Ihnen den Zahlschein als PDF zusätzlich an folgende E-Mail geschickt: foo@bar.com.&lt;br /&gt;Sollte sich kein Popup mit dem Barzahlen-Zahlschein geöffnet haben, klicken Sie bitte &lt;a href="https://paymentsb.barzahlen.de/download/4053361104830/3f35e2849f1d368c028591a4ef611b9e544d9ce49fe7050d50d9d0ca3e5aaa9e/Zahlschein_Barzahlen.pdf" target="_blank"&gt;hier&lt;/a&gt; für den manuellen Download.</infotext-1>
                  <infotext-2>Wenn Sie keinen Drucker besitzen, können Sie sich den Zahlschein alternativ auf Ihr Handy schicken lassen. &lt;a href="https://f.bar-bezahlen.nl/customer/sms/4053361104830/3f35e2849f1d368c028591a4ef611b9e544d9ce49fe7050d50d9d0ca3e5aaa9e" target="_blank"&gt;Klicken Sie dazu hier.&lt;/a&gt;</infotext-2>
                  <result>0</result>
                  <hash>1c4de3e27206243acc209936cfbbce0450c85975598e60b8b20ed6fb8d817e54f987d38dd55289236564c3e8b18374335ede3ef14aab38e95bd4b2d8f4bc76b8</hash>
                </response>';
  $payment->parseXml($response, 'de74310368a4718a48e0e244fbf3e22e2ae117f2');

  return $payment;
}

function failureApiCall() {

  $payment = new Barzahlen_Request_Payment('foo@bar.com', '42', '24.95');
  return $payment;
}

?>
