<?php

class sfCore {

  public static $dispatcher;
  
  public static function hasProject()
  {
    return false;
  }
  
  public static function getEventDispatcher()
  {
    if(!self::$dispatcher)
    {
      self::$dispatcher = new sfEventDispatcher();
    }
    return self::$dispatcher;
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
  
  public static function getCoreHelpers()
  {
    return array('Escaping');
  }

}
