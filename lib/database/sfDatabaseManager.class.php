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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfDatabaseManager {

  protected $databases = array();

  /**
   * Retrieves the database connection associated with this sfDatabase implementation.
   *
   * @param string A database name
   *
   * @return mixed A Database instance
   *
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
   * Initializes this sfDatabaseManager object
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this sfDatabaseManager object
   */
  public function initialize()
  {
    // load database configuration
    $this->databases = require(sfConfigCache::getInstance()->checkConfig(
                        sfConfig::get('sf_app_config_dir_name') . '/databases.yml'));
  }

  /**
   * Executes the shutdown procedure
   *
   * @return void
   *
   * @throws sfDatabaseException If an error occurs while shutting down this DatabaseManager
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
