<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIp2CountryDriverGeoIp ip2country driver using the database from http://software77.net/geo-ip/
 *
 * @package    Sift
 * @subpackage ip2country
 */
class sfIp2CountryDriverGeoIp extends sfIp2Country {

  /**
   * Connected flag
   *
   * @var boolean
   */
  protected $isConnected;

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array();

  /**
   * Required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'database'
  );

  /**
   * Setup the driver instance
   *
   * @throws InvalidArgumentException If database is not readable
   * @throws sfConfigurationException If PDO sqlite extension is not loaded
   */
  protected function setup()
  {
    if(!($database = $this->getOption('database'))
        || !is_readable($database))
    {
      throw new InvalidArgumentException(sprintf('Database "%s" is missing or is not readable.', $database));
    }

    if(!extension_loaded('pdo_SQLite'))
    {
      throw new sfConfigurationException('sfSQLiteCache class needs "sqlite" or "pdo_sqlite" extension to be loaded.');
    }
  }

  /**
   * Sets the database name.
   *
   * @param string $database The database name where to store the cache
   * @throws PDOException If the connection cannot be made
   */
  protected function connect()
  {
    if($this->isConnected)
    {
      return;
    }

    $this->dbh = new sfPDO(sprintf('sqlite:%s', $this->getOption('database')), null, null,
        array(PDO::ATTR_PERSISTENT => true));

    $this->isConnected = true;
  }

  /**
   * Disconnects from the database
   *
   */
  public function disconnect()
  {
    // close the connection
    $this->dbh = null;
    $this->isConnected = false;
  }

  /**
   * Get the country code for the ip
   *
   * @param string $ip
   * @param string $default
   */
  public function getCountryCode($ip, $default = null)
  {
    $this->connect();

		$ip = $this->ip2int($ip);

    $statement = $this->dbh->prepare('SELECT code FROM ip2country WHERE ? BETWEEN ip_from AND ip_to');
    $statement->bindParam(1, $ip);
    $result = $statement->execute();

    if($result !== true)
    {
      return $default;
    }

    $code = $statement->fetchColumn();
    $statement->closeCursor();

    return $code === false ? $default : $code;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->disconnect();
  }

}
