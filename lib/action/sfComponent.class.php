<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfComponent.
 *
 * @package    Sift
 * @subpackage action
 */
abstract class sfComponent {

  private
    $context = null,
    $dispatcher,
    $request = null,
    $response = null,
    $varHolder = null,
    $requestParameterHolder = null;

  /**
   * Execute any application/business logic for this component.
   *
   * In a typical database-driven application, execute() handles application
   * logic itself and then proceeds to create a model instance. Once the model
   * instance is initialized it handles all business logic for the action.
   *
   * A model should represent an entity in your application. This could be a
   * user account, a shopping cart, or even a something as simple as a
   * single product.
   *
   * @return mixed A string containing the view name associated with this action
   */
  abstract function execute();

  /**
   * Gets the module name associated with this component.
   *
   * @return string A module name
   */
  public function getModuleName()
  {
    return $this->getContext()->getModuleName();
  }

  /**
   * Gets the action name associated with this component.
   *
   * @return string An action name
   */
  public function getActionName()
  {
    return $this->getContext()->getActionName();
  }

  /**
   * Initializes this component.
   *
   * @param sfContext The current application context
   * @return boolean true, if initialization completes successfully, otherwise false
   */
  public function initialize(sfContext $context)
  {
    $this->context = $context;
    $this->varHolder = new sfParameterHolder();
    $this->request = $context->getRequest();
    $this->response = $context->getResponse();
    $this->requestParameterHolder = $this->request->getParameterHolder();
    $this->dispatcher = $context->getEventDispatcher();
    return true;
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The current sfContext instance
   */
  public final function getContext()
  {
    return $this->context;
  }

  /**
   * Retrieves the current logger instance.
   *
   * @return sfLogger The current sfLogger instance
   */
  public final function getLogger()
  {
    return sfLogger::getInstance();
  }

  /**
   * Logs a message using the sfILogger object.
   *
   * @param mixed $message String or object containing the message to log
   * @param string $lovel The level of the message
   *               (available levels: emergency, alert, critical, error, warning, notice, info, debug)
   *
   * @see sfILogger
   */
  public function logMessage($message, $level = sfILogger::INFO, array $context = array())
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getLogger()->log($message, $level, $context);
    }
  }

  /**
   * Returns the event dispatcher
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Returns the value of a request parameter.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->get($name)</code>
   *
   * @param  string The parameter name
   *
   * @return string The request parameter value
   */
  public function getRequestParameter($name, $default = null)
  {
    return $this->requestParameterHolder->get($name, $default);
  }

  /**
   * Returns true if a request parameter exists.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getRequest()->getParameterHolder()->has($name)</code>
   *
   * @param  string  The parameter name
   * @return boolean true if the request parameter exists, false otherwise
   */
  public function hasRequestParameter($name)
  {
    return $this->requestParameterHolder->has($name);
  }

  /**
   * Retrieves the current sfRequest object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getRequest()</code>
   *
   * @return sfRequest The current sfRequest implementation instance
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Retrieves the current sfResponse object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getResponse()</code>
   *
   * @return sfResponse The current sfResponse implementation instance
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Retrieves the current sfController object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getController()</code>
   *
   * @return sfController The current sfController implementation instance
   */
  public function getController()
  {
    return $this->getContext()->getController();
  }

  /**
   * Retrieves the current sfUser object.
   *
   * This is a proxy method equivalent to:
   *
   * <code>$this->getContext()->getUser()</code>
   *
   * @return sfUser
   */
  public function getUser()
  {
    return $this->getContext()->getUser();
  }

  /**
   * Sets a variable for the template.
   *
   * @param  string The variable name
   * @param  mixed  The variable value
   */
  public function setVar($name, $value)
  {
    $this->varHolder->set($name, $value);
  }

  /**
   * Gets a variable set for the template.
   *
   * @param  string The variable name
   * @return mixed  The variable value
   */
  public function getVar($name)
  {
    return $this->varHolder->get($name);
  }

  /**
   * Gets the sfParameterHolder object that stores the template variables.
   *
   * @return sfParameterHolder The variable holder.
   */
  public function getVarHolder()
  {
    return $this->varHolder;
  }

  /**
   * Sets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->setVar('name', 'value')</code>
   *
   * @param  string The variable name
   * @param  string The variable value
   *
   * @return boolean always true
   *
   * @see setVar()
   */
  public function __set($key, $value)
  {
    return $this->varHolder->setByRef($key, $value);
  }

  /**
   * Gets a variable for the template.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVar('name')</code>
   *
   * @param  string The variable name
   *
   * @return mixed The variable value
   *
   * @see getVar()
   */
  public function & __get($key)
  {
    return $this->varHolder->get($key);
  }

  /**
   * Returns true if a variable for the template is set.
   *
   * This is a shortcut for:
   *
   * <code>$this->getVarHolder()->has('name')</code>
   *
   * @param  string The variable name
   *
   * @return boolean true if the variable is set
   */
  public function __isset($name)
  {
    return $this->varHolder->has($name);
  }

  /**
   * Removes a variable for the template.
   *
   * This is just really a shortcut for:
   *
   * <code>$this->getVarHolder()->remove('name')</code>
   *
   * @param  string The variable Name
   */
  public function __unset($name)
  {
    $this->varHolder->remove($name);
  }

  /**
   * Sets a flash variable that will be passed to the very next action.
   *
   * @param string $name The name of the flash variable
   * @param string $value The value of the flash variable
   * @param bool $persist true if the flash have to persist for the following request (true by default)
   */
  public function setFlash($name, $value, $persist = true)
  {
    $this->getUser()->setFlash($name, $value, $persist);
  }

  /**
   * Gets a flash variable.
   *
   * @param string $name The name of the flash variable
   * @param string $default The default value returned when named variable does not exist.
   * @return mixed The value of the flash variable
   */
  public function getFlash($name, $default = null)
  {
    return $this->getUser()->getFlash($name, $default);
  }

  /**
   * Returns true if a flash variable of the specified name exists.
   *
   * @param string $name The name of the flash variable
   * @return bool true if the variable exists, false otherwise
   */
  public function hasFlash($name)
  {
    return $this->getUser()->hasFlash($name);
  }

  /**
   * Returns the rendered view presentation of a given module/action.
   *
   * This is a shortcut for
   *
   * <code>$this->getController()->getPresentationFor($module, $action, $viewName)</code>
   *
   * @param  string A module name
   * @param  string An action name
   * @param  string A View class name
   *
   * @return string The generated content
   *
   * @see sfController
   */
  public function getPresentationFor($module, $action, $viewName = null)
  {
    return $this->getController()->getPresentationFor($module, $action, $viewName);
  }

  /**
   * Proxy action method to translate a string
   *
   * @param  string     $string
   * @param  array      $args
   * @param  $catalogue $catalogue
   * @return string
   */
  protected function __($string, $args = array(), $catalogue = 'messages')
  {
    return $this->getI18N()->__($string, $args, $catalogue);
  }

  /**
   * Return sfI18N object instance
   *
   * @return sfI18N
   */
  protected function getI18N()
  {
    return $this->getContext()->getI18N();
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(
            new sfEvent('component.method_not_found', array(
                'method' => $method,
                'arguments' => $arguments,
                'component' => $this)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
