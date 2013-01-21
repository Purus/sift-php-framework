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
class sfWebDebugLogger extends sfVarLogger {

  /**
   * Web debug holder
   * 
   * @var sfWebDebug 
   */
  protected $webDebug = null;

  /**
   * Array of default options
   * 
   * @var array 
   */
  protected $defaultOptions = array(
    'web_debug_class'   => 'sfWebDebug',
    'web_debug_options' => array(        
    )
  );
  
  /**
   * Initializes the web debug logger.
   *
   * @param array Logger options
   */
  public function initialize($options = array())
  {
    if(!sfConfig::get('sf_web_debug'))
    {
      return;
    }
    
    sfCore::getEventDispatcher()->connect('context.load_factories', array($this, 'listenForLoadFactories'));
    sfCore::getEventDispatcher()->connect('web_debug.filter_content', array($this, 'filterResponseContent'));
    sfCore::getEventDispatcher()->connect('application.render_exception', array($this, 'filterExceptionContent'));
    
  }

  /**
   * Listens for "context.load_factories" event.
   * 
   * @param sfEvent $event
   */
  public function listenForLoadFactories(sfEvent $event)
  {    
    $debugClass   = $this->getOption('web_debug_class', 'sfWebDebug');
    $debugOptions = array_merge(array(      
      'request_parameters' => $event['context']->getRequest()->getParameterHolder()->getAll(),
    ), (array)$this->getOption('web_debug_options', array()));
    $this->webDebug = new $debugClass($this, $debugOptions);
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
    if(!sfConfig::get('sf_web_debug'))
    {
      return $content;
    }
    return str_ireplace('</body>', $this->webDebug->getHtml() . '</body>', $content);  
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
    if(!sfConfig::get('sf_web_debug'))
    {
      return $content;
    }
    
    if(!$this->webDebug)
    {
      return $content;
    }
    
    return str_ireplace('</body>', $this->webDebug->getHtml() . '</body>', $content);  
  }  
  
}
