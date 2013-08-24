<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

ob_start();
$plan = 14;
$t = new lime_test($plan);

if (!extension_loaded('SQLite') && !extension_loaded('pdo_SQLite'))
{
  $t->skip('SQLite needed to run these tests', $plan);
  return;
}

// initialize the storage
$database = new sfPDODatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec('CREATE TABLE session (id, blob_data, expire)');

ini_set('session.use_cookies', 0);
$session_id = "1";

class myDatabaseManager extends sfDatabaseManager {

  public function __construct($databases)
  {
    $this->databases = $databases;
  }

  protected function loadDatabases($force = false)
  {
  }

}

$manager = new myDatabaseManager(array('default' => $database));

$storage = new sfPDOSessionStorage($manager, array('db_table' => 'session', 'session_id' => $session_id));
$t->ok($storage instanceof sfStorage, 'sfPDOSessionStorage is an instance of sfStorage');

// regenerate()
$oldSessionData = 'foo:bar';
$storage->sessionWrite($session_id, $oldSessionData);
$storage->regenerate(false);

$newSessionData = 'foo:bar:baz';
$storage->sessionWrite(session_id(), $newSessionData);
$t->isnt(session_id(), $session_id, 'regenerate() regenerated the session with a different session id');

// checking if the old session record still exists
$result = $connection->query(sprintf('SELECT id, blob_data FROM session WHERE id = "%s"', $session_id));
$data = $result->fetchAll();
$t->is(count($data), 1, 'regenerate() has kept destroyed old session');
$t->is($data[0]['blob_data'], $oldSessionData, 'regenerate() has kept destroyed old session data');

// checking if the new session record has been created
$result = $connection->query(sprintf('SELECT id, blob_data FROM session WHERE id = "%s"', session_id()));
$data = $result->fetchAll();
$t->is(count($data), 1, 'regenerate() has created a new session record');
$t->is($data[0]['blob_data'], $newSessionData, 'regenerate() has created a new record with correct data');

$session_id = session_id();

// sessionRead()
try
{
  $retrieved_data = $storage->sessionRead($session_id);
  $t->pass('sessionRead() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionRead() does not throw an exception');
}
$t->is($retrieved_data, $newSessionData, 'sessionRead() reads session data');

// sessionWrite()
$otherSessionData = 'foo:foo:foo';
try
{
  $write = $storage->sessionWrite($session_id, $otherSessionData);
  $t->pass('sessionWrite() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionWrite() does not throw an exception');
}

$t->ok($write, 'sessionWrite() returns true');
$t->is($storage->sessionRead($session_id), $otherSessionData, 'sessionWrite() wrote session data');

// sessionGC()
try
{
  $storage->sessionGC(0);
  $t->pass('sessionGC() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionGC() does not throw an exception');
}

// destroy the session
try
{
  $storage->sessionDestroy($session_id);
  $t->pass('sessionDestroy() does not throw an exception');
}
catch (Exception $e)
{
  $t->fail('sessionClose() does not throw an exception');
}
$result = $connection->query(sprintf('SELECT id, blob_data FROM session WHERE id = "%s"', $session_id));
$data = $result->fetchAll();
$t->is(count($data), 0, 'session is removed from the database');

// shutdown the storage
$storage->shutdown();
