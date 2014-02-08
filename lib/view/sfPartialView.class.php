<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP partial view
 *
 * @package Sift
 * @subpackage view
 * @inject context
 */
class sfPartialView extends sfPHPView implements sfIPartialView
{
  protected
    $viewCache   = null,
    $checkCache  = false,
    $cacheKey    = null,
    $partialVars = array();

  /**
   * Constructor.
   *
   * @see sfView
   */
  public function initialize($moduleName, $actionName, $viewName)
  {
    $ret = parent::initialize($moduleName, $actionName, $viewName);

    $this->viewCache = $this->context->getViewCacheManager();

    if(sfConfig::get('sf_cache'))
    {
      $this->checkCache = $this->viewCache->isActionCacheable($moduleName, $actionName);
    }

    return $ret;
  }

  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    $this->setDecorator(false);

    $this->setTemplate($this->actionName.$this->getExtension());
    if('global' == $this->moduleName)
    {
      $this->setDirectory(sfConfig::get('sf_app_template_dir'));
    }
    else
    {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * @param array $partialVars
   */
  public function setPartialVars(array $partialVars)
  {
    $this->partialVars = $partialVars;
    $this->attributeHolder->add($partialVars);
  }

  /**
   * Renders the presentation.
   *
   * @param array Template attributes
   *
   * @return string Current template content
   */
  public function render($templateVars = array())
  {
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    if($retval = $this->getCache())
    {
      return $retval;
    }

    if($this->checkCache)
    {
      $mainResponse = $this->context->getResponse();
      $response = $this->context->getServiceContainer()->createObject(get_class($mainResponse));
      $response->setOptions($mainResponse->getOptions());

      // the inner response has access to different properties, depending on whether it is marked as contextual in cache.yml
      if($this->viewCache->isContextual($this->viewCache->getPartialUri($this->moduleName, $this->actionName, $this->cacheKey)))
      {
        $response->mergeProperties($mainResponse);
      }
      else
      {
        $response->setContentType($mainResponse->getContentType());
      }

      $this->context->setResponse($response);
    }

    try
    {
      // execute pre-render check
      $this->preRenderCheck();

      $this->getAttributeHolder()->set('sf_type', 'partial');

      // assigns some variables to the template
      $this->attributeHolder->add($this->getGlobalVars());
      $this->attributeHolder->add($templateVars);

      // render template
      $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());
    }
    catch(Exception $e)
    {
      if($this->checkCache)
      {
        $this->context->setResponse($mainResponse);
        $mainResponse->merge($response);
      }

      throw $e;
    }

    if($this->checkCache)
    {
      $retval = $this->viewCache->setPartialCache($this->moduleName, $this->actionName, $this->cacheKey, $retval);
      $this->context->setResponse($mainResponse);
      $mainResponse->merge($response);
    }

    if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $retval;
  }

  public function getCache()
  {
    if(!$this->checkCache)
    {
      return null;
    }
    $this->cacheKey = $this->viewCache->checkCacheKey($this->partialVars);
    if(($retval = $this->viewCache->getPartialCache($this->moduleName, $this->actionName, $this->cacheKey)))
    {
      return $retval;
    }
  }

}
