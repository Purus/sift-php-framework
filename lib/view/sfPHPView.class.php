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
 */
class sfPHPView extends sfView
{
  /**
   * Helpers loaded?
   *
   * @var boolean
   */
  private static $helpersLoaded = false;

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
      'sf_response' => $context->getResponse(),
      'sf_view'    => $this,
    );

    if (sfConfig::get('sf_use_flash')) {
      $sf_flash = new sfParameterHolder();
      $sf_flash->add($context->getUser()->getAttributeHolder()->getAll(sfUser::FLASH_NAMESPACE));
      $shortcuts['sf_flash'] = $sf_flash;
    }

    return $shortcuts;
  }

  /**
   * Load core, standard and other helpers to be use in the template.
   *
   * @return void
   */
  protected function loadHelpers()
  {
    if (self::$helpersLoaded) {
      return;
    }

    $helpers = array_unique(array_merge(sfCore::getCoreHelpers(), sfConfig::get('sf_standard_helpers', array()), $this->helpers));
    sfLoader::loadHelpers($helpers);
    self::$helpersLoaded = true;
  }

  /**
   * Renders the presentation.
   *
   * @param string $file Filename
   * @return string File content
   */
  protected function renderFile($file)
  {
    if (sfConfig::get('sf_logging_enabled')) {
      $this->getContext()->getLogger()->log('{sfView} Render "{file}"', array('file' => $file));
    }

    $this->loadHelpers();

    // EXTR_REFS can't be used (see #3595 and #3151)
    $vars = $this->getVariables();

    // render
    ob_start();
    ob_implicit_flush(0);

    try {
      sfLimitedScope::load($file, $vars);
    } catch (Exception $e) {
      // need to end output buffering before throwing the exception #7596
      ob_end_clean();
      throw $e;
    }

    return ob_get_clean();
  }

  /**
   * Returns an array of variable to be accessible to template
   *
   * @return array
   */
  protected function getVariables()
  {
    $attributes = array();

    // filter by event system
    $parameters = sfCore::filterByEventListeners($this->attributeHolder->getAll(),
            'view.template.variables', array(
            'view' => $this
    ));

    if ($this->getEscaping()) {
      $attributes['sf_data'] = sfOutputEscaper::escape($this->getEscapingMethod(), $parameters);
      foreach ($attributes['sf_data'] as $key => $value) {
        $attributes[$key] = $value;
      }
    } else {
      $attributes = $parameters;
      $attributes['sf_data'] = sfOutputEscaper::escape(ESC_RAW, $parameters);
    }

    return $attributes;
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

    if (!$actionStackEntry->getViewInstance()) {
      $actionStackEntry->setViewInstance($this);
    }

    // require our configuration
    require(sfConfigCache::getInstance()->checkConfig(
      sfConfig::get('sf_app_module_dir_name').'/'.$this->moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/view.yml')
    );

    // set template directory
    if (!$this->directory) {
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

    if (sfConfig::get('sf_logging_enabled')) {
      $this->getContext()->getLogger()->info('{sfView} Decorate content with "{template}".', array('template' => $template));
    }

    // set the decorator content as an attribute
    $this->attributeHolder->set('sf_content', $content);

    // render the decorator template and return the result
    return $this->renderFile($template);
  }

  /**
   * @see sfView
   */
  public function render($templateVars = array())
  {
    $context = $this->getContext();
    // get the render mode
    $mode = $context->getController()->getRenderMode();

    if ($mode == sfView::RENDER_NONE) {
      return null;
    }

    $uri = null;
    $retval = null;
    $response = $context->getResponse();
    if (sfConfig::get('sf_cache')) {
      $viewCache = $this->context->getViewCacheManager();
      $uri = $viewCache->getCurrentCacheKey();
      $vars = array();

      if (null !== $uri) {
        list($retval, $decoratorTemplate) = $viewCache->getActionCache($uri);
        if (null !== $retval) {
          $this->setDecoratorTemplate($decoratorTemplate);
        }
      }
    }

    // decorator
    $layout = $response->getParameter($this->moduleName.'_'.$this->actionName.'_layout', null, 'sift/action/view');
    if (false === $layout) {
      $this->setDecorator(false);
    } else if (null !== $layout) {
      $this->setDecoratorTemplate($layout.$this->getExtension());
    }

    // template variables
    if ($templateVars === null) {
      $actionInstance   = $context->getActionStack()->getLastEntry()->getActionInstance();
      $templateVars     = $actionInstance->getVarHolder()->getAll();
    }

    // assigns some variables to the template
    $this->attributeHolder->add($this->getGlobalVars());
    $this->attributeHolder->add($retval !== null ? $vars : $templateVars);

    // render template if no cache
    if ($retval === null) {
      // execute pre-render check
      $this->preRenderCheck();

      // render template file
      $template = $this->getDirectory().'/'.$this->getTemplate();
      $retval = $this->renderFile($template);

      if (sfConfig::get('sf_cache') && $uri !== null) {
        $viewCache->setActionCache($uri, $retval, $this->isDecorator() ? $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate() : false);
      }
    }

    // now render decorator template, if one exists
    if ($this->isDecorator()) {
      $retval = $this->decorate($retval);
    }

    return $retval;
  }

}
