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