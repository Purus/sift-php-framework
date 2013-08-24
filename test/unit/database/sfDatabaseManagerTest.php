<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

class myDatabaseManager extends sfDatabaseManager {

  public $called = false;

  protected function loadFromConfiguration()
  {
    $this->called = true;
    $this->databases = array(
      'default' => new sfMockDatabase()
    );
  }
}

$manager = new myDatabaseManager();

// databases is loaded only on request
$t->is($manager->called, false, 'constructor does not load databases.');
$databases = $manager->getDatabases();

$t->is($manager->called, true, 'databases are loaded on request');

$t->isa_ok($databases, 'array', 'getDatabases() returns array');
$t->is($databases, array('default' => new sfMockDatabase()), 'getDatabases() returns array of databases');

try
{
  $manager->getDatabase('foo');
  $t->fail('getDatabase() throws exception if database is invalid');
}
catch(sfDatabaseException $e)
{
  $t->pass('getDatabase() throws exception if database is invalid');
}