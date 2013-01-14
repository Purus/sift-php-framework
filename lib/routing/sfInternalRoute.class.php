<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfInternalRoute class is a utility class to modify internal route parameters
 * 
 * @package Sift
 * @subpackage routing
 */
class sfInternalRoute {

  protected $internalUri;  
  protected $baseRoute;
  
  /**
   * Parameters holder
   * @var array
   */
  protected $params = array();
  
  /**
   * Instance holder
   * 
   * @var sfInternalRoute
   */
  protected static $instances = array();
  
  /**
   * Returns an instance of this class. Implements singleton pattern
   * 
   * @return sfInternalRoute
   */
  public static function getInstance($route = null)
  {
    $route = $route ? $route : 1;
    if(!self::$instances[$route])
    {
      self::$instances[$route] = self::create($route);
    }
    return self::$instances[$route];
  }
  
  /**
   * Creates new sfInternalRoute class
   * 
   * @param string $route
   * @return sfInternalRoute 
   */
  public static function create($route = null)
  {
    return new self($route);
  }
  
  /**
   * Constructor
   * 
   * @param string $route Internal route (if null, current route will be used)
   */
  public function __construct($route = null)
  {
    if(is_null($route))
    {
      $route = sfRouting::getInstance()->getCurrentInternalUri(true);
    }
    
    $this->internalUri = $route;
        
    if(strpos($this->internalUri, '?') !== false)
    {
      list($baseRoute, $params) = explode('?', $this->internalUri);
      parse_str($params, $params);
      
      $this->baseRoute = $baseRoute;
      $this->params = $params;            
    }
    else
    {
      $this->baseRoute = $this->internalUri;
      $this->params = array();      
    }
  }

  /**
   * Returns an array of route parameters
   * 
   * @return array
   */
  public function getParameters()
  {
    return $this->params;
  }
  
  /**
   * Sets parameters to the route
   * 
   * @param array $params
   * @return sfInternalRoute 
   */
  public function setParameters(array $params)
  {
    $this->params = $params;
    return $this;
  }
  
  /**
   * Removes parameter
   * 
   * @param string|array $name Parameter name or array of parameter names
   * @return sfInternalRoute 
   */
  public function remove($name)
  {
    if(!is_array($name))
    {
      $name = array($name);
    }
    foreach($name as $i => $key)
    {
      if(isset($this->params[$key]))
      {
        unset($this->params[$key]);
      }
    }
    return $this;
  }
  
  /**
   * Adds a parameter
   * 
   * @param string $name
   * @param mixed $value
   * @return sfInternalRoute 
   */
  public function add($name, $value)
  {
    $this->params[$name] = $value;
    return;
  }
  
  public function addFromRequest($name)
  {
    $value = $this->getRequest()->getParameter($name);
    if(!is_null($value))
    {
      $this->add($name, $value);
    }    
    return $this;
  }
  
  /**
   * Modifies the parameter (sets it to new value). If null is passed as $newValue,
   * the parameter will be removed.
   * 
   * @param string $name
   * @param mixed $newValue
   * @return sfInternalRoute 
   */
  public function modify($name, $newValue)
  {
    if(isset($this->params[$name]))
    {
      if(is_null($newValue))
      {
        return $this->remove($name);
      }      
    }
    
    $this->params[$name] = $newValue;    
    return $this;
  }
  
  /**
   * Modifies the parameters in a batch 
   * 
   * @param array $namesAndValues associative array of $name => $newValue
   * @return sfInternalRoute 
   */
  public function batchModify(array $namesAndValues)
  {
    foreach($namesAndValues as $name => $newValue)
    {
      $this->modify($name, $newValue);
    }
    return $this;
  }
  
  /**
   * Removes the pager parameter
   * 
   * @param string $name Pager parameter name (or detects automatically)
   * @return sfInternalRoute 
   */
  public function removePagerParameter($name = null)
  {
    return $this->remove($name ? $name : $this->getPagerParameterName());
  }
  
  /**
   * Has this route pager parameter set?
   * 
   * @param string $name Pager parameter name
   * @return boolean 
   */
  public function hasPagerParameter($name = null)
  {
    return isset($this->params[$name ? $name : $this->getPagerParameterName()]);
  }
  
  /**
   * Get pager parameter value if pager parameter is set, false otherwise
   * 
   * @param string $name Pager parameter name
   * @return integer|false  
   */
  public function getPagerParameter($name = null)
  {
    return $this->hasPagerParameter($name) ? 
            $this->params[$name ? $name : $this->getPagerParameterName()] 
            : false;
  }
  
  /**
   * Modifies pager parameter
   * 
   * @param string $value
   * @param string $pagerParameterName
   * @return sfInternalRoute 
   */
  public function modifyPagerParameter($value, $pagerParameterName = null)
  {
    $pagerParameterName = $pagerParameterName ? $pagerParameterName : 
                            $this->getPagerParameterName();
    $this->params[$pagerParameterName] = $value;  
    return $this;
  }
  
  /**
   * Returns final route. Also takes care of pager parameter which will
   * be always added at the end.
   * 
   * @return string
   */
  public function getRoute()
  {
    $pagerParam = $this->getPagerParameterName();
    
    // support for parent classes to override the method
    $params = $this->getParameters();    

    if($pagerParam && 
      isset($params[$pagerParam]))
    {
      $pagerValue = $params[$pagerParam];
      unset($params[$pagerParam]);
    }
    
    // sort alphabetically
    ksort($params);
    
    // put it back, as last parameter
    if($pagerParam && isset($pagerValue))
    {
      $params[$pagerParam] = $pagerValue;
    }     
    
    return $this->baseRoute . (count($params) ? ('?' . http_build_query($params, '', '&')) : '');
  }
  
  /**
   * Tries to find what is the pager parameter name using
   * my%SF_ORM%Pager::getParameterName() where %SF_ORM% is 
   * current setting.
   * 
   * @return string|false
   */
  public function getPagerParameterName()
  {
    // pager parameter will be added as the last parameter
    $pagerParamCallback = sprintf('my%sPager::getParameterName', 
                            ucfirst(sfConfig::get('sf_orm')));
    $pagerParam = false;    
    if(is_callable($pagerParamCallback))
    {
      $pagerParam = call_user_func($pagerParamCallback);
    }    
    return $pagerParam;    
  }
  
  /**
   * __toString() magix method. Simply calls ->getRoute()
   * 
   * @return string
   */
  public function __toString()
  {
    return $this->getRoute();
  }
  
  /**
   * Returns sfRequest object
   * 
   * @return sfRequest
   */
  protected function getRequest()
  {
    return sfContext::getInstance()->getRequest();
  }
  
}
