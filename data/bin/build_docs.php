<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates API docs using Apigen
 *
 * @package    Sift
 * @subpackage script
 */

print "Generating Sift API docs \n";

$targetDir  = realpath(dirname(__FILE__) . '/../../../sift_docs.git/api');
$libDir     = realpath(dirname(__FILE__) . '/../../lib');
$excludeDir = realpath(dirname(__FILE__) . '/../../lib/vendor') . '/*';
$title      = 'API docs ~ Sift PHP framework';

passthru(sprintf('apigen --source %s --exclude %s --title %s --destination %s',
                escapeshellarg($libDir), escapeshellarg($excludeDir),
                escapeshellarg($title), escapeshellarg($targetDir)));
