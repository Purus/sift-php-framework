<?php

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

  static public function getInstance()
  {
    if(!isset(self::$instance))
    {
      self::$instance = new sfContext();
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

  public function getRequest()
  {
    if(!$this->request)
    {
      $this->request = new sfWebRequest();
      $this->request->initialize($this);
    }

    return $this->request;
  }

  public function getResponse()
  {
    if(!$this->response)
    {
      $this->response = new sfWebResponse();
      $this->response->initialize($this);
    }

    return $this->response;
  }

  public function getStorage()
  {
    if(!$this->storage)
    {
      $this->storage = sfStorage::newInstance('sfSessionTestStorage');
      $this->storage->initialize($this);
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
      $this->user = new sfBasicSecurityUser();
      $this->user->initialize($this);
    }
    return $this->user;
  }

  public function getController()
  {
    if(!$this->controller)
    {
      $this->controller = new sfFrontWebController();
      $this->controller->initialize($this);
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

}
