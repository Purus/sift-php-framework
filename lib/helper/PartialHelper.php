<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * PartialHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Evaluates and echoes a component slot.
 * The component name is deduced from the definition of the view.yml
 * For a variable to be accessible to the component and its partial, 
 * it has to be passed in the second argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_component_slot('sidebar', array('myvar' => 12345));
 * </code>
 *
 * @param  string slot name
 * @param  array variables to be made accessible to the component
 * @return void
 * @see    get_component_slot, include_partial, include_component
 */
function include_component_slot($name, $vars = array())
{
  echo get_component_slot($name, $vars);
}

/**
 * Evaluates and returns a component slot.
 * The syntax is similar to the one of include_component_slot.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_component_slot('sidebar', array('myvar' => 12345));
 * </code>
 *
 * @param  string slot name
 * @param  array variables to be made accessible to the component
 * @return string result of the component execution
 * @see    get_component_slot, include_partial, include_component
 */
function get_component_slot($name, $vars = array())
{
  $context = sfContext::getInstance();

  $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
  $viewInstance     = $actionStackEntry->getViewInstance();

  if(!$viewInstance->hasComponentSlot($name))
  {
    // cannot find component slot
    throw new sfConfigurationException(sprintf('The component slot "%s" is not set', $name));
  }

  if($componentSlot = $viewInstance->getComponentSlot($name))
  {
    if(is_array($componentSlot[0]))
    {
      $result = '';
      foreach($componentSlot as $slot)
      {
        $result .= get_component($slot[0], $slot[1], $vars);
      }
      return $result;
    }
    
    return get_component($componentSlot[0], $componentSlot[1], $vars);
  }
}

/**
 * Checks if given component slot exists.
 * 
 * @param string $name
 * @return boolean
 */
function has_component_slot($name)
{
  $context = sfContext::getInstance();

  $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
  $viewInstance     = $actionStackEntry->getViewInstance();

  return $viewInstance->hasComponentSlot($name);
}

/**
 * Evaluates and echoes a component.
 * For a variable to be accessible to the component and its partial, 
 * it has to be passed in the third argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_component('mymodule', 'mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string module name
 * @param  string component name
 * @param  array variables to be made accessible to the component
 * @return void
 * @see    get_component, include_partial, include_component_slot
 */
function include_component($moduleName, $componentName, $vars = array(), $viewName = null)
{
  echo get_component($moduleName, $componentName, $vars, $viewName);
}

/**
 * Evaluates and returns a component.
 * The syntax is similar to the one of include_component.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_component('mymodule', 'mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string module name
 * @param  string component name
 * @param  array variables to be made accessible to the component
 * @return string result of the component execution
 * @see    include_component
 */
function get_component($moduleName, $componentName, $vars = array(), $viewName = null)
{  
  $context = sfContext::getInstance();
  $actionName = '_'.$componentName;

  // check cache
  if ($cacheManager = $context->getViewCacheManager())
  {
    if ($retval = _get_cache($cacheManager, $moduleName, $actionName, $vars))
    {
      return $retval;
    }
    
    $response = $context->getResponse();
      
    $css = $js = $discovery_links = array();
    foreach(array('first', '', 'last') as $position)
    {
      $js  = array_merge($js, $response->getJavascripts($position));
      $css = array_merge($css, $response->getStylesheets($position));      
    }
    $discovery_links = $response->getAutoDiscoveryLinks(); 
  }

  $controller = $context->getController();

  if(!$controller->componentExists($moduleName, $componentName))
  {
    // cannot find component
    throw new sfConfigurationException(sprintf('The component does not exist: "%s", "%s"', $moduleName, $componentName));
  }

  // create an instance of the action
  $componentInstance = $controller->getComponent($moduleName, $componentName);

  // initialize the action
  if(!$componentInstance->initialize($context))
  {
    // component failed to initialize
    throw new sfInitializationException(sprintf('Component initialization failed for module "%s", component "%s"', $moduleName, $componentName));
  }

  // load component's module config file
  require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));

  $componentInstance->getVarHolder()->add($vars);

  // dispatch component
  $componentToRun = 'execute'.ucfirst($componentName);
  if(!method_exists($componentInstance, $componentToRun))
  {
    if(!method_exists($componentInstance, 'execute'))
    {
      // component not found
      throw new sfInitializationException(sprintf('sfComponent initialization failed for module "%s", component "%s"', $moduleName, $componentName));
    }

    $componentToRun = 'execute';
  }

  if(sfConfig::get('sf_logging_enabled'))
  {
    $context->getLogger()->info('{PartialHelper} call "'.$moduleName.'->'.$componentToRun.'()'.'"');
  }

  // run component
  if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
  {
    $timer = sfTimerManager::getTimer(sprintf('Component "%s/%s"', $moduleName, $componentName));
  }

  $retval = $componentInstance->$componentToRun();

  if(sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
  {
    $timer->addTime();
  }

  if($retval != sfView::NONE)
  {
    // render
    if($viewName == null)
    {      
     // default view for the partial
      $viewName = 'sfPartial';      
      // load setting for the module, and detect which view is used for the partial!
      require_once(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.
            sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));
      $viewName = sfConfig::get(sprintf('mod_'.strtolower($moduleName).'_%s_view_class', $actionName), $viewName);
    }
    
    $viewName = sprintf('%sView', $viewName);
    $view     = new $viewName;
    
    $view->initialize($context, $moduleName, $actionName, '');
    
    $view->setPartialVars(true === sfConfig::get('sf_escaping_strategy') ? sfOutputEscaper::unescape($vars) : $vars); 

    $retval = $view->render($componentInstance->getVarHolder()->getAll());

    if ($cacheManager && (!sfConfig::get('sf_lazy_cache_key') || $cacheManager->isCacheable($moduleName, $actionName)))
    {
      $uri = _get_cache_uri($moduleName, $actionName, $vars);
      
      $importedJs = $importedCss = $importedDiscoveryLinks = array();      
      foreach(array('first', '', 'last') as $position)
      {
        $importedJs  = array_merge($importedJs, $response->getJavascripts($position));
        $importedCss = array_merge($importedCss, $response->getStylesheets($position));        
      }
      
      $importedJs = array_diff_key($importedJs, $js);      
      $importedCss = array_diff_key($importedCss, $css);
      $importedDiscoveryLinks = array_diff_key($response->getAutoDiscoveryLinks(), $discovery_links);

      $retval = _set_cache($cacheManager, $uri, $retval, $importedJs, $importedCss, $importedDiscoveryLinks);
    }

    return $retval;
  }
}

/**
 * Evaluates and echoes a partial.
 * The partial name is composed as follows: 'mymodule/mypartial'.
 * The partial file name is _mypartial.php and is looked for in modules/mymodule/templates/.
 * If the partial name doesn't include a module name,
 * then the partial file is searched for in the caller's template/ directory.
 * If the module name is 'global', then the partial file is looked for in myapp/templates/.
 * For a variable to be accessible to the partial, it has to be passed in the second argument.
 *
 * <b>Example:</b>
 * <code>
 *  include_partial('mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string partial name
 * @param  array variables to be made accessible to the partial
 * @return void
 * @see    get_partial, include_component
 */
function include_partial($templateName, $vars = array(), $viewName = null)
{
  echo get_partial($templateName, $vars, $viewName);
}

/**
 * Evaluates and returns a partial.
 * The syntax is similar to the one of include_partial
 *
 * <b>Example:</b>
 * <code>
 *  echo get_partial('mypartial', array('myvar' => 12345));
 * </code>
 *
 * @param  string partial name
 * @param  array variables to be made accessible to the partial
 * @return string result of the partial execution
 * @see    include_partial
 */
function get_partial($templateName, $vars = array(), $viewName = null)
{
  $context = sfContext::getInstance();
  
  // partial is in another module?
  if (false !== $sep = strpos($templateName, '/'))
  {
    $moduleName   = substr($templateName, 0, $sep);
    $templateName = substr($templateName, $sep + 1);    
  }
  else
  {
    $moduleName = $context->getActionStack()->getLastEntry()->getModuleName();
  }
  
  $actionName = '_'.$templateName;
  
  if ($cacheManager = $context->getViewCacheManager())
  {
    if ($retval = _get_cache($cacheManager, $moduleName, $actionName, $vars))
    {
      return $retval;
    }
    
    $response = $context->getResponse();
    
    $css = $js = $discovery_links = array();
    foreach(array('', 'first', 'last') as $position)
    {
      $js  = array_merge($js, $response->getJavascripts($position));
      $css = array_merge($css, $response->getStylesheets($position));      
    }
    $discovery_links = $response->getAutoDiscoveryLinks();
    
  }
  
  if(is_null($viewName))
  {
    // default view for the partial
    $viewName = 'sfPartial';
    if($moduleName != 'global')
    {
      // load setting for the module, and detect which view is used for the partial!
      require_once(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.
            sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));
      
      $viewName = sfConfig::get(sprintf('mod_'.strtolower($moduleName).'__%s_view_class', $templateName), $viewName);
    }
  }

  $viewName = sprintf('%sView', $viewName);
  $view = new $viewName;    
  
  $view->initialize($context, $moduleName, $actionName, '');
  $view->setPartialVars(true === sfConfig::get('sf_escaping_strategy') ? sfOutputEscaper::unescape($vars) : $vars);  
  $retval = $view->render();

  if ($cacheManager && (!sfConfig::get('sf_lazy_cache_key') || $cacheManager->isCacheable($moduleName, $actionName)))
  {
    $uri = _get_cache_uri($moduleName, $actionName, $vars);
    $importedJs = $importedCss = $importedDiscoveryLinks = array();      
    foreach(array('first', '', 'last') as $position)
    {
      $importedJs  = array_merge($importedJs, $response->getJavascripts($position));
      $importedCss = array_merge($importedCss, $response->getStylesheets($position));        
    }

    $importedJs = array_diff_key($importedJs, $js);      
    $importedCss = array_diff_key($importedCss, $css);
    $importedDiscoveryLinks = array_diff_key($response->getAutoDiscoveryLinks(), $discovery_links);

    $retval = _set_cache($cacheManager, $uri, $retval, $importedJs, $importedCss, $importedDiscoveryLinks);
  }

  return $retval;
}

function _get_cache($cacheManager, $moduleName, $actionName = null, $vars = array())
{
  if (!$cacheManager->isCacheable($moduleName, $actionName))
  {
    return null;
  }
  
  $uri    = _get_cache_uri($moduleName, $actionName, $vars);
  $data   = @unserialize($cacheManager->get($uri));
  $retval = null;
  if(is_array($data))
  {
    $response = sfContext::getInstance()->getResponse();

    foreach($data['js'] as $js => $options)
    {
      $response->addJavascript($js, '');
    }
    
    foreach($data['css'] as $css => $options)
    {
      $response->addStylesheet($css, '', $options);
    }

    foreach($data['discovery_links'] as $url => $link)
    {
      $response->addAutoDiscoveryLink($url, $link['type'], $link['tag_options']);
    }

    $retval = $data['data'];
  }

  if(sfConfig::get('sf_web_debug'))
  {
    $retval = sfCore::filterByEventListeners($retval, 'view.cache.filter_content', array(
      'uri' => $uri,
      'new' => false  
    ));
  }
  return $retval;
}

function _get_cache_uri($moduleName, $actionName, & $vars = array())
{
  return '@sf_cache_partial?module='.$moduleName.'&action='.$actionName.'&sf_cache_key='._get_cache_key($vars);
}

function _get_cache_key(& $vars = array())
{
  if (!isset($vars['sf_cache_key']))
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{PartialHelper} Generate cache key');
    }
    $vars['sf_cache_key'] = md5(serialize($vars));
  }
  return $vars['sf_cache_key'];
}

function _set_cache($cacheManager, $uri, $retval, $js = array(), $css = array(), $discoveryLinks = array())
{  
  $data   = array('data' => $retval, 'css' => $css, 'js' => $js, 'discovery_links' => $discoveryLinks);
  $saved  = $cacheManager->set(serialize($data), $uri);

  if($saved && sfConfig::get('sf_web_debug'))
  {
    $retval = sfCore::filterByEventListeners($retval, 'view.cache.filter_content', array(
      'uri' => $uri,
      'new' => true  
    ));    
  }
  return $retval;
}

/**
 * Begins the capturing of the slot.
 *
 * @param  string slot name
 * @param  string $value  The slot content
 * @return void
 * @see    end_slot
 */
function slot($name, $value = null)
{
  $context = sfContext::getInstance();
  $response = $context->getResponse();

  $slots = $response->getParameter('slots', array(), 'sift/view/sfView/slot');
  $slot_names = $response->getParameter('slot_names', array(), 'sift/view/sfView/slot');
  if (in_array($name, $slot_names))
  {
    throw new sfCacheException(sprintf('A slot named "%s" is already started.', $name));
  }

  $slot_names[] = $name;
  $slots[$name] = $value ? $value : '';

  $response->setParameter('slots', $slots, 'sift/view/sfView/slot');
  $response->setParameter('slot_names', $slot_names, 'sift/view/sfView/slot');

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getLogger()->info(sprintf('{PartialHelper} set slot "%s"', $name));
  }

  if(null !== $value)
  {
    ob_start();
    ob_implicit_flush(0);
  }
}

/**
 * Stops the content capture and save the content in the slot.
 *
 * @return void
 * @see    slot
 */
function end_slot()
{
  $content = ob_get_clean();

  $response = sfContext::getInstance()->getResponse();
  $slots = $response->getParameter('slots', array(), 'sift/view/sfView/slot');
  $slot_names = $response->getParameter('slot_names', array(), 'sift/view/sfView/slot');
  if (!$slot_names)
  {
    throw new sfCacheException('No slot started.');
  }

  $name = array_pop($slot_names);
  $slots[$name] = $content;

  $response->setParameter('slots', $slots, 'sift/view/sfView/slot');
  $response->setParameter('slot_names', $slot_names, 'sift/view/sfView/slot');
}

/**
 * Returns true if the slot exists.
 *
 * @param  string slot name
 * @return boolean true, if the slot exists
 * @see    get_slot, include_slot
 */
function has_slot($name)
{
  $response = sfContext::getInstance()->getResponse();
  $slots = $response->getParameter('slots', array(), 'sift/view/sfView/slot');

  return array_key_exists($name, $slots);
}

/**
 * Evaluates and echoes a slot.
 *
 * <b>Example:</b>
 * <code>
 *  include_slot('navigation');
 * </code>
 *
 * @param  string slot name
 * @return void
 * @see    has_slot, get_slot
 */
function include_slot($name, $default = '')
{
  return ($v = get_slot($name, $default)) ? print $v : false;
}

/**
 * Evaluates and returns a slot.
 *
 * <b>Example:</b>
 * <code>
 *  echo get_slot('navigation');
 * </code>
 *
 * @param  string slot name
 * @params string default content
 * @return string content of the slot
 * @see    has_slot, include_slot
 */
function get_slot($name, $default = '')
{
  $context = sfContext::getInstance();
  $slots = $context->getResponse()->getParameter('slots', array(), 'sift/view/sfView/slot');

  if (sfConfig::get('sf_logging_enabled'))
  {
    $context->getLogger()->info(sprintf('{PartialHelper} get slot "%s"', $name));
  }

  return isset($slots[$name]) ? $slots[$name] : $default;
}
