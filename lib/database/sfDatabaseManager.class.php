<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDatabaseManager allows you to setup your database connectivity before the
 * request is handled.
 *
 * @package    Sift
 * @subpackage database
 */
class sfDatabaseManager implements sfIService {

  /**
   * Array of databases
   *
   * @var array
   */
  protected $databases = array();

  /**
   * Constructor
   *
   */
  public function __construct()
  {
    // $this->loadDatabases();
  }

  /**
   * Load databases from databases.yml confuration file
   *
   * @throws LogicException If the registered database is not instance of sfDatabase
   */
  protected function loadDatabases()
  {
    // load database configuration
    $databases = require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name') . '/databases.yml'));
    foreach($databases as $name => $database)
    {
      if(!$database instanceof sfDatabase)
      {
        throw new LogicException('The database "%s" (class: "%s") is not an instance of sfDatabase', $name, get_class($database));
      }
    }
    $this->databases = $databases;
  }

  /**
   * Retrieves the database connection associated with this sfDatabase implementation.
   *
   * @param string $name A database name
   * @return sfDatabase A Database instance
   * @throws sfDatabaseException If the requested database name does not exist
   */
  public function getDatabase($name = 'default')
  {
    if(isset($this->databases[$name]))
    {
      return $this->databases[$name];
    }
    // nonexistent database name
    throw new sfDatabaseException(sprintf('Database "%s" does not exist', $name));
  }

  /**
   * Executes the shutdown procedure
   *
   * @return void
   */
  public function shutdown()
  {
    // loop through databases and shutdown connections
    foreach($this->databases as $database)
    {      
      $database->shutdown();
    }
  }

}
