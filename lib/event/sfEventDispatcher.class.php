<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfEventDispatcher implements a dispatcher object.
 *
 * @package    Sift
 * @subpackage event
 */
class sfEventDispatcher {

  protected
    $listeners = array();

  /**
   * Connects a listener to a given event name.
   *
   * @param string  $name      An event name
   * @param mixed   $listener  A PHP callable
   * @param integer $priority  Priority  
   *
   * @return sfEventDispatcher
   */
  public function connect($name, $listener, $priority = 10)
  {
    if(!isset($this->listeners[$name]))
    {
      $this->listeners[$name] = array();
    }

    if(!isset($this->listeners[$name][$priority]))
    {
      $this->listeners[$name][$priority] = array();
    }

    $this->listeners[$name][$priority][] = $listener;
    
    return $this;
  }

  /**
   * Disconnects a listener for a given event name.
   *
   * @param string   $name      An event name
   * @param mixed    $listener  A PHP callable
   *
   * @return mixed false if listener does not exist, true otherwise
   */
  public function disconnect($name, $listener)
  {
    if(!isset($this->listeners[$name]))
    {
      return false;
    }

    foreach($this->listeners[$name] as $priority => $listeners)
    {
      foreach($listeners as $index => $callable)
      {
        if($listener === $callable)
        {
          unset($this->listeners[$name][$priority][$index]);
          return true;          
        }
      }
    }

    return false;
  }

  /**
   * Notifies all listeners of a given event.
   *
   * @param myEvent $event A myEvent instance
   *
   * @return myEvent The myEvent instance
   */
  public function notify($event)
  {
    foreach($this->getListeners($event->getName()) as $priority => $listeners)
    {
      foreach($listeners as $listener)
      {
        call_user_func($listener, $event);        
      }
    }
    return $event;
  }

  /**
   * Notifies all listeners of a given event until one returns a non null value.
   *
   * @param  myEvent $event A myEvent instance
   *
   * @return myEvent The myEvent instance
   */
  public function notifyUntil($event)
  {
    foreach($this->getListeners($event->getName()) as $priority => $listeners)
    {
      foreach($listeners as $listener)
      {
        if(call_user_func($listener, $event))
        {
          $event->setProcessed(true);
          break 2;
        }
      }
    }
    return $event;
  }

  /**
   * Filters a value by calling all listeners of a given event.
   *
   * @param  myEvent  $event   A myEvent instance
   * @param  mixed    $value   The value to be filtered
   *
   * @return myEvent The myEvent instance
   */
  public function filter($event, $value)
  {
    foreach($this->getListeners($event->getName()) as $priority => $listeners)
    {
      foreach($listeners as $listener)
      {
        $value = call_user_func_array($listener, array($event, $value));
      }
    }
    $event->setReturnValue($value);
    return $event;
  }

  /**
   * Returns true if the given event name has some listeners.
   *
   * @param  string   $name    The event name
   *
   * @return Boolean true if some listeners are connected, false otherwise
   */
  public function hasListeners($name)
  {
    if(!isset($this->listeners[$name]))
    {
      $this->listeners[$name] = array();
    }

    return (boolean) count($this->listeners[$name]);
  }

  /**
   * Returns all listeners associated with a given event name.
   *
   * @param  string   $name    The event name
   *
   * @return array  An array of listeners
   */
  public function getListeners($name)
  {
    if(!isset($this->listeners[$name]))
    {
      return array();
    }
    $listeners = $this->listeners[$name];
    // sort by priority
    krsort($listeners);
    return $listeners;
  }

}
