<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Logs to web debug panel
 *
 * @package    Sift
 * @subpackage log
 */
class sfWebDebugLogger extends sfVarLogger implements sfIEventDispatcherAware {

  /**
   * Web debug holder
   *
   * @var sfWebDebug
   */
  protected $webDebug = null;

  /**
   * The event dispatcher
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'web_debug' => array(
      'class' => 'sfWebDebug',
      'options' => array(),
    ),
  );

  /**
   * Contructor
   *
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param array $options Array of options
   */
  public function __construct($options = array(), sfEventDispatcher $dispatcher = null)
  {
    parent::__construct($options);
    $this->dispatcher = $dispatcher;
    $this->setupEvents();
  }

  /**
   * @see sfIEventDispatcherAware
   */
  public function setEventDispatcher(sfEventDispatcher $dispatcher = null)
  {
    $this->dispatcher = $dispatcher;
    $this->setupEvents();
  }

  /**
   * @see sfIEventDispatcherAware
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Initializes the web debug logger.
   *
   * @param array Logger options
   */
  public function setupEvents()
  {
    if(!$this->dispatcher)
    {
      return;
    }

    $this->dispatcher->connect('context.load_factories', array($this, 'listenForLoadFactories'));
    $this->dispatcher->connect('web_debug.filter_content', array($this, 'filterResponseContent'));
    $this->dispatcher->connect('application.render_exception', array($this, 'filterExceptionContent'));
  }

  /**
   * Listens for "context.load_factories" event.
   *
   * @param sfEvent $event
   */
  public function listenForLoadFactories(sfEvent $event)
  {
    $debugClass = $this->getOption('web_debug.class');
    $debugOptions = array_merge(array(
      'request_parameters' => $event['context']->getRequest()->getParameterHolder()->getAll(),
    ), (array)$this->getOption('web_debug.options', array()));


    $this->webDebug = new $debugClass($this, $this->dispatcher, $debugOptions);
  }

  /**
   * Listens to the web_debug.filter_content event.
   *
   * @param  sfEvent $event   The sfEvent instance
   * @param  string  $content The response content
   *
   * @return string  The filtered response content
   */
  public function filterResponseContent(sfEvent $event, $content)
  {
    if(!$this->webDebug)
    {
      return $content;
    }

    $content = str_ireplace('</head>', "<style type=\"text/css\">\n".$this->webDebug->getDebugCss().'</style>' . '</head>', $content);
    $content = str_ireplace('</body>', $this->webDebug->getHtml() . '</body>', $content);
    return $content;
  }

 /**
   * Listens to the application.render_exception event.
   *
   * @param  sfEvent $event   The sfEvent instance
   * @param  string  $content The response content
   *
   * @return string  The filtered response content
   */
  public function filterExceptionContent(sfEvent $event, $content)
  {
    if(!$this->webDebug)
    {
      return $content;
    }

    $content = str_ireplace('</head>', "<style type=\"text/css\">\n".$this->webDebug->getDebugCss().'</style>' . '</head>', $content);
    $content = str_ireplace('</body>', $this->webDebug->getHtml() . '</body>', $content);
    return $content;
  }

}
