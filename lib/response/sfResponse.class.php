<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfResponse provides methods for manipulating client response information such
 * as headers, cookies and content.
 *
 * @package    Sift
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class sfResponse {

  protected
  $parameterHolder = null,
  $context = null,
  $content = '';

  /**
   * Initializes this sfResponse.
   *
   * @param sfContext A sfContext instance
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this Response
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Sets the context for the current response.
   *
   * @param sfContext  A sfContext instance
   */
  public function setContext($context)
  {
    $this->context = $context;
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The application context
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Retrieves a new sfResponse implementation instance.
   *
   * @param string A sfResponse implementation name
   *
   * @return sfResponse A sfResponse implementation instance
   *
   * @throws sfFactoryException If a request implementation instance cannot be created
   */
  public static function newInstance($class)
  {
    // the class exists
    $object = new $class();

    if(!($object instanceof sfResponse))
    {
      // the class name is of the wrong type
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfResponse', $class));
    }

    return $object;
  }

  /**
   * Sets the response content
   *
   * @param string Content
   */
  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * Gets the current response content
   *
   * @return string Content
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Outputs the response content
   */
  public function sendContent()
  {
    $content = $this->getContent();
    $content = sfCore::filterByEventListeners($content, 'response.filter_content', array(
      'response' => &$this
    ));
    
    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfResponse} send content (' . strlen($this->content) . ' o)');
    }
    echo $content;
  }
  
  /**
   * Retrieves the parameters from the current response.
   *
   * @return sfParameterHolder List of parameters
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves a parameter from the current response.
   *
   * @param string A parameter name
   * @param string A default paramter value
   * @param string Namespace for the current response
   *
   * @return mixed A parameter value
   */
  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  /**
   * Indicates whether or not a parameter exist for the current response.
   *
   * @param string A parameter name
   * @param string Namespace for the current response
   *
   * @return boolean true, if the parameter exists otherwise false
   */
  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  /**
   * Sets a parameter for the current response.
   *
   * @param string A parameter name
   * @param string The parameter value to be set
   * @param string Namespace for the current response
   */
  public function setParameter($name, $value, $ns = null)
  {
    $this->parameterHolder->set($name, $value, $ns);
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
                    new sfEvent('response.method_not_found', array(
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
