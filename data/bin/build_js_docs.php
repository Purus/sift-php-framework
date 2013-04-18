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
$libDir     = realpath(dirname(__FILE__) . '/../../data/web/sf/js/core');

// THIS IS JSDOC3 version, which does not work ok for Sift!
//$jsDocCmd = "C:/DOCUME~1/michal/DATAAP~1/npm/node_modules/jsdoc/jsdoc.cmd";
//$cmd       = (sprintf('%s -d %s %s',
//                $jsDocCmd,
//                escapeshellarg($targetDir),
//                $libDir));
//
//passthru($cmd);

$jsDocDir = 'D:/data/tools/jsdoc-toolkit/';
// $templateDir = $jsDocDir . '/templates/OrgaChem-JsDoc2-Template-Bootstrap';
$templateDir = $jsDocDir . '/templates/JSDoc-Bootstrap-Theme-master';

$cwd = getcwd();

chdir(dirname($libDir));

$cmd = sprintf('java -jar %s %s/app/run.js %s -a -t=%s -d=%s -r=2 --exclude="min.js" --exclude="globalize.js"',
        $jsDocDir . '/jsrun.jar',
        $jsDocDir,
        basename($libDir),
        $templateDir,
        $targetDir);

passthru($cmd);
chdir($cwd);
echo "Done.\n";