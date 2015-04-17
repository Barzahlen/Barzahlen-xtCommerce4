<?php
/**
 * Barzahlen Payment Module (xt:Commerce 4)
 *
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

require_once '../xtCore/main.php';
require_once _SRV_WEBROOT . 'plugins/zi_barzahlen/classes/class.resend.php';

if(barzahlen_resend::tryResend() == true) {
  header("HTTP/1.1 200 OK");
  header("Status: 200 OK");
}
else {
  header("HTTP/1.1 400 Bad Request");
  header("Status: 400 Bad Request");
}
?>