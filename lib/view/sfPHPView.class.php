<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Plain PHP view.
 *
 * @package    Sift
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfPHPView extends sfView
{
  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * Returns variables that will be accessible to the template.
   *
   * @return array Attributes from the template
   */
  protected function getGlobalVars()
  {
    $context = $this->getContext();

    $shortcuts = array(
      'sf_context' => $context,
      'sf_params'  => $context->getRequest()->getParameterHolder(),
      'sf_request' => $context->getRequest(),
      'sf_user'    => $context->getUser(),
      'sf_view'    => &$this,
    );

    if(sfConfig::get('sf_use_flash'))
    {
      $sf_flash = new sfParameterHolder();
      $sf_flash->add($context->getUser()->getAttributeHolder()->getAll('sift/flash'));
      $shortcuts['sf_flash'] = $sf_flash;
    }

    return $shortcuts;
  }

  /**
   * Load core, standard and other helpers to be use in the template.
   *
   */
  protected function loadHelpers()
  {
    static $helpersLoaded = 0;

    if($helpersLoaded)
    {
      return;
    }

    $helpersLoaded    = 1;
    $core_helpers     = sfCore::getCoreHelpers();
    $standard_helpers = sfConfig::get('sf_standard_helpers');
    $helpers = array_unique(array_merge($core_helpers, $standard_helpers, $this->helpers));
    sfLoader::loadHelpers($helpers);
  }

  /**
   * Renders the presentation.
   *
   * @param string Filename
   *
   * @return string File content
   */
  protected function renderFile($_sfFile)
  {
    if(sfConfig::get('sf_debug'))
    {
      $timer = sfTimerManager::getTimer('{sfView} render "'.basename($_sfFile).'"');
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfView} render "'.$_sfFile.'"');
    }

    $this->loadHelpers();

    $_escaping = $this->getEscaping();

    if ($_escaping === false || $_escaping === 'bc')
    {
      $vars = $this->attributeHolder->getAll();
      extract($vars);
    }

    if(sfConfig::get('sf_debug'))
    {
      $timer->addTime();
    }

    if ($_escaping !== false)
    {
      if(sfConfig::get('sf_debug'))
      {
        $timer2 = sfTimerManager::getTimer('{sfView} escaping using "'. $this->getEscapingMethod() .'"');
      }

      $sf_data = sfOutputEscaper::escape($this->getEscapingMethod(), $this->attributeHolder->getAll());

      if ($_escaping === 'both')
      {
        foreach ($sf_data as $_key => $_value)
        {
          ${$_key} = $_value;
        }
      }

      if(sfConfig::get('sf_debug'))
      {
        $timer2->addTime();
      }

    }

    // render
    ob_start();
    ob_implicit_flush(0);
    require($_sfFile);
    $content = ob_get_clean();

    if(sfConfig::get('sf_debug'))
    {
      $timer->addTime();
    }

    return $content;
  }

  /**
   * Retrieves the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function getEngine()
  {
    return null;
  }

  /**
   * Configures template.
   *
   * @return void
   */
  public function configure()
  {
    // store our current view
    $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
    if (!$actionStackEntry->getViewInstance())
    {
      $actionStackEntry->setViewInstance($this);
    }

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/view.yml';
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$viewConfigFile));

    // set template directory
    if (!$this->directory)
    {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Loop through all template slots and fill them in with the results of
   * presentation data.
   *
   * @param string A chunk of decorator content
   *
   * @return string A decorated template
   */
  protected function decorate($content)
  {
    $template = $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate();

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfView} decorate content with "'.$template.'"');
    }

    // set the decorator content as an attribute
    $this->attributeHolder->set('sf_content', $content);

    // render the decorator template and return the result
    return $this->renderFile($template);    
  }

  /**
   * Renders the presentation.
   *
   * When the controller render mode is sfView::RENDER_CLIENT, this method will
   * render the presentation directly to the client and null will be returned.
   *
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise null
   */
  public function render($templateVars = null)
  {
    $context = $this->getContext();

    // get the render mode
    $mode = $context->getController()->getRenderMode();

    if ($mode == sfView::RENDER_NONE)
    {
      return null;
    }

    $retval = null;
    $response = $context->getResponse();
    if (sfConfig::get('sf_cache'))
    {
      $key   = $response->getParameterHolder()->remove('current_key', 'sift/cache/current');
      $cache = $response->getParameter($key, null, 'sift/cache');
      if ($cache !== null)
      {
        $cache  = unserialize($cache);
        $retval = $cache['content'];
        $vars   = $cache['vars'];
        $response->mergeProperties($cache['response']);
      }
    }

    // decorator
    $layout = $response->getParameter($this->moduleName.'_'.$this->actionName.'_layout', null, 'sift/action/view');
    if (false === $layout)
    {
      $this->setDecorator(false);
    }
    else if (null !== $layout)
    {
      $this->setDecoratorTemplate($layout.$this->getExtension());
    }

    // template variables
    if ($templateVars === null)
    {
      $actionInstance   = $context->getActionStack()->getLastEntry()->getActionInstance();
      $templateVars     = $actionInstance->getVarHolder()->getAll();
    }

    // assigns some variables to the template
    $this->attributeHolder->add($this->getGlobalVars());
    $this->attributeHolder->add($retval !== null ? $vars : $templateVars);

    // render template if no cache
    if ($retval === null)
    {
      // execute pre-render check
      $this->preRenderCheck();

      // render template file
      $template = $this->getDirectory().'/'.$this->getTemplate();
      $retval = $this->renderFile($template);

      if (sfConfig::get('sf_cache') && $key !== null)
      {
        $cache = array(
          'content'   => $retval,
          'vars'      => $templateVars,
          'view_name' => $this->viewName,
          'response'  => $context->getResponse(),
        );
        $response->setParameter($key, serialize($cache), 'sift/cache');

        if (sfConfig::get('sf_web_debug'))
        {
          $retval = sfWebDebug::getInstance()->decorateContentWithDebug($key, $retval, true);
        }
      }
    }

    // now render decorator template, if one exists
    if ($this->isDecorator())
    {
      $retval = $this->decorate($retval);
    }

    // render to client
    if ($mode == sfView::RENDER_CLIENT)
    {
      $context->getResponse()->setContent($retval);
    }

    return $retval;
  }

}
