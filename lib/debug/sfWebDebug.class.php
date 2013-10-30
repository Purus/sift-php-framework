<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebug creates debug information for easy debugging in the browser.
 *
 * @package    Sift
 * @subpackage debug
 */
class sfWebDebug extends sfConfigurable
{
  protected
    $logger     = null,
    $dispatcher = null,
    $context    = null,
    $panels     = array();

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // options which are exported to javascript
    // for `web_debug.js`
    'javascript' => array(
      // option names are Lower-CamelCased
      'htmlValidator' => array(
        // see: http://about.validator.nu/
        'url' => 'http://html5.validator.nu'
      ),
    ),
    // panels configuration
    'panels' => array(
      'current_route' => array(
        'class' => 'sfWebDebugPanelCurrentRoute'
      ),
      'response' => array(
        'class' => 'sfWebDebugPanelResponse'
      ),
      'environment' => array(
        'class' => 'sfWebDebugPanelEnvironment'
      ),
      'cache' => array(
        'class' => 'sfWebDebugPanelCache',
        'condition' => 'sf_cache'
      ),
      'html_validate' => array(
        'class' => 'sfWebDebugPanelHtmlValidate',
        //'enabled' => false
      ),
      'logs' => array(
        'class' => 'sfWebDebugPanelLogs',
        'condition' => 'sf_logging_enabled'
      ),
      'memory' => array(
        'class' => 'sfWebDebugPanelMemory',
      ),
      'time' => array(
        'class' => 'sfWebDebugPanelTimer',
        'condition' => 'sf_debug'
      ),
      'database' => array(
       'class' => 'sfWebDebugPanelDatabase',
       'condition' => 'sf_use_database'
      ),
      'mailer' => array(
        'class' => 'sfWebDebugPanelMailer'
      ),
      'user' => array(
        'class' => 'sfWebDebugPanelUser'
      ),
      'docs' => array(
        'class' => 'sfWebDebugPanelDocumentation'
      )
    ),
    // status map: level to css class
    'status_map' => array(
      sfILogger::EMERGENCY => 'emergency',
      sfILogger::ALERT => 'alert',
      sfILogger::CRITICAL => 'critical',
      sfILogger::ERROR => 'error',
      sfILogger::WARNING => 'warning',
      sfILogger::NOTICE => 'notice',
      sfILogger::INFO => 'info',
      sfILogger::DEBUG => 'debug'
    )
  );

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * request_parameters: The current request parameters
   *
   * @param sfVarLogger       $logger     The logger
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param array             $options    An array of options
   */
  public function __construct(sfVarLogger $logger, sfEventDispatcher $dispatcher, array $options = array())
  {
    // recursive merge with defaults
    $options = array_merge_recursive($this->defaultOptions, $options);

    parent::__construct($options);

    $this->logger = $logger;
    $this->dispatcher = $dispatcher;

    $this->configure();

    // allow extensions
    $this->dispatcher->notify(new sfEvent('web_debug.load_panels', array(
      'web_debug' => $this
    )));

    // context created
    $this->dispatcher->connect('context.instance_created', array($this, 'listenToContextCreatedEvent'));
    // hook for cached content
    $this->dispatcher->connect('view.cache.filter_content', array($this, 'decorateCachedContent'), -99);
  }

  /**
   * Sets the logger
   *
   * @param sfVarLogger $logger
   */
  public function setLogger(sfVarLogger $logger)
  {
    $this->logger = $logger;
  }

  /**
   * Gets the logger.
   *
   * @return sfVarLogger The logger instance
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Returns the context
   *
   * @return sfContext|null
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Listens to "context.instance_created" event
   *
   * @param sfEvent $event
   */
  public function listenToContextCreatedEvent(sfEvent $event)
  {
    $this->context = $event['context'];
  }

  /**
   * Configures the web debug toolbar.
   */
  public function configure()
  {
    foreach(array_keys($this->getOption('panels')) as $panel)
    {
      $options = $this->getOptionsForPanel($panel);

      // panel is not enabled
      if(isset($options['enabled']) && !$options['enabled'])
      {
        continue;
      }
      // condition
      elseif(isset($options['condition']) && !sfConfig::get($options['condition']))
      {
        continue;
      }

      if(!isset($options['class']))
      {
        throw new sfConfigurationException(sprintf('The debug panel "%s" configuration is missing the class option.', $panel));
      }

      $class = $options['class'];
      unset($options['class'], $options['enabled'], $options['condition']);

      $this->setPanel($panel, new $class($this, $options));
    }
  }

  /**
   * Gets the registered panels.
   *
   * @return array The panels
   */
  public function getPanels()
  {
    return $this->panels;
  }

  /**
   * Sets a panel by name.
   *
   * @param string          $name  The panel name
   * @param sfWebDebugPanel $panel The panel
   */
  public function setPanel($name, sfWebDebugPanel $panel)
  {
    $this->panels[$name] = $panel;
  }

  /**
   * Removes a panel by name.
   *
   * @param string $name The panel name
   */
  public function removePanel($name)
  {
    unset($this->panels[$name]);
  }

  /*
   * Returns the web debug toolbar as HTML.
   *
   * @return string The web debug toolbar HTML
   */
  public function getHtml()
  {
    $status = 999;
    foreach($this->panels as $panel)
    {
      $panel->beforeRender();
      $status = min($status, $panel->getStatus());
    }

    // global panel status
    if($status >= 6)
    {
      $status = 'success';
    }
    elseif($status >= 4)
    {
      $status = 'warning';
    }
    else
    {
      $status = 'error';
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->debug('{sfWebDebug} Rendering web debug');
    }

    return $this->render(sfConfig::get('sf_sift_data_dir').'/web_debug/web_debug.php', array(
      'web_debug_id' => 'web-debug',
      'options' => $this->getOptionsForJavascript(),
      'current' => null,
      'panels' => $this->panels,
      'web_debug' => $this,
      'status' => $status
    ));
  }

  /**
   * Returns options for panel
   *
   * @param string $panel
   * @return array
   */
  protected function getOptionsForPanel($panel)
  {
    return array_merge((array)$this->getOption(sprintf('panels.%s', $panel), array()), array(
      'template_dir' => sfConfig::get('sf_sift_data_dir') . '/web_debug'
    ));
  }

  /**
   * Returns an array of options for javascript.
   * See `web_debug.js` for more information.
   *
   * @return array
   */
  protected function getOptionsForJavascript()
  {
    return $this->getOption('javascript', array());
  }

  /**
   * Returns the CSS rules for the debug bar
   *
   * @return string
   */
  public function getDebugCss()
  {
    return file_get_contents(sfConfig::get('sf_sift_data_dir'). '/web_debug/web_debug.min.css');
  }

  /**
   * Returns the javascript
   *
   * @return string
   */
  public function getDebugJavascript()
  {
    $js = array();
    $js[] = file_get_contents(sfConfig::get('sf_sift_data_dir'). '/web_debug/web_debug.min.js');
    foreach($this->panels as $panel)
    {
      if($panelJs = $panel->getPanelJavascript())
      {
        $js[] = $panelJs;
      }
    }
    return  join("\n", $js);
  }

  /**
   * Loads helpers needed for the web debug toolbar.
   */
  protected function loadHelpers()
  {
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));
  }

 /**
   * Decorates a chunk of HTML with cache information.
   *
   * @param sfEvent The event
   * @param string The content
   * @return string The decorated HTML string
   */
  public function decorateCachedContent(sfEvent $event, $content)
  {
    if(!$content || false === strpos($event['response']->getContentType(), 'html'))
    {
      return $content;
    }

    // we are caching whole layout, do nothing here
    if(isset($event['with_layout']))
    {
      return $content;
    }

    $cache = $event['view_cache_manager'];

    $this->loadHelpers();

    return $this->render(sfConfig::get('sf_sift_data_dir'). '/web_debug/cache/_cache_fragment.php', array(
      'id' => dechex(crc32($event['uri'])),
      'class' => $event['new'] ? 'new' : 'old',
      'new' => $event['new'],
      'lifetime' => $cache->getLifeTime($event['uri'], true),
      'uri' => $event['uri'],
      'content' => $content,
      // human readable format
      'last_modified' => $cache->getLastModified($event['uri'], true, time())
    ));
  }

  /**
   * Converts the log level value to a string.
   *
   * @param integer The level value
   * @return string The status class
   */
  public function getStatusClass($value)
  {
    return $this->getOption(sprintf('status_map.%s', $value), $value);
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
   * Renders the template with given arguments
   *
   * @param string $template The path to a template
   * @param array $variables Array of variables
   * @return string
   */
  public function render(/*$template, array $variables = null*/)
  {
    return call_user_func_array(array('sfLimitedScope', 'render'), func_get_args());
  }

}
