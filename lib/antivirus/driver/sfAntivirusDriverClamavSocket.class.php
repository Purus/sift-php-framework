<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Antivirus driver for Clamav antivirus. Sockets implementation.
 *
 * @package Sift
 * @subpackage antivirus
 * @link http://www.jejik.com/articles/2009/07/scanning_files_with_clamav_from_cakephp/
 * @link http://www.clamav.net/doc/latest/html/node28.html
 */
class sfAntivirusDriverClamavSocket extends sfAntivirus {

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // default socket to connect to
    'host' => 'unix:///var/run/clamav/clamd.ctl',
    'port' => 0,
    'timeout' => 60,
  );

  /**
   * @var resource Reference to the connection to clamd
   */
  protected $connection = null;

  /**
   * @var boolean The state of the connection
   */
  protected $connected = false;

  /**
   * Setup the driver
   *
   */
  protected function setup()
  {
    if(!sfToolkit::isCallable('fsockopen'))
    {
      throw new InvalidArgumentException('The driver cannot connect to sockets. Function "fsockopen" is not available. Please enable it in the php.ini or use another driver.');
    }
  }

  /**
   * Scan a single file or a directory
   *
   * @param string $path Full path to the file to scan
   * @return array Array(status, array of viruses found)
   */
  public function scan($object)
  {
    if(!is_file($object) && !is_dir($object))
    {
      throw new InvalidArgumentException('Cannot scan object. Object is not a directory nor a file.');
    }

    $command = 'SCAN';
    if(!$response = $this->exec($command . ' ' . $object))
    {
      throw new RuntimeException('Error executing the command.');
    }

    $viruses = array();
    $status = self::STATUS_CLEAN;
    foreach(explode("\n", $response) as $line)
    {
      if(preg_match(sfAntivirusDriverClamav::SOMETHING_FOUND_REGEXP, $line, $match))
      {
        $status = self::STATUS_INFECTED;
      }

      if(preg_match(sfAntivirusDriverClamav::INFECTIONS_REGEXP, $line, $matches))
      {
        $viruses[] = $matches[1];
      }
    }

    return array(
      $status, $viruses
    );
  }

  /**
   * Connect to clamav daemon
   *
   * @return boolean Success
   */
  public function connect()
  {
    if($this->isConnected())
    {
      return true;
    }

    $this->connection = @fsockopen($this->getOption('host'), $this->getOption('port'),
            $errNum, $errStr, $this->getOption('timeout'));

    if(!empty($errNum) || !empty($errStr))
    {
      throw new RuntimeException(sprintf('Error while opening connection to Clamav daemon. Error returned: %s (code: %s)', $errStr, $errNum));
    }

    return $this->connected = is_resource($this->connection);
  }

  /**
   * Is there an active connection to clamav daemon?
   *
   * @return boolean
   */
  public function isConnected()
  {
    return $this->connected;
  }

  /**
   * Disconnect from spamd
   *
   * @return boolean Success
   */
  public function disconnect()
  {
    if(!is_resource($this->connection))
    {
      $this->connected = false;

      return true;
    }

    $this->connected = !fclose($this->connection);
    if(!$this->connected)
    {
      $this->connection = null;
    }

    return !$this->connected;
  }

  /**
   * Read from the spamd socket and close the connection
   *
   * @return mixed Socket data
   */
  public function read()
  {
    if(!$this->connected)
    {
      return false;
    }

    $buffer = '';
    while(!feof($this->connection))
    {
      $buffer .= fread($this->connection, 1024);
    }

    $this->disconnect();

    return $buffer;
  }

  /**
   * Send a command to spamd
   *
   * @param string command The command to send
   * @return boolean Success
   */
  public function send($command)
  {
    if(in_array($command[0], array('n', 'z')))
    {
      $command[0] = 'n';
    }
    else
    {
      $command = 'n' . $command;
    }

    if(substr($command, -1) != "\n")
    {
      $command .= "\n";
    }

    if(!$this->write($command))
    {
      $this->disconnect();

      return false;
    }

    return true;
  }

  /**
   * Execute a command on the spamd socket and wait for a response. The response
   * will be stripped of session IDs
   *
   * @param string $command The command to execute
   * @return mixed The result or false on failure
   */
  public function exec($command)
  {
    if(!$this->send($command))
    {
      return false;
    }

    return $this->read();
  }

  /**
   * The PING command
   *
   * @return boolean Success
   */
  public function ping()
  {
    return (trim($this->exec('PING')) == 'PONG');
  }

  /**
   * Write to the spamd socket
   *
   * @param string $data The data to write to the socket
   * @return boolean Success
   */
  protected function write($data)
  {
    if(!$this->isConnected())
    {
      if(!$this->connect())
      {
        return false;
      }
    }

    return @fwrite($this->connection, $data, strlen($data));
  }

  /**
   * Return the connection
   *
   * @return resource Connection to clamd
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->disconnect();
  }

}
