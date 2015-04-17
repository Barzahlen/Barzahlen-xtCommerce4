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

require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.zi_barzahlen.php';
require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.log.php';
require_once dirname(__FILE__) . '/api/version_check.php';

class barzahlen_check
{
    /**
     * Connects to the Barzahlen and check if a newer version is available.
     *
     * @return null
     */
    public static function checkVersion()
    {
        if (strpos($_SERVER['REQUEST_URI'], 'dashboard.php') === false) {
            return;
        }

        if (file_exists(_SRV_WEBROOT . 'cache/barzahlen.check')) {
            $file = fopen(_SRV_WEBROOT . 'cache/barzahlen.check', 'r');
            $lastCheck = fread($file, 1024);
            fclose($file);
        } else {
            $lastCheck = 0;
        }

        if ($lastCheck < strtotime("-1 week")) {

            $file = fopen(_SRV_WEBROOT . 'cache/barzahlen.check', 'w');
            fwrite($file, time());
            fclose($file);

            try {
                $plugin = new zi_barzahlen;
                $checker = new Barzahlen_Version_Check();
                $newAvailable = $checker->isNewVersionAvailable(ZI_BARZAHLEN_SID, 'xt:Commerce 4', 'xt:Commerce 4.2', $plugin->version);
            } catch (Exception $e) {
                barzahlen_log::error($e);
            }

            if($newAvailable) {
                echo '<script type="javascript">
                          if (confirm("' . sprintf(TEXT_BARZAHLEN_PLUGIN_UPDATE, (string) $checker->getNewPluginVersion()) . '")) {
                              window.location.href = "' . $checker->getNewPluginUrl() . '";
                          }
                          </script>';
            }
        }
    }
}
