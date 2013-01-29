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
class sfContext
{
  protected
    $dispatcher        = null,
    $application       = null;
          
  protected
    $actionStack       = null,
    $controller        = null,
    $databaseManager   = null,
    $request           = null,
    $response          = null,
    $storage           = null,
    $viewCacheManager  = null,
    $i18n              = null,
    $logger            = null,
    $user              = null,
    $mailer            = null;

  protected static
    $instances = array(),
    $current   = null;
  
  /**
   * Creates a new context instance.
   *
   * @param  sfApplication $application  An sfApplication instance
   * @param  string        $name         A name for this context (application name by default)
   * @param  string        $class        The context class to use (sfContext by default)
   *
   * @return sfContext An sfContext instance
   */
  static public function createInstance(sfApplication $application, $name = null, $class = __CLASS__)
  {
    if(null === $name)
    {
      $name = $application->getName();
    }

    self::$current = $name;
    self::$instances[$name] = new $class();

    if (!self::$instances[$name] instanceof sfContext)
    {
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfContext.', $class));
    }

    self::$instances[$name]->initialize($application);
    
    return self::$instances[$name];
  }
  
  protected function initialize($application)
  {
    $this->application = $application;
    $this->dispatcher  = $application->getEventDispatcher();
    
    $this->logger = sfLogger::getInstance();
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->logger->info('{sfContext} initialization');
    }

    if (sfConfig::get('sf_use_database'))
    {
      // setup our database connections
      $this->databaseManager = new sfDatabaseManager();
      $this->databaseManager->initialize();
    }

    // create a new action stack
    $this->actionStack = new sfActionStack();

    // include the factories configuration
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/factories.yml'));

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));

    sfCore::dispatchEvent('context.load_factories', array('context' => &$this));
  }

  /**
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
   * @param  string    $name   The name of the sfContext to retrieve.
   *
   * @return sfContext An sfContext implementation instance.
   */
  static public function getInstance($name = null)
  {
    if (null === $name)
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
    if (null === $name)
    {
      $name = self::$current;
    }

    return isset(self::$instances[$name]);
  } 
  
  /**
   * Loads the symfony factories.
   */
  public function loadFactories()
  {
    if (sfConfig::get('sf_use_database'))
    {
      // setup our database connections
      $this->factories['databaseManager'] = new sfDatabaseManager($this->application, 
              array('auto_shutdown' => false));
    }

    // create a new action stack
    $this->factories['actionStack'] = new sfActionStack();

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer('Factories');
    }

    // include the factories configuration
    require(sfConfigCache::getInstance()->checkConfig('config/factories.yml'));
      
    $this->dispatcher->notify(new sfEvent('context.load_factories'));

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }
  }
  
  /**
   * Dispatches the current request.
   */
  public function dispatch()
  {
    $this->getController()->dispatch();
  }
  
  /**
   * Sets the current context to something else
   *
   * @param string $name  The name of the context to switch to
   *
   */
  public static function switchTo($name)
  {
    if(!isset(self::$instances[$name]))
    {
      $current = sfContext::getInstance()->getApplication();      
      sfContext::createInstance(sfCore::getProject()->getApplication($name, 
        $current->getEnvironment(), $current->isDebug()));
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
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
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
   *
   * @return sfController The current sfController implementation instance.
   */
   public function getController()
   {
     return $this->controller;
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
    return $this->logger;
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
   *
   * @return mixed A Database instance.
   *
   * @throws sfDatabaseException If the requested database name does not exist.
   */
  public function getDatabaseConnection($name = 'default')
  {
    if ($this->databaseManager != null)
    {
      return $this->databaseManager->getDatabase($name)->getConnection();
    }

    return null;
  }

  public function retrieveObjects($class, $peerMethod, $options = array())
  {
    $retrievingClass = 'sf'.ucfirst(sfConfig::get('sf_orm', 'doctrine')).'DataRetriever';

    return call_user_func(array($retrievingClass, 'retrieveObjects'), $class, $peerMethod, $options);
  }

  /**
   * Retrieve the database manager.
   *
   * @return sfDatabaseManager The current sfDatabaseManager instance.
   */
  public function getDatabaseManager()
  {
    return $this->databaseManager;
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
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return sfConfig::get('sf_app_module_dir').'/'.$lastEntry->getModuleName();
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
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getModuleName();
    }
  }

  /**
   * Retrieve the curretn view instance for this context.
   *
   * @return sfView The currently view instance, if one is set,
   *                otherwise null.
   */
  public function getCurrentViewInstance()
  {
    // get the last action stack entry
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
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
    return $this->request;
  }

  /**
   * Retrieve the response.
   *
   * @return sfResponse The current sfResponse implementation instance.
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Set the response object.
   *
   * @param sfResponse A sfResponse instance.
   *
   * @return void.
   */
  public function setResponse($response)
  {
    $this->response = $response;
  }

  /**
   * Retrieve the storage.
   *
   * @return sfStorage The current sfStorage implementation instance.
   */
  public function getStorage()
  {
    return $this->storage;
  }

  /**
   * Retrieve the view cache manager
   *
   * @return sfViewCacheManager The current sfViewCacheManager implementation instance.
   */
  public function getViewCacheManager()
  {
    return $this->viewCacheManager;
  }

  /**
   * Retrieve the i18n instance
   *
   * @return sfI18N The current sfI18N implementation instance.
   */
  public function getI18N()
  {
    if(!$this->i18n && sfConfig::get('sf_i18n'))
    {
      $this->i18n = sfI18n::getInstance();
      // FIXME: load options from i18n.yml
      $this->i18n->initialize($this, array());
    }

    return $this->i18n;
  }

  /**
   * Retrieve the user.
   *
   * @return sfUser The current sfUser implementation instance.
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    // shutdown all factories
    $this->getUser()->shutdown();
    $this->getStorage()->shutdown();
    $this->getRequest()->shutdown();
    $this->getResponse()->shutdown();

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getLogger()->shutdown();
    }

    if (sfConfig::get('sf_use_database'))
    {
      $this->getDatabaseManager()->shutdown();
    }

    if (sfConfig::get('sf_cache'))
    {
      $this->getViewCacheManager()->shutdown();
    }
  }
}
