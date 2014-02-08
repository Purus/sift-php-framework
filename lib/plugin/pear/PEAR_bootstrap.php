<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loads all dependencies from PEAR, also removed ugly E_DEPRECATED and E_STRICT notices
 * which PEAR produces
 *
 * @package    Sift
 * @subpackage plugin_pear
 */

// remove PEAR deprecated notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
date_default_timezone_set('UTC');

require_once 'PEAR.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Downloader.php';
require_once 'PEAR/Frontend.php';
require_once 'PEAR/Frontend/CLI.php';
require_once 'PEAR/Registry.php';
require_once 'PEAR/Command.php';
require_once 'PEAR/PackageFile/v2/rw.php';
require_once 'PEAR/Dependency2.php';
require_once 'PEAR/Installer.php';
require_once 'PEAR/Packager.php';

// load rest
require_once 'PEAR/REST.php';
require_once 'PEAR/REST/10.php';
require_once 'PEAR/REST/11.php';
require_once 'PEAR/REST/13.php';
