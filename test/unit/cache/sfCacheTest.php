<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class myCache extends sfCache
{
  public function get($key, $default = null) {}
  public function has($key) {}
  public function set($key, $data, $lifetime = null) {}
  public function remove($key) {}
  public function clean($key = null, $mode = sfCache::MODE_ALL) {}
  public function getLastModified($key) {}
  public function getTimeout($key) {}
  public function removePattern($pattern, $delimiter = ':') {}

}

class fooCache {}

$t = new lime_test(6, new lime_output_color());

$cache = new myCache();

$t->diag('->factory()');

try {
  $driver = sfCache::factory('invalid');
  $t->fail('->factory() throws exception if driver class is invalid');
}
catch(Exception $e)
{
  $t->pass('->factory() throws exception if driver class is invalid');
}

try
{
  $driver = sfCache::factory('myCache');
  $t->pass('->factory() accepts class name for the cache');
  $t->isa_ok($driver, 'myCache', '->factory() returns correct object');
}
catch(Exception $e)
{
  $t->fail('->factory() accepts class name for the cache');
  $t->skip();
}

try
{
  $driver = sfCache::factory('file', array('cache_dir' => sys_get_temp_dir()));  
  $t->pass('->factory() accepts part of the driver class name');
  $t->isa_ok($driver, 'sfFileCache', '->factory() returns correct object');
}
catch(Exception $e)
{
  $t->fail('->factory() accepts part of the driver class name');
  $t->skip();
}

try
{
  $driver = sfCache::factory('fooCache', array('cache_dir' => sys_get_temp_dir()));
  $t->fail('->factory() throws exception if driver class does not implement sfICache interface');
}
catch(Exception $e)
{
  $t->pass('->factory() throws exception if driver class does not implement sfICache interface');
}