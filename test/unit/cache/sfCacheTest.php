<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class myCache extends sfCache
{
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {}
  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {}
  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data, $lifetime = null) {}
  public function remove($id, $namespace = self::DEFAULT_NAMESPACE) {}
  public function clean($namespace = null, $mode = 'all') {}
  public function getLastModified($id, $namespace = self::DEFAULT_NAMESPACE) {}
}

$t = new lime_test(2, new lime_output_color());

$cache = new myCache();

// ->getLifeTime() ->setLifeTime()
$t->diag('->getLifeTime() ->setLifeTime()');
$t->is($cache->getLifeTime(), 86400, '->getLifeTime() return the 86400 as the default lifetime');
$cache->setLifeTime(10);
$t->is($cache->getLifeTime(), 10, '->setLifeTime() takes a number of seconds as its first argument');
