<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRequest provides methods for manipulating client request information such
 * as attributes, errors and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    Sift
 * @subpackage request
 */
abstract class sfRequest {

  const GET    = 'GET';
  const POST   = 'POST';
  const PUT    = 'PUT';
  const DELETE = 'DELETE';
  const HEAD   = 'HEAD';

  /**
   * Skip validation and execution for any request method.
   *
   */
  const NONE = 1;

  protected
    $errors = array(),
    $context = null,
    $method = null,
    $parameterHolder = null,
    $config = null,
    $attributeHolder = null;

  /**
   * Extracts parameter values from the request.
   *
   * @param array An indexed array of parameter names to extract
   *
   * @return array An associative array of parameters and their values. If
   *               a specified parameter doesn't exist an empty string will
   *               be returned for its value
   */
  public function & extractParameters($names)
  {
    $array = array();

    $parameters = & $this->parameterHolder->getAll();
    foreach($parameters as $key => &$value)
    {
      if(in_array($key, $names))
      {
        $array[$key] = & $value;
      }
    }

    return $array;
  }

  /**
   * Retrieves an error message.
   *
   * @param string An error name
   *
   * @return string An error message, if the error exists, otherwise null
   */
  public function getError($name)
  {
    $retval = null;

    if(isset($this->errors[$name]))
    {
      $retval = $this->errors[$name];
    }

    return $retval;
  }

  /**
   * Retrieves an array of error names.
   *
   * @return array An indexed array of error names
   */
  public function getErrorNames()
  {
    return array_keys($this->errors);
  }

  /**
   * Retrieves an array of errors.
   *
   * @return array An associative array of errors
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Retrieves this request's method.
   *
   * @return int One of the following constants:
   *             - sfRequest::GET
   *             - sfRequest::POST
   *             - sfRequest::PUT
   *             - sfRequest::HEAD
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Indicates whether or not an error exists.
   *
   * @param string An error name
   *
   * @return boolean true, if the error exists, otherwise false
   */
  public function hasError($name)
  {
    return array_key_exists($name, $this->errors);
  }

  /**
   * Indicates whether or not any errors exist.
   *
   * @return boolean true, if any error exist, otherwise false
   */
  public function hasErrors()
  {
    return (count($this->errors) > 0);
  }

  /**
   * Initializes this sfRequest.
   *
   * @param sfContext A sfContext instance
   * @param array   An associative array of initialization parameters
   * @param array   An associative array of initialization attributes
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request
   */
  public function initialize($context, $parameters = array(), $attributes = array())
  {
    $this->context = $context;

    // initialize parameter and attribute holders
    $this->parameterHolder = new sfParameterHolder();
    $this->attributeHolder = new sfParameterHolder();

    $this->parameterHolder->add($parameters);
    $this->attributeHolder->add($attributes);
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext Current application context
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Retrieves a new sfRequest implementation instance.
   *
   * @param string A sfRequest implementation name
   *
   * @return sfRequest A sfRequest implementation instance
   *
   * @throws <b>sfFactoryException</b> If a request implementation instance cannot be created
   */
  public static function newInstance($class)
  {
    // the class exists
    $object = new $class();

    if(!($object instanceof sfRequest))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfRequest';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }

  /**
   * Removes an error.
   *
   * @param string An error name
   *
   * @return string An error message, if the error was removed, otherwise null
   */
  public function & removeError($name)
  {
    $retval = null;

    if(isset($this->errors[$name]))
    {
      $retval = & $this->errors[$name];

      unset($this->errors[$name]);
    }

    return $retval;
  }

  /**
   * Sets an error.
   *
   * @param string An error name
   * @param string An error message
   *
   */
  public function setError($name, $message)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfRequest} error in form for parameter "' . $name . '" (with message "' . $message . '")');
    }

    $this->errors[$name] = $message;
  }

  /**
   * Sets an array of errors
   *
   * If an existing error name matches any of the keys in the supplied
   * array, the associated message will be overridden.
   *
   * @param array An associative array of errors and their associated messages
   *
   */
  public function setErrors($errors)
  {
    $this->errors = array_merge($this->errors, $errors);
  }

  /**
   * Sets the request method.
   *
   * @param int One of the following constants:
   *
   * - sfRequest::GET
   * - sfRequest::POST
   * - sfRequest::PUT
   * - sfRequest::DELETE
   * - sfRequest::HEAD
   *
   * @return void
   *
   * @throws sfException - If the specified request method is invalid
   */
  public function setMethod($methodCode)
  {
    $available_methods = array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD, self::NONE);
    if(in_array($methodCode, $available_methods))
    {
      $this->method = $methodCode;
      return;
    }
    // invalid method type
    throw new sfException(sprintf('Invalid request method: %s',  $methodCode));
  }

  /**
   * Retrieves the parameters for the current request.
   *
   * @return sfParameterHolder The parameter holder
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves the attributes holder.
   *
   * @return sfParameterHolder The attribute holder
   */
  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  /**
   * Retrieves an attribute from the current request.
   *
   * @param string Attribute name
   * @param string Default attribute value
   * @param string Namespace for the current request
   *
   * @return mixed An attribute value
   */
  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attributeHolder->get($name, $default, $ns);
  }

  /**
   * Indicates whether or not an attribute exist for the current request.
   *
   * @param string Attribute name
   * @param string Namespace for the current request
   *
   * @return boolean true, if the attribute exists otherwise false
   */
  public function hasAttribute($name, $ns = null)
  {
    return $this->attributeHolder->has($name, $ns);
  }

  /**
   * Sets an attribute for the request.
   *
   * @param string Attribute name
   * @param string Value for the attribute
   * @param string Namespace for the current request
   *
   */
  public function setAttribute($name, $value, $ns = null)
  {
    $this->attributeHolder->set($name, $value, $ns);
  }

  /**
   * Retrieves a paramater for the current request.
   *
   * @param string Parameter name
   * @param string Parameter default value
   * @param string Namespace for the current request
   *
   */
  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  /**
   * Indicates whether or not a parameter exist for the current request.
   *
   * @param string Parameter name
   * @param string Namespace for the current request
   *
   * @return boolean true, if the paramater exists otherwise false
   */
  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  /**
   * Sets a parameter for the current request.
   *
   * @param string Parameter name
   * @param string Parameter value
   * @param string Namespace for the current request
   *
   */
  public function setParameter($name, $value, $ns = null)
  {
    $this->parameterHolder->set($name, $value, $ns);
  }

  /**
   * Returns a request parameter as integer
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return integer
   */
  public function getInt($name, $default = null, $ns = null)
  {
    return sfInputFilters::toInt($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as array of integer values
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return integer
   */
  public function getIntArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toIntArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as float
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return float
   */
  public function getFloat($name, $default = null, $ns = null)
  {
    return sfInputFilters::toFloat($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as arary of float values
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return float
   */
  public function getFloatArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toFloatArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as boolean
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return bool
   */
  public function getBool($name, $default = null, $ns = null)
  {
    return sfInputFilters::toBool($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as array of boolean values
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return bool
   */
  public function getBoolArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toBoolArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as string (with htmlentities applied)
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return string
   */
  public function getString($name, $default = null, $ns = null)
  {
    return sfInputFilters::toString($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as array of strings (with htmlentities applied)
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return string
   */
  public function getStringArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toStringArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as raw string (without htmlentities)
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return string
   */
  public function getRawString($name, $default = null, $ns = null)
  {
    return sfInputFilters::toRawString($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as array of raw strings (without htmlentities)
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return string
   */
  public function getRawStringArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toRawStringArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter as array
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return array
   */
  public function getArray($name, $default = null, $ns = null)
  {
    return sfInputFilters::toArray($this->parameterHolder->get($name, $default, $ns));
  }

  /**
   * Returns a request parameter filtered with given filters
   *
   * @param string $name Name of the parameter
   * @param mixed $default Optional. Default value. Default: NULL
   * @param string $ns Optional. Namespace. Default: NULL
   * @return array
   * @see    sfFilters::filterVar
   */
  public function getFiltered($name, array $filters, $default = null, $ns = null)
  {
    return sfInputFilters::filterVar($this->parameterHolder->get($name, $default, $ns), $filters);
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  abstract function shutdown();

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
    $event = sfCore::getEventDispatcher()->notifyUntil(
      new sfEvent('request.method_not_found', array(
          'method'    => $method,
          'arguments' => $arguments,
          'request'   => $this)));

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
