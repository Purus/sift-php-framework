<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPDO provides an extension to PDO
 *
 * @package Sift
 * @subpackage database
 */
class sfPDO extends PDO {

  /**
   * Is logging enabled?
   * 
   * @var boolean 
   */
  protected $isLoggingEnabled = false;
  
  /**
   * Creates a PDO instance representing a connection to a database 
   * 
   * @param string $dsn The Data Source Name, or DSN, contains the information required to connect to the database. 
   * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers. 
   * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers. 
   * @param array $options  A key=>value array of driver-specific connection options. 
   */
  public function __construct($dsn, $username = null, $password = null, $options = array())
  {
    parent::__construct($dsn, $username, $password, $options);
    
    // enable logging only is logging enabled and is greater than "notice" (ie. debug, notice)
    $this->isLoggingEnabled = sfConfig::get('sf_logging_enabled') 
            && constant('SF_LOG_'.strtoupper(sfConfig::get('sf_logging_level'))) >= SF_LOG_NOTICE;
    
    // custom statement class
    $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('sfPDOStatement',         
        array($this, array(            
            'logging' => $this->isLoggingEnabled
          ))));
    
    // always use exceptions.
    $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     
  }

  /**
   * Is logging enabled? It if only if sf_logging_enabled is turned on
   * and log level is greater and equal to "notice" (ie. "debug", "notice")
   *  
   * @return boolean
   */
  protected function isLoggingEnabled()
  {
    return $this->isLoggingEnabled;
  }
  
  /**
   * Returns PDO_PARAM_? constant for given $var. USefull for bindParam() method
   * 
   * @param mixed $var
   * @return integer 
   */
  public static function getConstantType($var)
  {
    if(is_int($var))
    {
      return PDO::PARAM_INT;
    }      
    else if(is_bool($var))
    {
      return PDO::PARAM_BOOL;
    }      
    else if(is_null($var))
    {
      return PDO::PARAM_NULL;
    }      
    // Default 
    return PDO::PARAM_STR;
  }
  
  /**
   * PDO::quote() places quotes around the input string (if required) and 
   * escapes special characters within the input string, using a quoting 
   * style appropriate to the underlying driver. 
   * 
   * @param mixed $value
   * @param integer $parameter_type
   * @return string 
   * @see http://cz2.php.net/manual/en/pdo.quote.php#100131
   */
  public function quote($value, $parameter_type = PDO::PARAM_STR) 
  {
    if(is_null($value)) 
    {
      return 'NULL';
    }
    return parent::quote($value, $parameter_type);
  } 
  
  /**
   * Execute an SQL statement and return the number of affected rows 
   *  
   * @param string $statement The SQL statement to prepare and execute. 
   * @return integer Returns the number of rows that were modified or deleted by the SQL statement you issued. If no rows were affected, exec() returns 0. 
   */
  public function exec($statement)
  {
    if(sfConfig::get('sf_debug'))
    {
      sfTimerManager::getTimer('Database');            
    }
    
    if($this->isLoggingEnabled())
    {
      $this->log($statement);
    }
    
    $result = parent::exec($statement);
    
    if(sfConfig::get('sf_debug'))
    {
      sfTimerManager::getTimer('Database')->addTime();      
    }    
    
    return $result;
  }
  
  /**
   * Logs message
   * 
   * @param string $message
   * @return void
   */
  public function log($message)
  {
    if(!$this->isLoggingEnabled())
    {
      return;
    }
    
    return $this->getLogger()->info(sprintf('{sfPDO} %s', (string)$message));
  }
  
  /**
   * Returns sfLogger instance
   * 
   * @return sfLogger
   */
  public function getLogger()
  {
    return sfContext::getInstance()->getLogger();
  }
  
}