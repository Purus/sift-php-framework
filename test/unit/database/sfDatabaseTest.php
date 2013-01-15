<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(15, new lime_output_color());

class myDatabase extends sfDatabase
{
  function connect () {}
  function shutdown () {}
}

$context = new sfContext();
$database = new myDatabase();
$database->initialize(array());

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($database, 'parameter');

putenv('DATABASE_USER=root');
putenv('DATABASE_PASSWORD=mysecret$');
putenv('DATABASE_NAME=my_database');

$database = new myDatabase();
$database->initialize(array(
  'classname' => 'PropelPDO',
  'username' => '%ENV_DATABASE_USER%',
  'password' => '%ENV_DATABASE_PASSWORD%',
  'encoding' => 'utf-8',
  'persistent' => true,
  'pooling' => true,
  'dsn' => 'mysql:dbname=%ENV_DATABASE_NAME%;host=localhost'        
));

$t->is($database->getParameterHolder()->getAll(), array(
  'classname' => 'PropelPDO',
  'username' => 'root',
  'password' => 'mysecret$',
  'encoding' => 'utf-8',
  'persistent' => true,
  'pooling' => true,
  'dsn' => 'mysql:dbname=my_database;host=localhost'        
), 'replacement of environment variables works ok for propel style settings');

putenv('DATABASE_SERVER=192.168.37.1');

$database = new myDatabase();
$database->initialize(array(
  'dsn' => 'mysql:dbname=%ENV_DATABASE_NAME%;host=%ENV_DATABASE_SERVER%',
  'username' => '%ENV_DATABASE_USER%',
  'password' => '',
  'attributes' => array(
    'quote_identifier' =>  false,
    'use_native_enum' =>  false,
    'validate' => 'all',
    'idxname_format' => '%s_idx',
    'seqname_format' => '%s_seq',
    'tblname_format' => '%s',      
  )      
));

$t->is($database->getParameterHolder()->getAll(), array(
  'dsn' => 'mysql:dbname=my_database;host=192.168.37.1',
  'username' => 'root',
  'password' => '',
  'attributes' => array(
    'quote_identifier' =>  false,
    'use_native_enum' =>  false,
    'validate' => 'all',
    'idxname_format' => '%s_idx',
    'seqname_format' => '%s_seq',
    'tblname_format' => '%s',      
  )      
), 'replacement of environment variables works ok for doctrine style settings');
