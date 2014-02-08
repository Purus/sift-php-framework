<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfCliTaskEnvironment provides access to project settings for tasks
 *
 * @package    Sift
 * @subpackage cli
 */
class sfCliTaskEnvironment {
  
  protected $variables = array();
  
  /**
   * Constructs the object
   * 
   * @param array $variables
   */
  public function __construct($variables = array())
  {
    $this->variables = $variables;
  }
  
  /**
   * Returns environment parameter
   * 
   * @param string $name Variable name 
   * @param mixed $default Default value if variable is not present 
   * @return mixed
   */
  public function get($name, $default = null)
  {
    return isset($this->variables[$name]) ? $this->variables[$name] : $default;
  }
  
  /**
   * Sets environment variable
   * 
   * @param string $name Variable name
   * @param mixed $value Variable value
   * @return sfCliTaskEnvironment
   */
  public function set($name, $value)
  {
    $this->variables[$name] = $value;
    return $this;
  }
  
  /**
   * Add environment variables
   * 
   * @param array $variables Array of variables
   * @return sfCliTaskEnvironment
   */
  public function add($variables)
  {
    foreach($variables as $name => $value)
    {
      $this->set($name, $value);
    }    
    return $this;
  }
 
  /**
   * Returns all enviroment variables
   * 
   * @return array
   */
  public function getAll()
  {
    return $this->variables;
  }
  
  /**
   * Clears all variables
   * 
   * @return sfCliTaskEnvironment
   */
  public function clear()
  {
    $this->variables = array();
    return $this;
  }
  
}
