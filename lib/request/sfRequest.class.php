<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRequest provides methods for manipulating client request information such
 * as attributes and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    Sift
 * @subpackage request
 */
abstract class sfRequest implements sfIRequest, Serializable
{
    /**
     * Protected namespace. Request parameters which are
     * prefixed with _sf_ should be there
     *
     */
    const PROTECTED_NAMESPACE = 'sift/request/protected';

    /**
     * Request content
     *
     * @var string
     */
    protected $content;

    /**
     * Request method
     *
     * @var string
     */
    protected $method;

    /**
     * Parameter holder
     *
     * @var sfParameterHolder
     */
    protected $parameterHolder;

    /**
     * Attribute holder
     *
     * @var sfParameterHolder
     */
    protected $attributeHolder;

    /**
     * The dispatcher instance
     *
     * @var sfEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param sfEventDispatcher $dispatcher The dispatcher
     * @param array             $parameters Array of parameters
     * @param array             $attributes Array of attributes
     *
     * @inject event_dispatcher
     */
    public function __construct(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
    {
        $this->setEventDispatcher($dispatcher);

        // initialize parameter and attribute holders
        $this->parameterHolder = new sfParameterHolder();
        $this->attributeHolder = new sfParameterHolder();

        $this->parameterHolder->add($parameters);
        $this->attributeHolder->add($attributes);
    }

    /**
     * Extracts parameter values from the request.
     *
     * @param array An indexed array of parameter names to extract
     *
     * @return array An associative array of parameters and their values. If
     *               a specified parameter doesn't exist an empty string will
     *               be returned for its value
     */
    public function &extractParameters($names)
    {
        $array = array();
        $parameters = & $this->parameterHolder->getAll();
        foreach ($parameters as $key => &$value) {
            if (in_array($key, $names)) {
                $array[$key] = & $value;
            }
        }

        return $array;
    }

    /**
     * @see sfIRequest
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @see sfIRequest
     */
    public function setMethod($methodCode)
    {
        $available_methods = array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD);
        if (in_array($methodCode, $available_methods)) {
            $this->method = $methodCode;

            return $this;
        } else {
            // invalid method type
            throw new sfException(sprintf('Invalid request method: %s', $methodCode));
        }
    }

    /**
     * @see sfIRequest
     */
    public function getContent()
    {
        if (null === $this->content) {
            if (0 === strlen(trim($this->content = file_get_contents('php://input')))) {
                $this->content = false;
            }
        }

        return $this->content;
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
     * @return mixed The parameter value
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
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return integer
     */
    public function getInt($name, $default = null, $ns = null)
    {
        return sfInputFilters::toInt($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as array of integer values
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return integer
     */
    public function getIntArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toIntArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as float
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return float
     */
    public function getFloat($name, $default = null, $ns = null)
    {
        return sfInputFilters::toFloat($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as arary of float values
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return float
     */
    public function getFloatArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toFloatArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as boolean
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return bool
     */
    public function getBool($name, $default = null, $ns = null)
    {
        return sfInputFilters::toBool($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as array of boolean values
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return bool
     */
    public function getBoolArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toBoolArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as string (with htmlentities applied)
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return string
     */
    public function getString($name, $default = null, $ns = null)
    {
        return sfInputFilters::toString($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as array of strings (with htmlentities applied)
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return string
     */
    public function getStringArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toStringArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as raw string (without htmlentities)
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return string
     */
    public function getRawString($name, $default = null, $ns = null)
    {
        return sfInputFilters::toRawString($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as array of raw strings (without applying htmlentities)
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return string
     */
    public function getRawStringArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toRawStringArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter as array
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return array
     */
    public function getArray($name, $default = null, $ns = null)
    {
        return sfInputFilters::toArray($this->parameterHolder->get($name, $default, $ns));
    }

    /**
     * Returns a request parameter filtered with given filters
     *
     * @param string $name    Name of the parameter
     * @param mixed  $default Optional. Default value. Default: NULL
     * @param string $ns      Optional. Namespace. Default: NULL
     *
     * @return array
     * @see    sfFilters::filterVar
     */
    public function getFiltered($name, array $filters, $default = null, $ns = null)
    {
        return sfInputFilters::filterVar($this->parameterHolder->get($name, $default, $ns), $filters);
    }

    /**
     * Calls methods defined via sfEventDispatcher.
     *
     * @param string $method    The method name
     * @param array  $arguments The method arguments
     *
     * @return mixed The returned value of the called method
     *
     * @throws sfException If called method is undefined
     */
    public function __call($method, $arguments)
    {
        $event = $this->dispatcher->notifyUntil(
            new sfEvent('request.method_not_found', array(
                'method'    => $method,
                'arguments' => $arguments,
                'request'   => $this
            ))
        );

        if (!$event->isProcessed()) {
            throw new sfException(sprintf('Call to undefined method %s::%s', get_class($this), $method));
        }

        return $event->getReturnValue();
    }

    /**
     * Returns true if the request parameter exists (implements the ArrayAccess interface).
     *
     * @param  string $name The name of the request parameter
     *
     * @return Boolean true if the request parameter exists, false otherwise
     */
    public function offsetExists($name)
    {
        return $this->hasParameter($name);
    }

    /**
     * Returns the request parameter associated with the name (implements the ArrayAccess interface).
     *
     * @param  string $name The offset of the value to get
     *
     * @return mixed The request parameter if exists, null otherwise
     */
    public function offsetGet($name)
    {
        return $this->getParameter($name, false);
    }

    /**
     * Sets the request parameter associated with the offset (implements the ArrayAccess interface).
     *
     * @param string $offset The parameter name
     * @param string $value  The parameter value
     */
    public function offsetSet($offset, $value)
    {
        $this->setParameter($offset, $value);
    }

    /**
     * Removes a request parameter.
     *
     * @param string $offset The parameter name
     */
    public function offsetUnset($offset)
    {
        $this->getParameterHolder()->remove($offset);
    }

    /**
     * Serializes the request
     *
     * @return string
     */
    public function serialize()
    {
        $vars = get_object_vars($this);
        // we don't serialize the dispatcher!
        unset($vars['dispatcher']);

        return (string)@serialize($vars);
    }

    /**
     * Unserializes the request
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $vars = unserialize($serialized);
        foreach ($vars as $var => $value) {
            $this->$var = $value;
        }
    }

    /**
     * Clone the object
     */
    public function __clone()
    {
        $this->parameterHolder = clone $this->parameterHolder;
        $this->attributeHolder = clone $this->attributeHolder;
    }

    /**
     * Sets the dispatcher
     *
     * @param sfEventDispatcher $dispatcher
     */
    public function setEventDispatcher(sfEventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns the dispatcher
     *
     * @return sfEventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Dispatches an event using the dispatcher
     *
     * @param sfEvent $event
     */
    protected function dispatchEvent(sfEvent $event)
    {
        $this->dispatcher->notify($event);
    }

}
