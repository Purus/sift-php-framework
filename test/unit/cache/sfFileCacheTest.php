<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/sfCacheDriverTests.class.php');

$t = new lime_test(36, new lime_output_color());

// setup
sfConfig::set('sf_logging_enabled', false);
$temp = tempnam('/tmp/cachedir', 'tmp');
unlink($temp);
mkdir($temp);
$cache = new sfFileCache($temp);

sfCacheDriverTests::launch($t, $cache);

// teardown
sfToolkit::clearDirectory($temp);
rmdir($temp);

$temp = tempnam('/tmp/cachedir', 'tmp');
unlink($temp);
mkdir($temp);

// Create a file cache using initialize() method
$cache = new sfFileCache();
$cache->initialize(array('cacheDir' => $temp));

sfCacheDriverTests::launch($t, $cache);

// teardown
sfToolkit::clearDirectory($temp);
rmdir($temp);

$temp = tempnam('/tmp/cachedir', 'tmp');
unlink($temp);
mkdir($temp);
