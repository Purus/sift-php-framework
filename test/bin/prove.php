<?php

require_once(dirname(__FILE__).'/../../lib/vendor/lime/lime.php');

$h = new lime_harness(new lime_output_color());

$h->base_dir = realpath(dirname(__FILE__).'/..');

// cache autoload files
require_once($h->base_dir.'/bootstrap/unit.php');
testAutoloader::initialize(true);

// unit tests
$h->register_glob($h->base_dir.'/unit/*/*Test.php');

// functional tests
$h->register_glob($h->base_dir.'/functional/*Test.php');
$h->register_glob($h->base_dir.'/functional/*/*Test.php');

// other tests
$h->register_glob($h->base_dir.'/other/*Test.php');

$ret = $h->run();

testAutoloader::removeCache();

exit($ret ? 0 : 1);
