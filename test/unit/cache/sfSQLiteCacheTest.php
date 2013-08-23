<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/sfCacheDriverTests.class.php');

$plan = 129;
$t = new lime_test($plan);

if (!extension_loaded('SQLite') && !extension_loaded('pdo_SQLite')) 
{
  $t->skip('SQLite extension not loaded, skipping tests', $plan);
  return;
}

try
{
  new sfSQLiteCache(array('database' => ':memory:'));
}
catch (sfInitializationException $e)
{
  $t->skip($e->getMessage(), $plan);
  return;
}

// ->initialize()
$t->diag('->initialize()');
try
{
  $cache = new sfSQLiteCache();
  $t->fail('->initialize() throws an RuntimeException exception if you don\'t pass a "database" parameter');
}
catch (RuntimeException $e)
{
  $t->pass('->initialize() throws an RuntimeException exception if you don\'t pass a "database" parameter');
}

// database in memory
$cache = new sfSQLiteCache(array('database' => ':memory:'));

sfCacheDriverTests::launch($t, $cache);

// database on disk
$database = tempnam(sys_get_temp_dir(), 'tmp');
unlink($database);

$cache = new sfSQLiteCache(array('database' => $database));
sfCacheDriverTests::launch($t, $cache);

$cache->__destruct();
unset($cache);

@unlink($database);
