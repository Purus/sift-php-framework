<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/lib/sfSimpleErrorPage.class.php';
$error = new sfSimpleErrorPage('error500');
$error->render();
