<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilter provides a way for you to intercept incoming requests or outgoing responses.
 *
 * @package    Sift
 * @subpackage filter
 */
abstract class sfFilter implements sfIFilter
{
  /**
   * Default parameters
   *
   * @var array
   */
  protected $defaultParameters = array(
    'disabled_for' => array()
  );

  /**
   * The context
   *
   * @var sfContext
   */
  protected $context;

  /**
   * The parameter holder
   *
   * @var sfFlatParameterHolder
   */
  protected $parameterHolder;

  /**
   * Array of called filters
   *
   * @var array
   */
  public static $filterCalled = array();

  /**
   * Constructs the filter
   */
  public function __construct()
  {
    $this->parameterHolder = new sfFlatParameterHolder();
  }

  /**
   * Constructor
   *
   * @param sfContext $context The current application context
   * @param array $parameters An associative array of initialization parameters
   */
  public function initialize(sfContext $context, $parameters = array())
  {
    $this->context = $context;
    $this->parameterHolder->clear();
    $this->parameterHolder->add($this->defaultParameters);
    $this->parameterHolder->add($parameters);
  }

  /**
   * Returns true if this is the first call to the sfFilter instance.
   *
   * @return boolean true if this is the first call to the sfFilter instance, false otherwise
   */
  protected function isFirstCall()
  {
    $class = get_class($this);
    if (isset(self::$filterCalled[$class])) {
      return false;
    } else {
      self::$filterCalled[$class] = true;

      return true;
    }
  }

  /**
   * Is this filter disabled? Takes the parameter `disabled_for` and checks
   * the current module/action. See the docs for possible values for `disabled_for`.
   *
   * @param string $module Module name
   * @param string $action Action name
   * @return boolean
   */
  public function isDisabled($module, $action)
  {
    $disabledFor = $this->getParameter('disabled_for', array());

    if (!is_array($disabledFor)) {
      $disabledFor = array($disabledFor);
    }

    $isDisabled = false;
    foreach ($disabledFor as $disabled) {
      // handle special cases
      if (in_array($disabled, array('*', '*/*'))) {
        $isDisabled = true;
        break;
      }

      // intentionally
      @list($disabledModule, $disabledAction) = explode('/', $disabled);

      if($disabledModule == $module &&
         // action is disabled, or action is *
         ($disabledAction == $action || $disabledAction == '*'))
      {
        $isDisabled = true;
        break;
      }
    }

    return $isDisabled;
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The current sfContext instance
   */
  final public function getContext()
  {
    return $this->context;
  }

  /**
   * Gets the parameter holder for this object.
   *
   * @return sfFlatParameterHolder A sfFlatParameterHolder instance
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->get()</code>
   *
   * @param string $name The key name
   * @param string $default The default value
   * @return string The value associated with the key
   * @see sfFlatParameterHolder
   */
  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Returns true if the given key exists in the parameter holder.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->has()</code>
   *
   * @param string $name The key name
   * @return boolean true if the given key exists, false otherwise
   * @see sfFlatParameterHolder
   */
  public function hasParameter($name)
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets the value for the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->set()</code>
   *
   * @param string $name The key name
   * @param string $value The value
   * @see sfFlatParameterHolder
   */
  public function setParameter($name, $value)
  {
    return $this->parameterHolder->set($name, $value);
  }

  /**
   * Log a message.
   *
   * @param string $message The message
   * @param integer $level The log level
   * @param array $context Array of context variables
   */
  protected function log($message, $level = sfILogger::INFO, array $context = array())
  {
    if (sfConfig::get('sf_logging_enabled')) {
      sfLogger::getInstance()->log(sprintf('{%s} %s', get_class($this), $message), $level, $context);
    }
  }

}
