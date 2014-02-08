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
class sfDatabaseManager implements sfIService
{
    /**
     * Loaded flag
     *
     * @var boolean
     */
    protected $isLoaded = false;

    /**
     * Array of databases
     *
     * @var array
     */
    protected $databases = array();

    /**
     * Load databases from databases.yml confuration file
     *
     * @param boolean $force Force the load?
     *
     * @throws LogicException If the registered database is not instance of sfDatabase
     */
    public function loadDatabases($force = false)
    {
        if ($this->isLoaded && !$force) {
            return;
        }
        $this->loadFromConfiguration();
        $this->isLoaded = true;
    }

    /**
     * Loads databases from databases.yml file
     *
     * @throws LogicException
     */
    protected function loadFromConfiguration()
    {
        // load database configuration
        $databases = require(sfConfigCache::getInstance()->checkConfig(
            sfConfig::get('sf_app_config_dir_name') . '/databases.yml'
        ));
        foreach ($databases as $name => $database) {
            if (!$database instanceof sfDatabase) {
                throw new LogicException('The database "%s" (class: "%s") is not an instance of sfDatabase', $name, get_class(
                    $database
                ));
            }
        }
        $this->databases = $databases;
    }

    /**
     * Retrieves the database connection associated with this sfDatabase implementation.
     *
     * @param string $name A database name
     *
     * @return sfDatabase A Database instance
     * @throws sfDatabaseException If the requested database name does not exist
     */
    public function getDatabase($name = 'default')
    {
        $this->loadDatabases();

        if (isset($this->databases[$name])) {
            return $this->databases[$name];
        } else {
            throw new sfDatabaseException(sprintf('Database "%s" does not exist', $name));
        }
    }

    /**
     * Returns an array of configured databases
     *
     * @return array
     */
    public function getDatabases()
    {
        $this->loadDatabases();

        return $this->databases;
    }

    /**
     * Executes the shutdown procedure
     *
     * @return void
     */
    public function shutdown()
    {
        // loop through databases and shutdown connections
        foreach ($this->databases as $database) {
            $database->shutdown();
        }
    }

}
