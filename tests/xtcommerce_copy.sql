-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. April 2012 um 09:20
-- Server Version: 5.1.44
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `xtc_copy`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_barzahlen_refunds`
--

CREATE TABLE IF NOT EXISTS `xt_barzahlen_refunds` (
  `refund_transaction_id` int(11) unsigned NOT NULL,
  `origin_transaction_id` int(11) unsigned NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `zi_state` varchar(50) NOT NULL,
  PRIMARY KEY (`refund_transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `xt_barzahlen_refunds`
--

INSERT IGNORE INTO `xt_barzahlen_refunds` (`refund_transaction_id`, `origin_transaction_id`, `amount`, `zi_state`) VALUES
(4, 4, 24.95, 'TEXT_BARZAHLEN_REFUND_PENDING'),
(5, 5, 5.95, 'TEXT_BARZAHLEN_REFUND_EXPIRED'),
(61, 6, 14.95, 'TEXT_BARZAHLEN_REFUND_PENDING'),
(62, 6, 10.00, 'TEXT_BARZAHLEN_REFUND_PENDING');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_barzahlen_transactions`
--

CREATE TABLE IF NOT EXISTS `xt_barzahlen_transactions` (
  `transaction_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `zi_refund` decimal(6,2) NOT NULL,
  `zi_state` varchar(50) NOT NULL,
  PRIMARY KEY (`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `xt_barzahlen_transactions`
--

INSERT IGNORE INTO `xt_barzahlen_transactions` (`transaction_id`, `order_id`, `amount`, `currency`, `zi_refund`, `zi_state`) VALUES
(1, 1, 24.95, 'EUR', 0.00, 'TEXT_BARZAHLEN_PENDING'),
(2, 2, 24.95, 'EUR', 0.00, 'TEXT_ZI_PAID'),
(3, 3, 24.95, 'EUR', 0.00, 'TEXT_ZI_EXPIRED'),
(4, 4, 24.95, 'EUR', 24.95, 'TEXT_BARZAHLEN_REFUND_PENDING'),
(5, 5, 24.95, 'EUR', 0.00, 'TEXT_BARZAHLEN_REFUND_EXPIRED'),
(6, 6, 24.95, 'EUR', 24.95, 'TEXT_ZI_REFUNDS');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_callback_log`
--

CREATE TABLE IF NOT EXISTS `xt_callback_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(64) NOT NULL,
  `orders_id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `callback_data` longtext NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `class` varchar(32) NOT NULL,
  `error_msg` varchar(255) NOT NULL,
  `error_data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `xt_callback_log`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_config_1`
--

CREATE TABLE IF NOT EXISTS `xt_config_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(64) NOT NULL,
  `config_value` text NOT NULL,
  `group_id` int(11) NOT NULL,
  `sort_order` int(5) DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_configuration_group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `xt_config_1`
--

INSERT IGNORE INTO `xt_config_1` (`id`, `config_key`, `config_value`, `group_id`, `sort_order`, `last_modified`, `date_added`, `type`, `url`) VALUES
(1, '_STORE_NAME', 'xt:Commerce 4.0', 1, 1, NULL, '0000-00-00 00:00:00', NULL, NULL),
(2, '_STORE_COUNTRY', 'DE', 1, 6, NULL, '0000-00-00 00:00:00', 'dropdown', 'countries'),
(3, '_STORE_DEFAULT_TEMPLATE', 'xt_default', 1, 26, NULL, '0000-00-00 00:00:00', 'dropdown', 'templateSets'),
(4, '_STORE_CURRENCY', 'EUR', 1, 0, NULL, '0000-00-00 00:00:00', 'dropdown', 'currencies'),
(5, '_STORE_LANGUAGE', 'de', 1, 0, NULL, '0000-00-00 00:00:00', 'dropdown', 'language_codes');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_language_content`
--

CREATE TABLE IF NOT EXISTS `xt_language_content` (
  `language_content_id` int(11) NOT NULL AUTO_INCREMENT,
  `translated` int(1) NOT NULL DEFAULT '1',
  `language_code` char(2) DEFAULT NULL,
  `language_key` varchar(255) DEFAULT NULL,
  `language_value` text,
  `class` varchar(32) NOT NULL DEFAULT 'store',
  `plugin_key` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`language_content_id`),
  UNIQUE KEY `language_code` (`language_code`,`language_key`,`class`),
  KEY `language_code_2` (`language_code`,`class`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6283 ;

--
-- Daten für Tabelle `xt_language_content`
--

INSERT IGNORE INTO `xt_language_content` (`language_content_id`, `translated`, `language_code`, `language_key`, `language_value`, `class`, `plugin_key`) VALUES
(6282, 1, 'en', 'ZI_BARZAHLEN_REFUND_COMPLETE_TITLE', 'Complete Refunded', 'admin', 'zi_barzahlen'),
(6281, 1, 'de', 'ZI_BARZAHLEN_REFUND_COMPLETE_TITLE', 'Komplett-Rückzahlung abgeschlossen', 'admin', 'zi_barzahlen'),
(6280, 1, 'en', 'ZI_BARZAHLEN_REFUND_PARTLY_TITLE', 'Partly Refunded', 'admin', 'zi_barzahlen'),
(6279, 1, 'de', 'ZI_BARZAHLEN_REFUND_PARTLY_TITLE', 'Teil-Rückzahlung abgeschlossen', 'admin', 'zi_barzahlen'),
(6278, 1, 'en', 'ZI_BARZAHLEN_REFUND_PENDING_TITLE', 'Refund Pending', 'admin', 'zi_barzahlen'),
(6277, 1, 'de', 'ZI_BARZAHLEN_REFUND_PENDING_TITLE', 'Rückzahlung ausstehend', 'admin', 'zi_barzahlen'),
(6275, 1, 'de', 'ZI_BARZAHLEN_EXPIRED_TITLE', 'Abgelaufen', 'admin', 'zi_barzahlen'),
(6276, 1, 'en', 'ZI_BARZAHLEN_EXPIRED_TITLE', 'Expired', 'admin', 'zi_barzahlen'),
(6274, 1, 'en', 'ZI_BARZAHLEN_PAID_TITLE', 'Paid', 'admin', 'zi_barzahlen'),
(6272, 1, 'en', 'ZI_BARZAHLEN_PENDING_TITLE', 'Payment Pending', 'admin', 'zi_barzahlen'),
(6273, 1, 'de', 'ZI_BARZAHLEN_PAID_TITLE', 'Bezahlt', 'admin', 'zi_barzahlen'),
(6271, 1, 'de', 'ZI_BARZAHLEN_PENDING_TITLE', 'Zahlung ausstehend', 'admin', 'zi_barzahlen'),
(6270, 1, 'en', 'ZI_BARZAHLEN_VAR2_TITLE', 'Custom Variable 3', 'admin', 'zi_barzahlen'),
(6269, 1, 'de', 'ZI_BARZAHLEN_VAR2_TITLE', 'Benutzerdefinierte Variable 3', 'admin', 'zi_barzahlen'),
(6268, 1, 'en', 'ZI_BARZAHLEN_VAR1_TITLE', 'Custom Variable 2', 'admin', 'zi_barzahlen'),
(6267, 1, 'de', 'ZI_BARZAHLEN_VAR1_TITLE', 'Benutzerdefinierte Variable 2', 'admin', 'zi_barzahlen'),
(6265, 1, 'de', 'ZI_BARZAHLEN_VAR0_TITLE', 'Benutzerdefinierte Variable 1', 'admin', 'zi_barzahlen'),
(6266, 1, 'en', 'ZI_BARZAHLEN_VAR0_TITLE', 'Custom Variable 1', 'admin', 'zi_barzahlen'),
(6264, 1, 'en', 'ZI_BARZAHLEN_NID_TITLE', 'Notification Key', 'admin', 'zi_barzahlen'),
(6263, 1, 'de', 'ZI_BARZAHLEN_NID_TITLE', 'Benachrichtigungsschlüssel', 'admin', 'zi_barzahlen'),
(6261, 1, 'de', 'ZI_BARZAHLEN_PID_TITLE', 'Zahlungsschlüssel', 'admin', 'zi_barzahlen'),
(6262, 1, 'en', 'ZI_BARZAHLEN_PID_TITLE', 'Payment Key', 'admin', 'zi_barzahlen'),
(6260, 1, 'en', 'ZI_BARZAHLEN_SID_TITLE', 'Shop ID', 'admin', 'zi_barzahlen'),
(6259, 1, 'de', 'ZI_BARZAHLEN_SID_TITLE', 'Shop ID', 'admin', 'zi_barzahlen'),
(6258, 1, 'en', 'ZI_BARZAHLEN_SANDBOX_TITLE', 'Testmode', 'admin', 'zi_barzahlen'),
(6257, 1, 'de', 'ZI_BARZAHLEN_SANDBOX_TITLE', 'Testmodus', 'admin', 'zi_barzahlen'),
(6256, 1, 'en', 'TEXT_BARZAHLEN_REFUND_EXPIRED', 'Refund Expired', 'admin', 'zi_barzahlen'),
(6255, 1, 'de', 'TEXT_BARZAHLEN_REFUND_EXPIRED', 'Rückzahlung abgelaufen', 'admin', 'zi_barzahlen'),
(6254, 1, 'en', 'TEXT_BARZAHLEN_REFUND_COMPLETED', 'Refund Completed', 'admin', 'zi_barzahlen'),
(6253, 1, 'de', 'TEXT_BARZAHLEN_REFUND_COMPLETED', 'Rückzahlung abgeschlossen', 'admin', 'zi_barzahlen'),
(6252, 1, 'en', 'TEXT_BARZAHLEN_REFUND_PENDING', 'Refund Pending', 'admin', 'zi_barzahlen'),
(6251, 1, 'de', 'TEXT_BARZAHLEN_REFUND_PENDING', 'Rückzahlung in Bearbeitung', 'admin', 'zi_barzahlen'),
(6250, 1, 'en', 'TEXT_BARZAHLEN_EXPIRED', 'Expired', 'admin', 'zi_barzahlen'),
(6248, 1, 'en', 'TEXT_BARZAHLEN_PAID', 'Paid', 'admin', 'zi_barzahlen'),
(6249, 1, 'de', 'TEXT_BARZAHLEN_EXPIRED', 'Abgelaufen', 'admin', 'zi_barzahlen'),
(6247, 1, 'de', 'TEXT_BARZAHLEN_PAID', 'Zahlung erhalten', 'admin', 'zi_barzahlen'),
(6246, 1, 'en', 'TEXT_BARZAHLEN_PENDING', 'Payment Pending', 'admin', 'zi_barzahlen'),
(6245, 1, 'de', 'TEXT_BARZAHLEN_PENDING', 'Zahlung ausstehend', 'admin', 'zi_barzahlen'),
(6244, 1, 'en', 'TEXT_ALREADY', 'Already Refunded', 'admin', 'zi_barzahlen'),
(6243, 1, 'de', 'TEXT_ALREADY', 'Bereits zurückgezahlt', 'admin', 'zi_barzahlen'),
(6242, 1, 'en', 'TEXT_INITIAL', 'Initial Amount', 'admin', 'zi_barzahlen'),
(6241, 1, 'de', 'TEXT_INITIAL', 'Ursprungsbetrag', 'admin', 'zi_barzahlen'),
(6229, 1, 'de', 'TEXT_ZI_BARZAHLEN', 'Bar zahlen', 'admin', 'zi_barzahlen'),
(6230, 1, 'en', 'TEXT_ZI_BARZAHLEN', 'Bar zahlen', 'admin', 'zi_barzahlen'),
(6231, 1, 'de', 'TEXT_ZI_REFUND', 'Rückzahlung', 'admin', 'zi_barzahlen'),
(6232, 1, 'en', 'TEXT_ZI_REFUND', 'Refund', 'admin', 'zi_barzahlen'),
(6233, 1, 'de', 'TEXT_ZI_REFUNDS', 'Rückzahlungen', 'admin', 'zi_barzahlen'),
(6234, 1, 'en', 'TEXT_ZI_REFUNDS', 'Refunds', 'admin', 'zi_barzahlen'),
(6235, 1, 'de', 'TEXT_REFUND_TRANSACTION_ID', 'Rückzahlungs ID', 'admin', 'zi_barzahlen'),
(6236, 1, 'en', 'TEXT_REFUND_TRANSACTION_ID', 'Refund ID', 'admin', 'zi_barzahlen'),
(6237, 1, 'de', 'TEXT_ORIGIN_TRANSACTION_ID', 'Transaction ID', 'admin', 'zi_barzahlen'),
(6238, 1, 'en', 'TEXT_ORIGIN_TRANSACTION_ID', 'Transaction ID', 'admin', 'zi_barzahlen'),
(6239, 1, 'de', 'TEXT_ZI_STATE', 'Bar zahlen Status', 'admin', 'zi_barzahlen'),
(6240, 1, 'en', 'TEXT_ZI_STATE', 'Bar zahlen Status', 'admin', 'zi_barzahlen');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_orders`
--

CREATE TABLE IF NOT EXISTS `xt_orders` (
  `orders_id` int(11) NOT NULL AUTO_INCREMENT,
  `customers_id` int(11) NOT NULL,
  `customers_cid` varchar(32) DEFAULT NULL,
  `customers_vat_id` varchar(20) DEFAULT NULL,
  `customers_status` int(11) DEFAULT NULL,
  `customers_email_address` varchar(96) DEFAULT NULL,
  `delivery_gender` varchar(32) DEFAULT NULL,
  `delivery_phone` varchar(32) DEFAULT NULL,
  `delivery_fax` varchar(23) DEFAULT NULL,
  `delivery_firstname` varchar(64) NOT NULL,
  `delivery_lastname` varchar(64) NOT NULL,
  `delivery_company` varchar(64) DEFAULT NULL,
  `delivery_company_2` varchar(64) DEFAULT NULL,
  `delivery_company_3` varchar(64) DEFAULT NULL,
  `delivery_street_address` varchar(64) NOT NULL,
  `delivery_suburb` varchar(32) DEFAULT NULL,
  `delivery_city` varchar(32) NOT NULL,
  `delivery_postcode` varchar(10) NOT NULL,
  `delivery_zone` varchar(32) DEFAULT NULL,
  `delivery_zone_code` int(11) DEFAULT NULL,
  `delivery_country` varchar(32) NOT NULL,
  `delivery_country_code` char(2) NOT NULL,
  `delivery_address_book_id` int(11) DEFAULT NULL,
  `billing_gender` varchar(32) DEFAULT NULL,
  `billing_phone` varchar(32) DEFAULT NULL,
  `billing_fax` varchar(23) DEFAULT NULL,
  `billing_firstname` varchar(64) NOT NULL,
  `billing_lastname` varchar(64) NOT NULL,
  `billing_company` varchar(64) DEFAULT NULL,
  `billing_company_2` varchar(64) DEFAULT NULL,
  `billing_company_3` varchar(64) DEFAULT NULL,
  `billing_street_address` varchar(64) NOT NULL,
  `billing_suburb` varchar(32) DEFAULT NULL,
  `billing_city` varchar(32) NOT NULL,
  `billing_postcode` varchar(10) NOT NULL,
  `billing_zone` varchar(32) DEFAULT NULL,
  `billing_zone_code` int(11) DEFAULT NULL,
  `billing_country` varchar(32) NOT NULL,
  `billing_country_code` char(2) NOT NULL,
  `billing_address_book_id` int(11) DEFAULT NULL,
  `payment_code` varchar(64) DEFAULT NULL,
  `subpayment_code` varchar(32) DEFAULT NULL,
  `shipping_code` varchar(64) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `currency_value` decimal(15,4) DEFAULT NULL,
  `language_code` char(2) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_purchased` datetime DEFAULT NULL,
  `orders_status` int(5) DEFAULT NULL,
  `orders_date_finished` datetime DEFAULT NULL,
  `account_type` int(1) DEFAULT '0',
  `allow_tax` tinyint(1) DEFAULT NULL,
  `customers_ip` varchar(32) DEFAULT NULL,
  `shop_id` int(11) NOT NULL DEFAULT '1',
  `orders_data` longtext,
  `campaign_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orders_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `xt_orders`
--

INSERT IGNORE INTO `xt_orders` (`orders_id`, `customers_id`, `customers_cid`, `customers_vat_id`, `customers_status`, `customers_email_address`, `delivery_gender`, `delivery_phone`, `delivery_fax`, `delivery_firstname`, `delivery_lastname`, `delivery_company`, `delivery_company_2`, `delivery_company_3`, `delivery_street_address`, `delivery_suburb`, `delivery_city`, `delivery_postcode`, `delivery_zone`, `delivery_zone_code`, `delivery_country`, `delivery_country_code`, `delivery_address_book_id`, `billing_gender`, `billing_phone`, `billing_fax`, `billing_firstname`, `billing_lastname`, `billing_company`, `billing_company_2`, `billing_company_3`, `billing_street_address`, `billing_suburb`, `billing_city`, `billing_postcode`, `billing_zone`, `billing_zone_code`, `billing_country`, `billing_country_code`, `billing_address_book_id`, `payment_code`, `subpayment_code`, `shipping_code`, `currency_code`, `currency_value`, `language_code`, `comments`, `last_modified`, `date_purchased`, `orders_status`, `orders_date_finished`, `account_type`, `allow_tax`, `customers_ip`, `shop_id`, `orders_data`, `campaign_id`) VALUES
(1, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, '2012-04-24 09:27:28', NULL, 11, NULL, 0, NULL, NULL, 1, NULL, 0),
(2, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, NULL, NULL, 12, NULL, 0, NULL, NULL, 1, NULL, 0),
(3, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, NULL, NULL, 13, NULL, 0, NULL, NULL, 1, NULL, 0),
(4, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, '2012-04-24 09:27:28', NULL, 21, NULL, 0, NULL, NULL, 1, NULL, 0),
(5, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, '2012-04-24 09:27:28', NULL, 23, NULL, 0, NULL, NULL, 1, NULL, 0),
(6, 1, NULL, NULL, 2, 'mustermann@barzahlen.de', NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, NULL, NULL, NULL, 'Max', 'Mustermann', NULL, NULL, NULL, 'Musterstr. 1a', NULL, 'Musterhausen', '12345', NULL, NULL, 'Deutschland', 'DE', NULL, 'zi_barzahlen', NULL, NULL, 'EUR', 1.0000, 'de', NULL, '2012-04-24 09:27:28', NULL, 21, NULL, 0, NULL, NULL, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_orders_stats`
--

CREATE TABLE IF NOT EXISTS `xt_orders_stats` (
  `orders_id` int(11) NOT NULL,
  `orders_stats_price` decimal(15,4) DEFAULT NULL,
  `products_count` int(11) DEFAULT NULL,
  PRIMARY KEY (`orders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `xt_orders_stats`
--

INSERT IGNORE INTO `xt_orders_stats` (`orders_id`, `orders_stats_price`, `products_count`) VALUES
(1, 24.9500, 1),
(2, 24.9500, 1),
(3, 24.9500, 1),
(4, 24.9500, 1),
(5, 24.9500, 1),
(6, 24.9500, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `xt_system_log`
--

CREATE TABLE IF NOT EXISTS `xt_system_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(32) NOT NULL,
  `module` varchar(64) NOT NULL,
  `identification` int(11) NOT NULL,
  `data` longtext NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FULLTEXT KEY `class` (`class`,`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `xt_system_log`
--

