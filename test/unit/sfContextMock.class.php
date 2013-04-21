<?php

class sfContext
{
  private static $instance = null;

  public $controller;
  public $request;
  public $response;
  public $actionStack;
  public $dispatcher;

  static public function getInstance()
  {
    if (!isset(self::$instance))
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
    $storage = sfStorage::newInstance('sfSessionTestStorage');
    $storage->initialize($this);

    return $storage;
  }

  public function getUser()
  {
    static $user;

    if (!$user)
    {
      $user = new sfBasicSecurityUser;
      $user->initialize($this);
    }

    return $user;
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
