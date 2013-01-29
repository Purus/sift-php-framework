<?php

// we need sqlite for functional tests
if (!extension_loaded('SQLite'))
{
  return false;
}

define('SF_ROOT_DIR',    realpath(dirname(__FILE__).sprintf('/../%s/fixtures/project', isset($type) ? $type : 'functional')));

define('SF_APP',         $app);
define('SF_ENVIRONMENT', 'test');
define('SF_DEBUG',       isset($debug) ? $debug : true);

$sf_sift_lib_dir = dirname(__FILE__) . '/../../lib';
$sf_sift_data_dir = dirname(__FILE__) . '/../../data';

require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir);

sfContext::createInstance(
  sfCore::getApplication(SF_APP, SF_ENVIRONMENT, SF_DEBUG),
  'test'      // name of the context instance
);

if (isset($fixtures))
{
  // initialize database manager
  $databaseManager = new sfDatabaseManager();
  $databaseManager->initialize();

  // cleanup database
  $db = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'/database.sqlite';
  if (file_exists($db))
  {
    unlink($db);
  }

  // initialize database
  $sql = file_get_contents(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'lib.model.schema.sql');
  $sql = preg_replace('/^\s*\-\-.+$/m', '', $sql);
  $sql = preg_replace('/^\s*DROP TABLE .+?$/m', '', $sql);
  $con = $databaseManager->getDatabase('default')->getConnection();
  $tables = preg_split('/CREATE TABLE/', $sql);
  foreach ($tables as $table)
  {
    $table = trim($table);
    if (!$table)
    {
      continue;
    }

    $con->exec('CREATE TABLE '.$table);
  }

  // load fixtures
  
  /*
  $data = new sfPDoData();
  if (is_array($fixtures))
  {
    $data->loadDataFromArray($fixtures);
  }
  else
  {
    $data->loadData(sfConfig::get('sf_data_dir').'/'.$fixtures);
  }
   * 
   */
}

return true;
