<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service container
 *
 * @package    Sift
 * @subpackage service
 */
class sfServiceContainer {

  /**
   * Service identifier
   *
   */
  const SERVICE_IDENTIFIER = '@';

  /**
   * Dependency identifier
   *
   */
  const DEPENDENCY_IDENTIFIER = '$';

  /**
   * Name of self service
   */
  const SELF_NAME = 'service_container';

  /**
   * Array of services
   *
   * @var array
   */
  protected $services = array();

  /**
   * Array of service definitions
   *
   * @var array
   */
  protected $definitions = array();

  /**
   * Dependency maps
   *
   * @var sfDependencyInjectionMaps
   */
  protected $maps;

  /**
   * Dependencies
   *
   * @var sfDependencyInjectionDependencies
   */
  protected $dependencies;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->maps = new sfDependencyInjectionMaps();
    $this->dependencies = new sfDependencyInjectionDependencies($this);

    // register self as a service
    $this->services[self::SELF_NAME] = $this;
  }

  /**
   * Returns the service
   *
   * @param string $serviceName
   * @return mixed
   * @throws InvalidArgumentException If the service is not registered
   */
  public function get($serviceName)
  {
    if(isset($this->services[$serviceName]))
    {
      return $this->services[$serviceName];
    }
    return $this->buildService($serviceName);
  }

  /**
   * Sets a service
   *
   * @param string $serviceName
   * @param mixed $service The service
   * @return sfServiceContainer
   */
  public function set($serviceName, $service)
  {
    $this->services[$serviceName] = $service;
    return $this;
  }

  /**
   * Set service
   *
   * @param string $serviceName
   * @param sfServiceDefinition|array $service
   * @return sfServiceContainer
   */
  public function register($serviceName, $service)
  {
    if(!$service instanceof sfServiceDefinition)
    {
      if(is_array($service))
      {
        $service = sfServiceDefinition::createFromArray($service);
      }
      else
      {
        throw new InvalidArgumentException('Invalid service definition given. Should be an array of sfServiceDefinition object.');
      }
    }

    $this->definitions[$serviceName] = $service;
    return $this;
  }

  /**
   * Gets all service ids.
   *
   * @return array An array of all defined service ids
   */
  public function getServiceIds()
  {
    return array_keys($this->definitions);
  }

  /**
   * Gets all service definitions
   *
   * @return array An array of all defined services
   */
  public function getServiceDefinitions()
  {
    return $this->definitions;
  }

  /**
   * Return array of already initialized services
   *
   * @return array
   */
  public function getServices()
  {
    return $this->services;
  }

  /**
   * Clears the container. All services are carefully shutdown.
   *
   * @return sfServiceContainer
   */
  public function clear()
  {
    // we need to clear in reverse order, more important services are at the top
    // remove from the bottom
    foreach(array_reverse($this->services) as $serviceName => $service)
    {
      $this->remove($serviceName);
    }

    $this->services = array();
    $this->definitions = array();

    // register self again
    $this->services[self::SELF_NAME] = $this;

    return $this;
  }

  /**
   * Builds the service
   *
   * @param string $serviceName
   * @return mixed
   * @throws InvalidArgumentException
   */
  protected function buildService($serviceName)
  {
    if(!isset($this->definitions[$serviceName]))
    {
      throw new InvalidArgumentException(sprintf('The given service "%s" is not set.', $serviceName));
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfServiceContainer} Building service "%s"', $serviceName));
    }

    return $this->services[$serviceName] = $this->createObjectFromDefinition($this->definitions[$serviceName]);
  }

  /**
   * Creates the object from callback definition
   *
   * @param sfObjectCallbackDefinition $definition
   * @return object
   * @throws InvalidArgumentException
   */
  public function createObjectFromDefinition(sfObjectCallbackDefinition $definition)
  {
    $arguments = $this->resolveDependencies($this->resolveServices($this->resolveValue($definition->getArguments())));

    // static call
    if(null !== $definition->getConstructor())
    {
      $object = call_user_func_array(array($this->resolveValue($definition->getClass()), $definition->getConstructor()), $arguments);
    }
    else
    {
      $object = $this->createObject($definition->getClass(), $arguments);
    }

    foreach($definition->getMethodCalls() as $call)
    {
      call_user_func_array(array($object, $call[0]), $this->resolveDependencies($this->resolveServices($this->resolveValue($call[1]))));
    }

    if($callable = $definition->getConfigurator())
    {
      if(is_array($callable))
      {
        $callable[0] = $this->resolveValue($callable[0]);
      }

      if(!sfToolkit::isCallable($callable, false, $callableName))
      {
        throw new InvalidArgumentException(sprintf('The configured callable "%s" for class "%s" is not callable.', $callableName, get_class($object)));
      }

      call_user_func($callable, $object);
    }

    return $object;
  }

  /**
   * Replaces parameter placeholders (%name%) by their values.
   *
   * @param  mixed $value A value
   * @return mixed The same value with all placeholders replaced by their values
   * @throw RuntimeException if a placeholder references a parameter that does not exist
   */
  public function resolveValue($value)
  {
    if(is_array($value))
    {
      $args = array();
      foreach($value as $k => $v)
      {
        $args[$this->resolveValue($k)] = $this->resolveValue($v);
      }
      $value = $args;
    }
    elseif(is_string($value))
    {
      $value = $this->replaceConstants($value);
    }
    return $value;
  }

  /**
   * Replaces constants (like %SF_CACHE_DIR%...)
   *
   * @param string $value
   * @return mixed
   */
  public function replaceConstants($value)
  {
    // it looks like a constant
    if(strpos($value, '%') !== false)
    {
      if(preg_match('/%(.+?)%/', $value, $matches, PREG_OFFSET_CAPTURE))
      {
        $name = strtolower($matches[1][0]);
        if(sfConfig::has($name))
        {
          $configured = sfConfig::get($name);
        }
        else
        {
          $configured = $value;
        }
        // we have non string
        if(is_array($configured) || is_object($configured))
        {
          return $configured;
        }
        $value = substr_replace($value, $configured, $matches[0][1], strlen($matches[0][0]));
      }
    }
    return $value;
  }

  /**
   * Resolve services. Services are marked with at mark (@)
   *
   * @param  mixed $value A value
   * @return mixed
   */
  public function resolveServices($value)
  {
    if(is_array($value))
    {
      $value = array_map(array($this, 'resolveServices'), $value);
    }
    elseif(is_string($value) && 0 === strpos($value, self::SERVICE_IDENTIFIER))
    {
      // get the service
      $value = $this->get(substr($value, 1));
    }
    return $value;
  }

  /**
   * Resolve dependencies. Dependencies are marked with exclamation mark (!)
   *
   * @param  mixed $value A value
   * @return mixed
   */
  public function resolveDependencies($value)
  {
    if(is_array($value))
    {
      $value = array_map(array($this, 'resolveDependencies'), $value);
    }
    elseif(is_string($value) && 0 === strpos($value, self::DEPENDENCY_IDENTIFIER))
    {
      // get the dependency
      $value = $this->getDependencies()->get(substr($value, 1));
    }
    return $value;
  }

  /**
   * Removes the service
   *
   * @param string $serviceName
   * @return sfServiceContainer
   */
  public function remove($serviceName)
  {
    if(isset($this->services[$serviceName]))
    {
      if($this->services[$serviceName] instanceof sfIService
        || method_exists($this->services[$serviceName], 'shutdown'))
      {
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->info(sprintf('{sfServiceContainer} Shutting down service "%s"', $serviceName));
        }
        $this->services[$serviceName]->shutdown();
      }
    }
    unset($this->services[$serviceName]);
    unset($this->definitions[$serviceName]);
    return $this;
  }

  /**
   * Is the service registered?
   *
   * @param string $serviceName The service name
   * @return boolean
   */
  public function has($serviceName)
  {
    if(isset($this->services[$serviceName]) || isset($this->definitions[$serviceName]))
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Create an object with given class name
   *
   * @param string $className The class name
   * @param array $arguments The arguments
   */
  public function createObject($className, $arguments = null)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfServiceContainer} Creating class "%s"', $className));
    }

    // construct the object
    $constructor = new sfDependencyInjectionBuilder($className, $this->getDependencies(), $this->getMaps());

    return $constructor->constructObject($arguments);
  }

  /**
   * Returns the dependency maps
   *
   * @return sfDependencyInjectionMaps
   */
  public function getMaps()
  {
    return $this->maps;
  }

  /**
   * Returns the dependencies
   *
   * @return sfDependencyInjectionDependencies
   */
  public function getDependencies()
  {
    return $this->dependencies;
  }

}