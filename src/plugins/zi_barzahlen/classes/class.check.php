<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
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
