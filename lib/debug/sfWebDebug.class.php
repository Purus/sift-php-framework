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
    $panels     = array();
  
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * request_parameters: The current request parameters
   *
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param sfVarLogger       $logger     The logger
   * @param array             $options    An array of options
   */
  public function __construct(sfVarLogger $logger, array $options = array())
  {
    parent::__construct($options);
    
    $this->logger = $logger;
    
    $this->configure();

    // allow extensions
    sfCore::dispatchEvent('web_debug.load_panels', array(
      'web_debug' => &$this 
    ));

    // hook for cached content
    sfCore::getEventDispatcher()->connect('view.cache.filter_content', array(
       $this, 'decorateCachedContent'
    ));    
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
   * Configures the web debug toolbar.
   */
  public function configure()
  {
    $this->setPanel('sift_version', new sfWebDebugPanelSiftVersion($this));
    
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $this->setPanel('cache', new sfWebDebugPanelCache($this));
    }
    
    $this->setPanel('current_route', new sfWebDebugPanelCurrentRoute($this));        
    $this->setPanel('response', new sfWebDebugPanelResponse($this));
    
    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->setPanel('config', new sfWebDebugPanelConfig($this));
    }
    
    $this->setPanel('logs', new sfWebDebugPanelLogs($this));    
    $this->setPanel('memory', new sfWebDebugPanelMemory($this));
    
    if(sfConfig::get('sf_debug'))
    {
      $this->setPanel('time', new sfWebDebugPanelTimer($this));
    }
    
    if(sfConfig::get('sf_use_database'))
    {
      $this->setPanel('database', new sfWebDebugPanelDatabase($this));
    }
    
    $this->setPanel('mailer', new sfWebDebugPanelMailer($this));
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
    $current = isset($this->options['request_parameters']['sf-web-debug-panel']) ? 
                     $this->options['request_parameters']['sf-web-debug-panel'] : null;
    
    $current = false;
    $titles = array();
    $panels = array();
    
    foreach ($this->panels as $name => $panel)
    {
      if($title = $panel->getTitle())
      {
        if(($content = $panel->getPanelContent()) || $panel->getTitleUrl())
        {
          $id = sprintf('sfWebDebug%sDetails', $name);
          $titles[] = sprintf('<li%s><a title="%s" href="%s"%s>%s %s</a></li>',
            $panel->getStatus() ? ' class="sfWebDebug'.ucfirst($this->getPriority($panel->getStatus())).'"' : '',
            $panel->getPanelTitle(),
            $panel->getTitleUrl() ? $panel->getTitleUrl() : '#',
            $panel->getTitleUrl() ? '' : ' onclick="sfWebDebugShowDetailsFor(\''.$id.'\'); return false;"',
            $panel->getIcon(),
            $title
          );
          $panels[] = sprintf('<div id="%s" class="sfWebDebugTop" style="display:%s"><h1>%s</h1>%s</div>',
            $id,
            $name == $current ? 'block' : 'none',
            $panel->getPanelTitle(),
            $content
          );
        }
        else
        {
          $titles[] = sprintf('<li>%s</li>', $title);
        }
      }
    }
    
    $html = (
'<div id="sfWebDebug">
  <div id="sfWebDebugBar">
    <a href="#" onclick="sfWebDebugToggleMenu(); sfWebDebugToggleCookie();return false;"><img src="'.$this->getLogoSrc().'" alt="Debug toolbar" /></a>

          <ul id="sfWebDebugDetails" class="sfWebDebugMenu">
            '.implode("\n", $titles).'
            <li class="last">
              <a href="#" onclick="document.getElementById(\'sfWebDebug\').style.display=\'none\'; return false;">close</a>
            </li>
          </ul>
        </div>

        '.implode("\n", $panels).'
</div>
<style type="text/css">
'.$this->getDebugCss().'
</style>
<script type="text/javascript">
'.$this->getDebugJavascript().'
</script>
    ');
    
    return $html;
  }
  
  protected function getLogoSrc()
  {
    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAUCAYAAABmvqYOAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAQjSURBVHjahFVbTFxVFF0zd+4wUBhwOtIqRDqgQtsptAVEbCBFQqv1QawkCGnVD7EmrTVGkyZ8+Gw0NdUEW/Wj/fGd1lhDfMTY2qHWEB9IBdIKodBaKa9Oh8c8C3MH1zlzhzd6kjVz7z737L3P2mufY2gsx9xhmH5Sia1EIVFCODiXyP8Q0Uc0E78T3xOjWGSYFrEZiaeJOmLjnJmp6QTS+Xu3/tZLfEy8TXjnO5o90vVMPljgeH6Qqem3TOJlfSf5SznPIU5zUQUiXKvNQPqLRDFnRGbshFOuB7bMd76cOE7Hd4QDgDbBCVWFYrFAiYvDVFhnw2hARH8OB+mbgRXVLL8xqpJhK/E54ZzN+Vt0vE5k6Xh4J9LKa5GUsZoLLPD3d6PltWqsf/EozNblaNlfi/HeHmQ8UANH5W4krMiAdiOAa62ncO7gbgaCjf6OEJuF8w3ETo0ayH78BazZdRBayI/r7T8xy0n4+3pxa+mjSC28X2aRVrqdQb9Bfv1nCLn74G5rQnr5Dvj6uiF80Dn0YleamPFDhGpQuLC8hhRMoKkuD9fbemCi8CKTQGpBAbSgDwZS5b3Sidu2PiE9XP76fVw98wXSymqhJiThppy1CAx1sk6aUFS14LxCaFtw6b10HgaTmTt4CalFBeSY3HNvnvMtcD3lxMnHskmNDbdX75PVzHlyP1YWPciMvbCtK0XJIRfrtCxW+FylxoE3+JAoHHk6mmFKsOIW0pBVtRf29SWITPi45U5kbd8De95m/HPqE1hSboY1Kw8XjuzDyIVm8r8Lo12/4dyBOgSHL0VlZIBmjClG0BLyDKP1zWeYYSY6Gp5FclYu7nr9S3JeyYBVyKzag+BAPwvaITvJ3XaGawa51oQbngEM/XKWtIZjXS7yZSvrTRHTdHB4BO3vHkbXh6/Id+sqJ8L+MYQDXtImZGqWdiUunh6iahYBZD/MNJdPFPS07EYabc4NUJclM8gUzCmpcDyyV341evFP1mDbgkY1KAozdyNCKdrWFCPt3gqMdLZg0j8i0u4WYb+TWRP59R/hnndc2NTQhMJXj8NiW4m/jtZj8OdvEZdsl8WSnWdSo5mb4xEYGEf74ec4l4jiAz9IqWpBOX3C0FgmGWoUkrRvLIM5yS450iZCGOtuhe9KP4tMORbdJ2kYOPsVklathjUzF+52cn5tUMo1+c5sNt5ajF1shf/q5S7SVyyci5FB/MomWBHjXYQ0mqNSFEM0iKDTFB/VvpCuaBhRAzERs3HNpFGVR7Ur1v5/iy5VLDghZLnYQajEzzrt1Gjg2cewTCRqe5421/xT8aR+OfQsrBz+e0TnPcQO4r2lznNxJm8iDhFjSzqeaxPn5DH9tvr0/26iIUJosIFOtum7ERdCCmGmTTgb16+6H/XL5Y/FNvSvAAMAwfJtjNjgGVYAAAAASUVORK5CYII=';
  }
   
  protected function getDebugJavascript()         
  {
    $js = array();    
    $js[] = file_get_contents(sfConfig::get('sf_sift_data_dir'). '/data/web_debug.js');
    
    foreach($this->panels as $panel)
    {
      if($panelJs = $panel->getPanelJavascript())
      {
        $js[] = $panelJs;
      }      
    }
    
    return sprintf("/* <![CDATA[ */\n%s/* ]]> */", join("\n", $js));
  }    
  
  /**
   * Loads helpers needed for the web debug toolbar.
   */
  protected function loadHelpers()
  {
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));
  }
  
  /**
   * Gets the stylesheet code to inject in the head tag.
   *
   * @param string The stylesheet code
   */
  public function getDebugCss()
  {
    $css = array();
    
    $css[] = file_get_contents(sfConfig::get('sf_sift_data_dir'). '/data/web_debug.css');
    
    foreach($this->panels as $panel)
    {
      if($panelCss = $panel->getPanelCss())
      {
        $css[] = $panelCss;
      }
    }
    
    return join("\n", $css);
  }
  
 /**
   * Decorates a chunk of HTML with cache information.
   *
   * @param string  The internalUri representing the content
   * @param string  The HTML content
   * @param boolean true if the content is new in the cache, false otherwise
   *
   * @return string The decorated HTML string
   */
  public function decorateCachedContent(sfEvent $event, $content)
  {
    // we are caching whole layout, do nothing here
    if(isset($event['with_layout']))
    {
      return $content;
    }

    $internalUri = $event['uri'];
    $new = $event['new'];
    
    $context = sfContext::getInstance();
    
    // don't decorate if not html or if content is null
    if (!sfConfig::get('sf_web_debug') || 
        !$content || false === strpos($context->getResponse()->getContentType(), 'html'))
    {
      return $content;
    }

    $cache = $context->getViewCacheManager();
    $this->loadHelpers();
    $class = $new ? 'new' : 'old';
    $last_modified = $cache->getLastModified($internalUri);
    
    $id = md5($internalUri);

    $template = '<div id="sf_cache_%id%" class="sf-web-debug-action-cache %class%">
      <div id="sf_cache_sub_%id%" class="sf-web-debug-cache %class%">
      <div>
        <a href="#" onclick="sfWebDebugToggle(\'sf_cache_info_%id%\');return false;" class="sf-cache-information-link">
        Cache information
        </a>
        <a href="#" onclick="sfWebDebugToggle(\'sf_cache_sub_%id%\'); document.getElementById(\'sf_cache_%id%\').className = \'hidden\'; return false;" class="sf-cache-close"><span>close</span></a>
      </div>
      <div style="display:none;" id="sf_cache_info_%id%" class="sf-web-debug-cache-info">
        <ul>
          <li><strong>Uri:</strong> %uri%</li>
          <li><strong>Lifetime:</strong> %lifetime%s</li>
          <li><strong>Last modified:</strong> %last_modified%s</li>
        </ul>
      </div>
      </div>
      <div class="sf-cache-content">
      %content%
      </div>
      </div>';
    
    // return strtr(($this->getOption('cached_content_template')), array(
    return strtr($template, array(
      '%id%'      => $id,
      '%class%'   => $class,
      '%lifetime%'  => $cache->getLifeTime($internalUri),  
      '%uri%'  => $internalUri,
      '%content%' => $content,
      '%last_modified%' => (time() - $last_modified)          
    ));    
  }
  
  /**
   * Converts a priority value to a string.
   *
   * @param integer The priority value
   *
   * @return string The priority as a string
   */
  public function getPriority($value)
  {
    if ($value >= 6)
    {
      return 'info';
    }
    else if ($value >= 4)
    {
      return 'warning';
    }
    else
    {
      return 'error';
    }
  }
  
}
