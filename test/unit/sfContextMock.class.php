<?php

class sfContext
{
  private static $instance = null;

  public $controller;
  public $request;
  public $response;
  public $actionStack;
  
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
  
}

class sfCore {

  public static function getEventDispatcher()
  {
    static $dispatcher;
    if(!$dispatcher)
    {
      $dispatcher = new sfEventDispatcher();
    }
    return $dispatcher;
  }

  /**
   * Dispatches an event
   *
   * @param string $name
   * @param array $data
   */
  public static function dispatchEvent($name, $data = array())
  {
    return self::getEventDispatcher()->notify(new sfEvent($name, $data));
  }
  
  /**
   * Filters variable using event dispatcher. Dispatches
   * an event with given name and parameters passed.
   * Returns modified (is any listener touched it) value
   *
   * @param mixed $value
   * @param string $eventName
   * @param array $params Params for the event
   */
  public static function filterByEventListeners(&$value, $eventName,
          $params = array())
  {
    $event = new sfEvent($eventName, $params);
    self::getEventDispatcher()->filter($event, $value);
    return $event->getReturnValue();
  }
  
}
