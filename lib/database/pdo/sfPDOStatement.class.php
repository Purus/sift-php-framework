<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPDOStatement extensions. 
 * 
 * Logging based on the code by inf3rno (https://github.com/inf3rno)
 *  
 * @package Sift
 * @subpackage database
 * @see https://github.com/inf3rno/Webshop-refactoring/tree/master/Clean/DataBase/Logged
 */
class sfPDOStatement extends PDOStatement {

  /**
   * @var sfPdo connection
   */
  private $connection;

  /**
   * Array of bounded params, used for logging
   * 
   * @var array
   */
  protected $params = array();
  
  /**
   * Array of options
   * 
   * @var array
   */
  protected $options = array(
    'logging' => false
  );
   
  /**
   * Constructor
   * 
   * @param sfPdo $conn
   * @param array $options 
   */
  private function __construct($conn, $options = array())
  {    
    $this->connection = $conn;
    $this->options = array_merge($this->options, $options);
  }

  public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, 
          $length = null, $driver_options = null)
  {
    if(!parent::bindParam($parameter, $variable, $data_type, $length, $driver_options))
    {
      return false;
    }

    if($this->options['logging'])
    {
      $this->logParam($parameter, $variable);
    }    
    
    return true;
  }  
  
  /**
   *
   * @param type $parameter
   * @param type $value
   * @param type $data_type
   * @return boolean 
   */
  public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
  {
    if(!parent::bindValue($parameter, $value, $data_type))
    {
      return false;
    }

    if($this->options['logging'])
    {
      $this->logValue($parameter, $value);
    }
    
    return true;
  }
    
  /**
   * Executes the statement
   * 
   * @return PDOResultset
   */
  public function execute($params = null)
  {
    if($this->options['logging'])
    {
      if($params)
      {
        foreach($params as $parameter => $value)
        {
          $this->logValue($parameter, $value);
        }
      }
      
      $query = array();
      
      // interpolate parameters
      foreach((array)self::fixParams($this->params) as $param)
      {
        $param = htmlspecialchars($param, ENT_QUOTES, sfConfig::get('sf_charset'));
        $query[] = var_export(is_scalar($param) ? $param : (string) $param, true);
      }
      
      $this->connection->log(sprintf('%s (%s)', $this->queryString, join(', ', $query)));
      
      if(sfConfig::get('sf_debug'))
      {
        sfTimerManager::getTimer('Database');
      }
    }
    
    $result = parent::execute($params);
    
    if(sfConfig::get('sf_debug'))
    {      
      sfTimerManager::getTimer('Database')->addTime();
    }
    
    // reset
    $this->params = array();
    
    return $result;
  }

  /**
   * Fixes query parameters for logging.
   * 
   * @param  array $params
   * 
   * @return array
   */
  static public function fixParams($params)
  {
    foreach($params as $key => $param)
    {
      if(strlen($param) >= 255)
      {
        $params[$key] = '[' . strlen($param) / 1024 .' kB]';
      }
    }
    return $params;
  }
  
  /**
   * Logs the parameter to the parameter stack
   * 
   * @param mixed $parameter
   * @param mixed $value 
   */
  public function logParam($parameter, &$value)
  {
    if(isset($this->params[$parameter]))
    {
      unset($this->params[$parameter]);
    }      
    $this->params[$parameter] = &$value;
  }

  /**
   * Logs the value to the parameter stack
   * 
   * @param mixed $parameter
   * @param mixed $value 
   */
  public function logValue($parameter, $value)
  {
    if(isset($this->params[$parameter]))
    {
      unset($this->params[$parameter]);
    }
    $this->params[$parameter] = $value;
  }
  
}