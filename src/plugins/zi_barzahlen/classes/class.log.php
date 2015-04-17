<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

class barzahlen_log
{
    /**
     * Logs more than just errors as long as debugging is enabled in the backend.
     *
     * @param string $debug_msg text about what was done
     * @param array $debug_data corresponding data
     */
    public static function debug($debug_msg, array $debug_data = array())
    {
        global $db;

        if (ZI_BARZAHLEN_DEBUG == 'true') {

            $debug = $debug_msg . " # " . serialize($debug_data);

            $db->Execute("INSERT INTO " . TABLE_SYSTEM_LOG . " (class, message_source, identification, data)
                  VALUES ('debug', 'zi_barzahlen', '', '$debug')");
        }
    }

    /**
     * Logs errors to the given log file.
     *
     * @param string $error_msg explaination of the occurred error
     * @param array $error_data corresponding data
     */
    public static function error($error_msg, array $error_data = array())
    {
        global $db;

        $error = $error_msg . " # " . serialize($error_data);

        $db->Execute("INSERT INTO " . TABLE_SYSTEM_LOG . " (class, message_source, identification, data)
                  VALUES ('error', 'zi_barzahlen', '', '$error')");
    }
}
