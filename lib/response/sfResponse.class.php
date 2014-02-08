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
 */
abstract class sfResponse extends sfConfigurable implements sfIResponse {

  /**
   * Parameter holder
   *
   * @var sfParameterHolder
   */
  protected $parameterHolder;

  /**
   * Content
   *
   * @var string
   */
  protected $content = '';

  /**
   * The dispatcher instance
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Constructor
   *
   * @param sfEventDispatcher $dispatcher
   * @param array $parameters
   * @param array $options
   * @inject event_dispatcher
   */
  public function __construct(sfEventDispatcher $dispatcher, $parameters = array(), $options = array())
  {
    $this->dispatcher = $dispatcher;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    parent::__construct($options);
  }

  /**
   * Sets the response content
   *
   * @param string Content
   * @return sfResponse
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
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

    $content = $this->dispatcher->filter(new sfEvent('response.filter_content', array(
      'response' => $this
    )), $content)->getReturnValue();

    if(sfConfig::get('sf_logging_enabled'))
    {
      $length = function_exists('mb_strlen') ? mb_strlen($this->content) : strlen($this->content);

      if($length > 255)
      {
        $length = round($length / 1000).' kB';
      }
      
      sfLogger::getInstance()->info(sprintf('{sfResponse} Sending content (%s)', $length));
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
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   * @return mixed The returned value of the called method
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(
               new sfEvent('response.method_not_found',
                 array('method' => $method,
                       'arguments' => $arguments,
                       'component' => $this)
               ));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    return serialize(array($this->content, $this->parameterHolder));
  }

  /**
   * Unserializes a sfResponse instance.
   *
   * @param string $serialized  A serialized sfResponse instance
   */
  public function unserialize($serialized)
  {
    list($this->content, $this->parameterHolder) = unserialize($serialized);
  }

}
