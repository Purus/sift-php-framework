<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/sfCacheDriverTests.class.php');

$t = new lime_test(129, new lime_output_color());

try
{
  $cache = new sfFileCache();
  $t->fail('->__construct() throws an RuntimeException exception if you don\'t pass a "cache_dir" parameter');
}
catch (RuntimeException $e)
{
  $t->pass('->__construct() throws an RuntimeException exception if you don\'t pass a "cache_dir" parameter');
}

// setup
sfConfig::set('sf_logging_enabled', false);
$temp = tempnam(sys_get_temp_dir(), 'tmp');
unlink($temp);
mkdir($temp);
$cache = new sfFileCache(array('cache_dir' => $temp));


sfCacheDriverTests::launch($t, $cache);

// teardown
sfToolkit::clearDirectory($temp);
rmdir($temp);

$temp = tempnam('/tmp/cachedir', 'tmp');
unlink($temp);
mkdir($temp);

// Create a file cache using initialize() method
$cache = new sfFileCache(array('cache_dir' => $temp));

sfCacheDriverTests::launch($t, $cache);

// teardown
sfToolkit::clearDirectory($temp);
rmdir($temp);


