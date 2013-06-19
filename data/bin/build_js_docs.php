<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates savascript API docs using JsDoc
 *
 * @package    Sift
 * @subpackage script
 */

print "Generating Sift Javascript API docs \n";

$targetDir  = realpath(dirname(__FILE__) . '/../../../sift_docs.git/js_api');

// which directories should be documented?
$dirs = array();
$dirs[] = realpath(dirname(__FILE__) . '/../../data/web/sf/js/core');
$dirs[] = realpath(dirname(__FILE__) . '/../../data/web/sf/js/file_uploader');
$dirs[] = realpath(dirname(__FILE__) . '/../../data/web/sf/js/dual_list');

$exclude = array();

// exclude minified scripts
$exclude[] = 'min.js';
$exclude[] = 'globalize.js';
$exclude[] = 'jquery.fileupload';

// THIS IS JSDOC3 version, which does not work ok for Sift!
//$jsDocCmd = "C:/DOCUME~1/michal/DATAAP~1/npm/node_modules/jsdoc/jsdoc.cmd";
//$cmd       = (sprintf('%s -d %s %s',
//                $jsDocCmd,
//                escapeshellarg($targetDir),
//                $libDir));
//
//passthru($cmd);

$jsDocDir = 'D:/data/tools/jsdoc-toolkit/';
$templateDir = $jsDocDir . '/templates/JSDoc-Bootstrap-Theme-master';

$excludes = array();
foreach($exclude as $x)
{
  $excludes[] = sprintf('--exclude="%s"', $x);
}

$cmd = sprintf('java -jar %s %s/app/run.js %s -a -t=%s -d=%s -r=2 %s',
        $jsDocDir . '/jsrun.jar',
        $jsDocDir,
        join(' ', $dirs),
        $templateDir,
        $targetDir,
        join(' ', $excludes)
);

passthru($cmd);

echo "Done.\n";