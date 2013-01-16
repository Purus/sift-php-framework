<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Updates sfCoreAutoloader
 *
 * Usage: php data/bin/update_core_autoloader.php
 *
 * @package    Sift
 * @subpackage cli
 */
require_once dirname(__FILE__).'/../../lib/autoload/sfCoreAutoload.class.php';

echo "Updating autoloader\n";
sfCoreAutoload::make();
echo "Done.";
