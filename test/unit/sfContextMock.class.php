<?php

class sfDummyApplication extends sfApplication {

}

class sfContext {

  private static $instance = null;
  public $user;
  public $controller;
  public $request;
  public $response;
  public $actionStack;
  public $dispatcher;
  public $storage;
  public $i18n;
  public $serviceContainer;

  static public function getInstance()
  {
    if(!isset(self::$instance))
    {
      self::$instance = new sfContext(new sfDummyApplication('test'));
    }

    return self::$instance;
  }

  static public function hasInstance()
  {
    return isset(self::$instance);
  }

  public function getModuleName()
  {
    return '';
  }

  public function getActionName()
  {
    return '';
  }

  public function getRequest()
  {
    if(!$this->request)
    {
      $this->request = new sfWebRequest($this->getEventDispatcher());
    }

    return $this->request;
  }

  public function getResponse()
  {
    if(!$this->response)
    {
      $this->response = new sfWebResponse($this->getEventDispatcher());
    }

    return $this->response;
  }

  public function getStorage()
  {
    if(!$this->storage)
    {
      $this->storage = new sfSessionTestStorage(array('session_path' => sys_get_temp_dir()));
    }

    return $this->storage;
  }

  public function getI18n()
  {
    if(!$this->i18n)
    {
      $this->i18n = new sfI18n($this);
    }
    return $this->i18n;
  }

  public function getUser()
  {
    if(!$this->user)
    {
      $this->user = new sfBasicSecurityUser($this->getEventDispatcher(), $this->getStorage(), $this->getRequest());
    }
    return $this->user;
  }

  public function getController()
  {
    if(!$this->controller)
    {
      $this->controller = new sfFrontWebController($this);
    }

    return $this->controller;
  }

  public function getViewCacheManager()
  {
    return false;
  }

  public function getActionStack()
  {
    return $this->actionStack;
  }

  public function getEventDispatcher()
  {
    if(!$this->dispatcher)
    {
      $this->dispatcher = new sfEventDispatcher();
    }
    return $this->dispatcher;
  }

  public function getServiceContainer()
  {
    if(!$this->serviceContainer)
    {
      $this->serviceContainer = new sfServiceContainer();
      $this->serviceContainer->set('context', $this);
    }
    return $this->serviceContainer;
  }

  public function getService($name)
  {
    switch($name)
    {
      case 'less_compiler':
        return new sfLessCompiler($this->getEventDispatcher(), array(
          'cache_dir' => sfConfig::get('sf_cache_dir')
        ));
      break;

      default:
        throw new InvalidArgumentException('Service not present in the mock');
    }
  }

}
