<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfShutdownScheduler is an utility class for managing shutdown events
 * inside an application
 *
 * Example:
 *
 * <code>
 * // register function
 * sfShutdownScheduler::getInstance()->register('my_function');
 * // register static method
 * sfShutdownScheduler::getInstance()->register(array('myClass' 'myStaticMethod'));
 * // register object method
 * sfShutdownScheduler::getInstance()->register(array($object, 'myPublicMethod'));
 * // register with arguments
 * sfShutdownScheduler::getInstance()->register('my_function', 'foo', true);
 * </code>
 *
 * @package    Sift
 * @subpackage util
 */
class sfShutdownScheduler implements Countable {

  /**
   * Low priority
   *
   */
  const LOW_PRIORITY = -100;

  /**
   * Default priority
   *
   */
  const DEFAULT_PRIORITY = 10;

  /**
   * High priority
   *
   */
  const HIGH_PRIORITY = 100;

  /**
   * Array to store user callbacks
   *
   * @var array
   */
  private $callbacks = array();

  /**
   * sfShutdownScheduler
   *
   * @var sfShutdownScheduler
   */
  protected static $instance;

  /**
   * Returns an singleton instance of this class.
   *
   * @return sfShutdownScheduler
   */
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new sfShutdownScheduler();
    }
    return self::$instance;
  }

  /**
   * Constructs the object. Automatically registers callRegisteredShutdown()
   * to be executed at script shutdown.
   *
   */
  public function __construct()
  {
    // register self to execute on shutdown
    register_shutdown_function(array($this, 'callRegisteredShutdown'));
  }

  /**
   * Registers shutdown event
   *
   * @param sfCallable|array|string $callback Callable
   * @param array $arguments
   * @param integer $priority
   * @return sfShutdownScheduler
   * @throws InvalidArgumentException If callable is not valid
   */
  public function register($callback, $arguments = array(), $priority = 10)
  {
    if($callback instanceof sfCallable)
    {
      $callback = $callback->getCallable();
    }

    // check the callback
    if(!sfToolkit::isCallable($callback, false, $callableName))
    {
      throw new InvalidArgumentException(sprintf('Invalid callback "%s" given.', $callableName));
    }

    $this->callbacks[$priority][] = array(&$callback, $arguments);
    return $this;
  }

  /**
   * Returns number of registered events
   *
   * @return integer
   */
  public function count()
  {
    $count = 0;
    foreach($this->callbacks as $priority => $callbacks)
    {
      $count += count($this->callbacks[$priority]);
    }
    return $count;
  }

  /**
   * Removes all registered events
   *
   */
  public function clear()
  {
    $this->callbacks = array();
  }

  /**
   * Calls shutdown events. Automatically called in script shutdown.
   * Not intended to be called manually.
   *
   */
  public function callRegisteredShutdown()
  {
    // sort by priority
    krsort($this->callbacks);

    foreach($this->callbacks as $priority => $callbacks)
    {
      foreach($callbacks as $callback)
      {
        list($callback, $arguments) = $callback;
        if($callback instanceof sfCallable)
        {
          call_user_func_array(array($callback, 'call'), $arguments);
        }
        else
        {
          call_user_func_array($callback, $arguments);
        }
      }
    }
    $this->clear();
  }

}
