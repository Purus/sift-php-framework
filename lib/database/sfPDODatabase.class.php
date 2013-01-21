<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPDODatabase provides connectivity for the PDO database abstraction layer.
 *
 * @package    Sift
 * @subpackage database
 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfPDODatabase extends sfDatabase {

  /**
   * Connects to the database.
   *
   * @throws sfDatabaseException If a connection could not be created
   */
  public function connect()
  {
    // determine how to get our parameters
    $method = $this->getParameter('method', 'dsn');

    // get parameters
    switch($method)
    {
      case 'dsn':
        $dsn = $this->getParameter('dsn');
        if($dsn == null)
        {
          // missing required dsn parameter
          throw new sfDatabaseException('Database configuration specifies method "dsn", but is missing dsn parameter');
        }
        break;
    }

    try
    {
      $pdo_username = $this->getParameter('username');
      $pdo_password = $this->getParameter('password');
      // driver specific options
      $options      = $this->getParameter('options', array());
      $this->connection = new sfPDO($dsn, $pdo_username, $pdo_password, $options);
    }
    catch(PDOException $e)
    {
      throw new sfDatabaseException($e->getMessage());
    }

    // lets generate exceptions instead of silent failures
    if(defined('PDO::ATTR_ERRMODE'))
    {
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    else
    {
      $this->connection->setAttribute(PDO_ATTR_ERRMODE, PDO_ERRMODE_EXCEPTION);
    }
  }

  /**
   * Executes the shutdown procedure.
   *
   * @return void
   *
   * @throws sfDatabaseException If an error occurs while shutting down this database
   */
  public function shutdown()
  {
    if($this->connection !== null)
    {
      $this->connection = null;
    }
  }

}
