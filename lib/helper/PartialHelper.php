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
 * @package Sift
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
 * @see get_component_slot, include_partial, include_component
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
  $viewInstance = $actionStackEntry->getViewInstance();

  if(!$viewInstance->hasComponentSlot($name))
  {
    // cannot find component slot
    throw new sfConfigurationException(sprintf('The component slot "%s" is not set.', $name));
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
  $viewInstance = $actionStackEntry->getViewInstance();
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
  $actionName = '_'.$componentName;

  require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml', true));

  $class = 'sfPartial';
  if(!$viewName)
  {
    $templateName = '_'.$componentName;
    if($partialViewClass = sfConfig::get(sprintf('mod_'.strtolower($moduleName).'__%s_view_class', $templateName)))
    {
      $class = $partialViewClass;
    }
    elseif($moduleViewClass = sfConfig::get('mod_'.strtolower($moduleName).'_partial_view_class', $templateName))
    {
      $class = $moduleViewClass;
    }
  }
  else
  {
    $class = $viewName;
  }

  $class = sprintf('%sView', $class);
  $view = sfDependencyInjectionContainer::create($class);

  if(!$view instanceof sfIPartialView)
  {
    throw new LogicException(sprintf('The view "%s" does not implement sfIPartialView.', $class));
  }

  $view->initialize($moduleName, $actionName, '');
  $view->setPartialVars(true === sfConfig::get('sf_escaping_strategy') ? sfOutputEscaper::unescape($vars) : $vars);

  if($retval = $view->getCache())
  {
    return $retval;
  }

  $allVars = _call_component($moduleName, $componentName, $vars);

  if(null !== $allVars)
  {
    // render
    $view->getAttributeHolder()->add($allVars);
    return $view->render();
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
 * @param string $templateName The partial template name
 * @param array $variables The variables to be made accessible to the partial
 * @param string $viewName The view class (will be expanded to sf%ViewName
 * @return string result of the partial execution
 * @see    include_partial
 */
function get_partial($templateName, $variables = array(), $viewName = null)
{
  // partial is in another module?
  if(false !== $sep = strpos($templateName, '/'))
  {
    $moduleName   = substr($templateName, 0, $sep);
    $templateName = substr($templateName, $sep + 1);
  }
  else
  {
    $moduleName = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();
  }

  $actionName = '_'.$templateName;
  $class = 'sfPartial';
  if(!$viewName)
  {
    // load setting for the module, and detect which view is used for the partial!
    require_once(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml', true));
    if($partialViewClass = sfConfig::get(sprintf('mod_'.strtolower($moduleName).'__%s_view_class', $templateName)))
    {
      $class = $partialViewClass;
    }
    elseif($moduleViewClass = sfConfig::get('mod_'.strtolower($moduleName).'_partial_view_class', $templateName))
    {
      $class = $moduleViewClass;
    }
  }
  else
  {
    $class = $viewName;
  }

  $class = sprintf('%sView', $class);
  // die('aa' . $class);
  $view = sfDependencyInjectionContainer::create($class);
  if(!$view instanceof sfIPartialView)
  {
    throw new LogicException(sprintf('Partial view "%s" does not implement sfIPartialView', $class));
  }

  $view->initialize($moduleName, $actionName, '');
  $view->setPartialVars(true === sfConfig::get('sf_escaping_strategy') ? sfOutputEscaper::unescape($variables) : $variables);
  return $view->render();
}

/**
 * Begins the capturing of the slot.
 *
 * @param string $name Slot name
 * @param string $value The slot content
 * @throws LogicException If the same slot has already been started
 * @return void
 * @see end_slot
 */
function slot($name, $value = null)
{
  $slot_names = sfConfig::get('sf_view_slot_names', array());
  if(in_array($name, $slot_names))
  {
    throw new LogicException(sprintf('A slot named "%s" is already started.', $name));
  }

  sfContext::getInstance()->getResponse()->setSlot($name, $value);

  if(sfConfig::get('sf_logging_enabled'))
  {
    sfLogger::getInstance()->info(sprintf('{PartialHelper} Set slot "%s"', $name));
  }

  if(null !== $value)
  {
    return;
  }

  $slot_names[] = $name;
  sfConfig::set('sf_view_slot_names', $slot_names);

  ob_start();
  ob_implicit_flush(0);
}

/**
 * Stops the content capture and save the content in the slot.
 *
 * @return void
 * @throw LogicException
 * @see slot
 */
function end_slot()
{
  $content = ob_get_clean();

  $slot_names = sfConfig::get('sf_view_slot_names', array());

  if(!$slot_names)
  {
    throw new LogicException('No slot started yet. Start the slot using slot().');
  }

  $name = array_pop($slot_names);

  sfContext::getInstance()->getResponse()->setSlot($name, $content);
  sfConfig::set('sf_view_slot_names', $slot_names);
}

/**
 * Returns true if the slot exists.
 *
 * @param string $name The slot name
 * @return boolean true, if the slot exists
 * @see get_slot, include_slot
 */
function has_slot($name)
{
  return sfContext::getInstance()->getResponse()->hasSlot($name);
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
 * @param string $name The slot name
 * @params string $default The default content
 * @return string Content of the slot
 * @see has_slot, include_slot
 */
function get_slot($name, $default = '')
{
  if(sfConfig::get('sf_logging_enabled'))
  {
    sfLogger::getInstance()->info(sprintf('{PartialHelper} Get slot "%s"', $name));
  }
  return sfContext::getInstance()->getResponse()->getSlot($name, $default);
}

function _call_component($moduleName, $componentName, $vars)
{
  $context = sfContext::getInstance();

  $controller = $context->getController();

  if (!$controller->componentExists($moduleName, $componentName))
  {
    // cannot find component
    throw new sfConfigurationException(sprintf('The component does not exist: "%s", "%s".', $moduleName, $componentName));
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
  require(sfConfigCache::getInstance()->checkConfig('modules/'.$moduleName.'/config/module.yml'));

  // pass unescaped vars to the component if escaping_strategy is set to true
  $componentInstance->getVarHolder()->add(true === sfConfig::get('sf_escaping_strategy') ? sfOutputEscaper::unescape($vars) : $vars);

  // dispatch component
  $componentToRun = 'execute'.ucfirst($componentName);
  if(!method_exists($componentInstance, $componentToRun))
  {
    if(!method_exists($componentInstance, 'execute'))
    {
      // component not found
      throw new sfInitializationException(sprintf('sfComponent initialization failed for module "%s", component "%s".', $moduleName, $componentName));
    }
    $componentToRun = 'execute';
  }

  if(sfConfig::get('sf_logging_enabled'))
  {
    sfLogger::getInstance()->info(sprintf('Call "%s->%s()'.'"', $moduleName, $componentToRun));
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

  return sfView::NONE == $retval ? null : $componentInstance->getVarHolder()->getAll();
}
