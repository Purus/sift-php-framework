<?php
/**
 * Do the tests prove the plugin ##PLUGIN_NAME## works?
 * 
 * @package     ##PLUGIN_NAME##
 * @subpackage  test
 * @author      ##AUTHOR_NAME##
 */
 
// load bootstrap for unit tests
include dirname(__FILE__).'/../bootstrap/unit.php';

$h = new lime_harness(new lime_output_color());
$h->register(sfFinder::type('file')->name('*Test.php')->in(dirname(__FILE__).'/..'));

exit($h->run() ? 0 : 1);
