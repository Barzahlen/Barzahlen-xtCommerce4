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

            $db->Execute("INSERT INTO " . TABLE_SYSTEM_LOG . " (class, module, identification, data)
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

        $db->Execute("INSERT INTO " . TABLE_SYSTEM_LOG . " (class, module, identification, data)
                  VALUES ('error', 'zi_barzahlen', '', '$error')");
    }
}
