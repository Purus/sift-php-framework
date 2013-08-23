<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfContext provides information about the current application context, such as
 * the module and action names and the module directory. References to the
 * current controller, request, and user implementation instances are also
 * provided.
 *
 * @package    Sift
 * @subpackage util
 */
class sfContext {

  /**
   * Application
   *
   * @var sfApplication
   */
  protected $application = null;

  /**
   * Action stack
   *
   * @var sfActionStack
   */
  protected $actionStack = null;

  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Current name
   *
   * @var string
   */
  protected static $current = null;

  /**
   * Construct the context
   *
   * @param sfApplication $application
   */
  public function __construct(sfApplication $application)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getLogger()->info('{sfContext} Initialization');
    }

    $this->application = $application;

    // create a new action stack
    $this->actionStack = new sfActionStack();

    $serviceContainer = sfServiceContainer::getInstance();
    $diContainer = sfDependencyInjectionContainer::getInstance();

    $diContainer->getDependencies()->clear();

    // register self to the dependencies for DIC
    $diContainer->getDependencies()->set('context', $this);

    // clear container
    $serviceContainer->clear();

    // include the factories configuration, will setup services
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name') . '/factories.yml'));

    // load databases
    if(sfConfig::get('sf_use_database'))
    {
      // create the manager
      $manager = $serviceContainer->get('database_manager');
      $diContainer->getDependencies()->set('database_manager', $serviceContainer->get('database_manager'));
    }

    // register existing services as dependecies, 
    foreach($serviceContainer->getServices() as $id => $service)
    {
      $diContainer->getDependencies()->set($id, $service);
    }

    sfShutdownScheduler::getInstance()->clear();

    // register our shutdown function, to be called last
    sfShutdownScheduler::getInstance()->register(array($this, 'shutdown'), array(), sfShutdownScheduler::LOW_PRIORITY);

    $this->application->getEventDispatcher()->notify(new sfEvent('context.load_factories', array(
      'context' => $this
    )));
  }

  /**
   * Creates a new context instance.
   *
   * @param  sfApplication $application  An sfApplication instance
   * @param  string        $name         A name for this context (application name by default)
   * @param  string        $class        The context class to use (sfContext by default)
   *
   * @return sfContext An sfContext instance
   */
  public static function createInstance(sfApplication $application, $name = null, $class = __CLASS__)
  {
    if(null === $name)
    {
      $name = $application->getName();
    }

    self::$current = $name;

    $instance = new $class($application);
    if(!$instance instanceof sfContext)
    {
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfContext.', $class));
    }

    self::$instances[$name] = $instance;
    return self::$instances[$name];
  }

  /**
   * @see sfServiceContainer::register
   */
  public function registerService($serviceName, $service)
  {
    return sfServiceContainer::getInstance()->register($serviceName, $service);
  }

  /**
   * @see sfServiceContainer::get
   */
  public function getService($serviceName)
  {
    return sfServiceContainer::getInstance()->get($serviceName);
  }

  /**
   * @see sfServiceContainer::get
   */
  public function setService($serviceName, $service)
  {
    return sfServiceContainer::getInstance()->set($serviceName, $service);
  }

  /**
   * Returns application instance
   * 
   * @return sfApplication
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @param string $name The name of the sfContext to retrieve.
   *
   * @return sfContext The sfContext implementation instance.
   */
  static public function getInstance($name = null)
  {
    if(null === $name)
    {
      $name = self::$current;
    }

    if(!isset(self::$instances[$name]))
    {
      throw new sfException(sprintf('The "%s" context does not exist.', $name));
    }

    return self::$instances[$name];
  }

  /**
   * Checks to see if there has been a context created
   *
   * @param  string $name  The name of the sfContext to check for
   *
   * @return bool true is instanced, otherwise false
   */
  public static function hasInstance($name = null)
  {
    if(null === $name)
    {
      $name = self::$current;
    }

    return isset(self::$instances[$name]);
  }

  /**
   * Dispatches the current request.
   *
   * 
   */
  public function dispatch()
  {
    return $this->getController()->dispatch();
  }

  /**
   * Sets the current context to something else
   *
   * @param string $name  The name of the context to switch to
   */
  public static function switchTo($name)
  {
    if(!isset(self::$instances[$name]))
    {
      $current = sfContext::getInstance()->getApplication();
      sfContext::createInstance(
              sfCore::getProject()->getApplication($name, $current->getEnvironment(), $current->isDebug()));
    }
    self::$current = $name;
  }

  /**
   * Retrieve the action name for this context.
   *
   * @return string The currently executing action name, if one is set,
   *                otherwise null.
   */
  public function getActionName()
  {
    // get the last action stack entry
    if($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getActionName();
    }
  }

  /**
   * Retrieve the ActionStack.
   *
   * @return sfActionStack the sfActionStack instance
   */
  public function getActionStack()
  {
    return $this->actionStack;
  }

  /**
   * Retrieve the controller.
   * @return sfController The current sfController implementation instance.
   */
  public function getController()
  {
    return $this->getService('controller');
  }

  /**
   * Retrieves the mailer.
   *
   * @return myMailer The current myMailer singleton
   */
  public function getMailer()
  {
    if(!isset($this->mailer))
    {
      $this->mailer = myMailer::getInstance();
    }
    return $this->mailer;
  }

  /**
   * Retrieve the logger.
   *
   * @return sfLogger The current sfLogger implementation instance.
   */
  public function getLogger()
  {
    return sfLogger::getInstance();
  }

  /**
   * Retrieve a database connection from the database manager.
   *
   * This is a shortcut to manually getting a connection from an existing
   * database implementation instance.
   *
   * If the [sf_use_database] setting is off, this will return null.
   *
   * @param name A database name.
   * @return mixed A Database instance.
   * @throws sfDatabaseException If the requested database name does not exist.
   */
  public function getDatabaseConnection($name = 'default')
  {
    return $this->getDatabaseManager()->getDatabase($name)->getConnection();
  }

  /**
   * Retrieve objects using sfIDataRetriever implementation
   *
   * @param string $class Class name
   * @param string $peerMethod Peer method
   * @param array $options
   * @return mixed
   */
  public function retrieveObjects($class, $peerMethod, $options = array())
  {
    $retrievingClass = 'sf' . ucfirst(sfConfig::get('sf_orm')) . 'DataRetriever';
    if(!class_exists($retrievingClass))
    {
      throw new InvalidArgumentException(sprintf('The data retriever class "%s" does not exist'), $retrievingClass);
    }
    return call_user_func(array($retrievingClass, 'retrieveObjects'), $class, $peerMethod, $options);
  }

  /**
   * Retrieve the database manager.
   *
   * @return sfDatabaseManager The current sfDatabaseManager instance.
   */
  public function getDatabaseManager()
  {
    return $this->getService('database_manager');
  }

  /**
   * Retrieve the module directory for this context.
   *
   * @return string An absolute filesystem path to the directory of the
   *                currently executing module, if one is set, otherwise null.
   */
  public function getModuleDirectory()
  {
    // get the last action stack entry
    if($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return sfConfig::get('sf_app_module_dir') . '/' . $lastEntry->getModuleName();
    }
  }

  /**
   * Retrieve the module name for this context.
   *
   * @return string The currently executing module name, if one is set,
   *                otherwise null.
   */
  public function getModuleName()
  {
    // get the last action stack entry
    if($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getModuleName();
    }
  }

  /**
   * Retrieve the current view instance for this context.
   *
   * @return sfView The currently view instance, if one is set,
   *                otherwise null.
   */
  public function getCurrentViewInstance()
  {
    // get the last action stack entry
    if($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getViewInstance();
    }
  }

  /**
   * Retrieve the request.
   *
   * @return sfRequest The current sfRequest implementation instance.
   */
  public function getRequest()
  {
    return $this->getService('request');
  }

  /**
   * Retrieve the response.
   *
   * @return sfResponse The current sfResponse implementation instance.
   */
  public function getResponse()
  {
    return $this->getService('response');
  }

  /**
   * Set the response object.
   *
   * @param sfResponse A sfResponse instance.
   * @return void.
   */
  public function setResponse(sfResponse $response)
  {
    return $this->setService('response', $response);
  }

  /**
   * Retrieve the storage.
   *
   * @return sfIStorage The current sfStorage implementation instance.
   */
  public function getStorage()
  {
    return $this->getService('storage');
  }

  /**
   * Retrieve the view cache manager
   *
   * @return sfViewCacheManager The current sfViewCacheManager implementation instance.
   */
  public function getViewCacheManager()
  {
    return $this->getService('view_cache_manager');
  }

  /**
   * Retrieve the i18n instance
   *
   * @return sfI18N The current sfI18N implementation instance.
   */
  public function getI18N()
  {
    return $this->getService('i18n');
  }

  /**
   * Retrieve the user.
   *
   * @return sfUser The current sfUser implementation instance.
   */
  public function getUser()
  {
    return $this->getService('user');
  }

  /**
   * Returns dispatcher instance
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->application->getEventDispatcher();
  }

  /**
   * Returns an array of already active services
   *
   * @return array
   */
  public function getServices()
  {
    return sfServiceContainer::getInstance()->getServices();
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfContext} Shutting down');
    }
    
    sfServiceContainer::getInstance()->clear();
  }

}
