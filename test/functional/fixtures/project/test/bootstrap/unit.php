<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
$_test_dir = realpath(dirname(__FILE__).'/..');
define('SF_ROOT_DIR', realpath($_test_dir.'/..'));

include(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

require_once($sf_sift_lib_dir.'/vendor/lime/lime.php');
