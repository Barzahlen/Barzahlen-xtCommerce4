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

define('_SRV_WEBROOT','src/');
define('DB_HOST','localhost');
define('DB_USER','xtcommerce');
define('DB_PASSWORD','xtcommerce');
define('DB_NAME','xtcommerce_copy');

define('TABLE_ORDERS','xt_orders');
define('TABLE_ORDERS_STATS','xt_orders_stats');
define('TABLE_BARZAHLEN_TRANSACTIONS','xt_barzahlen_transactions');
define('TABLE_BARZAHLEN_REFUNDS','xt_barzahlen_refunds');
define('TABLE_SYSTEM_LOG','xt_system_log');
define('TABLE_CALLBACK_LOG','xt_callback_log');
define('TABLE_LANGUAGE_CONTENT','xt_language_content');
define('TABLE_CONFIGURATION_MULTI','xt_config_');

define('ZI_BARZAHLEN_SANDBOX','false');
define('ZI_BARZAHLEN_DEBUG','true');
define('ZI_BARZAHLEN_SID','2');
define('ZI_BARZAHLEN_PID','a7388660329034fb1dc9350850ce18396f11ba3b');
define('ZI_BARZAHLEN_NID','177d10030b4fe66af00d4b81656c6ed6ad938f77');

define('ZI_BARZAHLEN_PENDING','11');
define('ZI_BARZAHLEN_PAID','12');
define('ZI_BARZAHLEN_EXPIRED','13');
define('ZI_BARZAHLEN_REFUND_PENDING','21');
define('ZI_BARZAHLEN_REFUND_PARTLY','22');
define('ZI_BARZAHLEN_REFUND_COMPLETE','23');
define('TEXT_ZI_REFUND','Rückzahlung');
define('TEXT_ZI_REFUNDS','Rückzahlungen');
define('TEXT_BARZAHLEN_RESEND_PAYMENT','Erneute E-Mail');
define('TEXT_BARZAHLEN_RESEND_REFUND', 'Erneute E-Mail');
define('TEXT_BARZAHLEN_RESEND_SUCCESS','Erfolg E-Mail');
define('TEXT_BARZAHLEN_RESEND_FAILURE','Fehler E-Mail');

$_SESSION['selected_language'] = 'de';

/**
 * DB-Handler
 */
class db_handler {

  /**
   * Sets up database before every test.
   */
  public function __construct() {
    exec('mysql -h' . DB_HOST . ' -u' . DB_USER . ' --password=' . DB_PASSWORD . ' ' . DB_NAME . '< tests/xtcommerce_copy.sql');

    mysql_connect('localhost',DB_USER,DB_PASSWORD);
    mysql_select_db(DB_NAME);
  }

  /**
   * Removes all database data after a test.
   */
  public function __destruct() {

    mysql_query("TRUNCATE TABLE ". TABLE_ORDERS);
    mysql_query("TRUNCATE TABLE ". TABLE_ORDERS_STATS);
    mysql_query("TRUNCATE TABLE ". TABLE_BARZAHLEN_TRANSACTIONS);
    mysql_query("TRUNCATE TABLE ". TABLE_BARZAHLEN_REFUNDS);
    mysql_query("TRUNCATE TABLE ". TABLE_SYSTEM_LOG);
    mysql_query("TRUNCATE TABLE ". TABLE_CALLBACK_LOG);
    mysql_query("TRUNCATE TABLE ". TABLE_LANGUAGE_CONTENT);
    mysql_query("TRUNCATE TABLE ". TABLE_CONFIGURATION_MULTI ."1");
    mysql_close();
  }
}

// v---------- replaced xt:Commerce classes ----------v

/**
 * Fake customer class for testing purpose.
 */
class cart {

  public $total;

  public function __construct() {
    $this->total = array('plain' => 24.95);
  }
}

/**
 * Fake customer class for testing purpose.
 */
class customer {

  public $customer_info;

  public function __construct() {
    $this->customer_info = array('customers_email_address' => 'foo@bar.com');
  }
}

/**
 * Fake order class for testing purpose.
 */
class order {

  public $oID; //!< order id
  public $cID; //!< customer id

  /**
   * Set order and customer id.
   *
   * @param integer $oID order id
   * @param integer $cID customer id
   */
  public function __construct($oID, $cID) {
    $this->oID = $oID;
    $this->cID = $cID > 0 ? $cID : 1;
  }

  /**
   * Updates order status in MySQL database.
   */
  public function _updateOrderStatus($status, $comment = '', $mail = 'true', $comment = 'true', $type = 'user', $log_id = '') {
    $update = mysql_query("UPDATE ". TABLE_ORDERS ." SET orders_status = '$status' WHERE orders_id = '".$this->oID."'");
  }
}

/**
 * Fake xtLink class for testing purpose.
 */
class xtLink {

  function __construct() {
    // do nothing
  }

  function _link($array) {
    // do nothing
  }

  function _redirect($array) {
    // do nothing
  }
}

/**
 * Fake language class for testing purpose.
 */
class language {

  public $environment_language;

  function __construct() {
    $this->environment_language = 'de';
  }
}

/**
 * Fake xtLink adminDB_DataRead for testing purpose.
 */
class adminDB_DataRead {

  protected $table; //!< working table
  protected $master_key; //!< primary key of the table

  /**
   * Sets necessary database data.
   */
  function __construct($table, $table_lang, $table_seo, $master_key) {
    $this->table = $table;
    $this->master_key = $master_key;
  }

  /**
   * Load single dataset or complete table data.
   */
  function getData($id = 0)  {

    if($id != 0) {
      $query = mysql_query("SELECT * FROM ". $this->table ." WHERE ". $this->master_key ." = '". $id ."'");
    }
    else {
      $query = mysql_query("SELECT * FROM ". $this->table);
    }

    $results = array();
    while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
      $results[] = $result;
    }

    return $results;
  }

  /**
   * Return empty array.
   */
  function getHeader()  {
    return array();
  }
}

/**
 * Fake adminDB_DataSave adminDB_DataRead for testing purpose.
 */
class adminDB_DataSave {

  protected $table; //!< working table
  protected $data; //!< send data which should be insert

  /**
   * Sets necessary database data.
   */
  function __construct($table, $data, $blubb, $bla) {
    $this->table = $table;
    $this->data = $data;
  }

  /**
   * Creates and performs mysql query.
   */
  function saveDataSet()  {

    $keys = '';
    $values = '';

    foreach ($this->data as $key => $value) {
      $keys .= $key.', ';
      $values .= '\''.$value.'\', ';
    }
    $keys = substr($keys,0,-2);
    $values = substr($values,0,-2);

    $insert = mysql_query("INSERT INTO ". $this->table ." (". $keys .") VALUES (". $values .")");
  }
}

/**
 * Fake DB object
 */
class db_object {

  public $insert_id;

  public function Execute($query) {

    if(substr($query, 0, 6) == 'SELECT') {
      return new db_result($query);
    }
    elseif(substr($query, 0, 6) == 'INSERT') {
      mysql_query($query);
      $this->insert_id = mysql_insert_id();
    }
    else {
      mysql_query($query);
    }
  }

  public function Insert_ID() {
    return $this->insert_id;
  }
}

/**
 * Fake DB result
 */
class db_result {

  public $count;
  public $fields = array();

  public function __construct($query) {

    $query = mysql_query($query);
    $this->fields = mysql_fetch_array($query);
    $this->count = mysql_num_rows($query);
  }

  public function RecordCount() {
    return $this->count;
  }
}

// v---------- faked xt:Commerce global variables ----------v

global $order;
$order = new order(1,1);

global $xtLink;
$xtLink = new xtLink;

global $db;
$db = new db_object;

global $language;
$language = new language;
?>