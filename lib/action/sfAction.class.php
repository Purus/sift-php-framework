<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAction executes all the logic for the current request.
 *
 * @package    Sift
 * @subpackage action
 */
abstract class sfAction extends sfComponent
{
  protected $security = array();

  /**
   * Initializes this action.
   *
   * @param sfContext The current application context.
   * @return bool true, if initialization completes successfully, otherwise false
   */
  public function initialize(sfContext $context)
  {
    parent::initialize($context);

    // include security configuration
    if($file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$this->getModuleName().'/'.sfConfig::get('sf_app_module_config_dir_name').'/security.yml', true))
    {
      require $file;
    }

    return true;
  }

  /**
   * Executes an application defined process prior to execution of this sfAction object.
   *
   * By default, this method is empty.
   */
  public function preExecute()
  {
  }

  /**
   * Execute an application defined process immediately after execution of this sfAction object.
   *
   * By default, this method is empty.
   */
  public function postExecute()
  {
  }

  /**
   * Forwards current action to the default 404 error action.
   *
   * @param  string Message of the generated exception
   *
   * @throws sfError404Exception
   *
   */
  public function forward404($message = '')
  {
    throw new sfError404Exception($message);
  }

  /**
   * Forwards current action to the default 404 error action unless the specified condition is true.
   *
   * @param bool A condition that evaluates to true or false
   * @param  string Message of the generated exception
   *
   * @throws sfError404Exception
   */
  public function forward404Unless($condition, $message = '')
  {
    if(!$condition)
    {
      throw new sfError404Exception($message);
    }
  }

  /**
   * Forwards current action to the default 404 error action if the specified condition is true.
   *
   * @param bool A condition that evaluates to true or false
   * @param  string Message of the generated exception
   *
   * @throws sfError404Exception
   */
  public function forward404If($condition, $message = '')
  {
    if($condition)
    {
      throw new sfError404Exception($message);
    }
  }

  /**
   * Redirects current action to the default 404 error action (with browser redirection).
   *
   * This method stops the current code flow.
   */
  public function redirect404()
  {
    return $this->redirect('/'.sfConfig::get('sf_error_404_module').'/'.sfConfig::get('sf_error_404_action'));
  }

  /**
   * Forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param string $module A module name
   * @param string $action An action name
   * @throws sfStopException
   */
  public function forward($module, $action)
  {
    $this->logMessage('{sfAction} Forwarding to {module_action}', sfILogger::INFO, array(
      'module_action' => $module.'/'.$action
    ));

    $this->getController()->forward($module, $action);
    throw new sfStopException();
  }

  /**
   * If the condition is true, forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param  bool   A condition that evaluates to true or false
   * @param  string A module name
   * @param  string An action name
   *
   * @throws sfStopException
   */
  public function forwardIf($condition, $module, $action)
  {
    if($condition)
    {
      $this->forward($module, $action);
    }
  }

  /**
   * Unless the condition is true, forwards current action to a new one (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param  bool   A condition that evaluates to true or false
   * @param  string A module name
   * @param  string An action name
   *
   * @throws sfStopException
   */
  public function forwardUnless($condition, $module, $action)
  {
    if (!$condition)
    {
      $this->forward($module, $action);
    }
  }

  /**
   * Forwards current action to security action (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @throws sfStopException
   */
  public function forwardToSecure()
  {
    $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
  }

  /**
   * If the condition is true, forwards current action to to security action (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param  bool   A condition that evaluates to true or false
   * @throws sfStopException
   */
  public function forwardToSecureIf($condition)
  {
    $this->forwardIf($condition, sfConfig::get('sf_secure_module'),
            sfConfig::get('sf_secure_action'));
  }

  /**
   * Unless the condition is true, forwards current action to security action (without browser redirection).
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param  bool   A condition that evaluates to true or false
   * @throws sfStopException
   */
  public function forwardToSecureUnless($condition)
  {
    $this->forwardUnless($condition,
            sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
  }

  /**
   * Redirects current request to a new URL.
   *
   * 2 URL formats are accepted :
   *  - a full URL: http://www.google.com/
   *  - an internal URL (url_for() format): module/action
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param string $url The url
   * @param string $statusCode The status code (default to 302)
   * @param array $getParameters Array of additional get parameters to append to url
   * @throws sfStopException
   */
  public function redirect($url, $statusCode = 302, $getParameters = array())
  {
    $url = $this->getController()->genUrl($url, true, $getParameters);

    $this->logMessage('{sfAction} Redirect to "{url}", code: {code}', sfILogger::INFO, array(
      'url' => $url,
      'code' => $statusCode
    ));

    $this->getController()->redirect($url, $statusCode);
    throw new sfStopException();
  }

  /**
   * Redirects current request to a new URL, unless specified condition is true.
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param boolean $condition A condition that evaluates to true or false
   * @param string $url The url
   * @param string $statusCode The status code (default to 302)
   * @param array $getParameters Array of additional get parameters to append to url
   *
   * @throws sfStopException
   *
   * @see redirect
   */
  public function redirectUnless($condition, $url, $statusCode = 302, $getParameters = array())
  {
    if(!$condition)
    {
      $this->redirect($url, $statusCode, $getParameters);
    }
  }

  /**
   * Redirects current request to a new URL, only if specified condition is true.
   *
   * This method stops the action. So, no code is executed after a call to this method.
   *
   * @param boolean $condition A condition that evaluates to true or false
   * @param string $url The url
   * @param string $statusCode The status code (default to 302)
   * @param array $getParameters Array of additional get parameters to append to url
   *
   * @throws sfStopException
   *
   * @see redirect
   */
  public function redirectIf($condition, $url, $statusCode = 302, $getParameters = array())
  {
    if($condition)
    {
      $this->redirect($url, $statusCode, $getParameters);
    }
  }

  /**
   * Appends the given text to the response content and bypasses the built-in view system.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderText('some text')</code>
   *
   * @param  string Text to append to the response
   *
   * @return sfView::NONE
   */
  public function renderText($text)
  {
    $this->getResponse()->setContent($this->getResponse()->getContent().$text);

    return sfView::NONE;
  }

  /**
   * Returns the security configuration for this module.
   *
   * @return string Current security configuration as an array
   */
  public function getSecurityConfiguration()
  {
    return $this->security;
  }

  /**
   * Overrides the current security configuration for this module.
   *
   * @param array The new security configuration
   */
  public function setSecurityConfiguration($security)
  {
    $this->security = $security;
  }

  /**
   * Indicates that this action requires security.
   *
   * @return bool true, if this action requires security, otherwise false.
   */
  public function isSecure()
  {
    $actionName = strtolower($this->getActionName());

    if(isset($this->security[$actionName]['is_secure']))
    {
      return $this->security[$actionName]['is_secure'];
    }

    if(isset($this->security['all']['is_secure']))
    {
      return $this->security['all']['is_secure'];
    }

    return false;
  }

  /**
   * Gets credentials the user must have to access this action.
   *
   * @return mixed An array or a string describing the credentials the user must have to access this action
   */
  public function getCredential()
  {
    $actionName = strtolower($this->getActionName());

    if(isset($this->security[$actionName]['credentials']))
    {
      $credentials = $this->security[$actionName]['credentials'];
    }
    else if (isset($this->security['all']['credentials']))
    {
      $credentials = $this->security['all']['credentials'];
    }
    else
    {
      $credentials = null;
    }

    return $credentials;
  }

  /**
   * Sets an alternate template for this sfAction.
   *
   * See 'Naming Conventions' in the 'Sift View' documentation.
   *
   * @param string Template name
   */
  public function setTemplate($name)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info('{sfAction} change template to "'.$name.'"');
    }

    $this->getResponse()->setParameter($this->getModuleName().'_'.$this->getActionName().'_template', $name, 'sift/action/view');
  }

  /**
   * Gets the name of the alternate template for this sfAction.
   *
   * WARNING: It only returns the template you set with the setTemplate() method,
   *          and does not return the template that you configured in your view.yml.
   *
   * See 'Naming Conventions' in the 'Sift View' documentation.
   *
   * @return string Template name. Returns null if no template has been set within the action
   */
  public function getTemplate()
  {
    return $this->getResponse()->getParameter($this->getModuleName().'_'.$this->getActionName().'_template', null, 'sift/action/view');
  }

  /**
   * Sets an alternate layout for this sfAction.
   *
   * To de-activate the layout, set the layout name to false.
   *
   * To revert the layout to the one configured in the view.yml, set the template name to null.
   *
   * @param mixed Layout name or false to de-activate the layout
   */
  public function setLayout($name)
  {
    $this->logMessage('{sfAction} Change layout to "{layout}"', sfILogger::INFO, array(
      'layout' => $name
    ));
    $this->getResponse()->setParameter($this->getModuleName().'_'.$this->getActionName().'_layout', $name, 'sift/action/view');
  }

  /**
   * Gets the name of the alternate layout for this sfAction.
   *
   * WARNING: It only returns the layout you set with the setLayout() method,
   *          and does not return the layout that you configured in your view.yml.
   *
   * @return mixed Layout name. Returns null if no layout has been set within the action
   */
  public function getLayout()
  {
    return $this->getResponse()->getParameter($this->getModuleName().'_'.$this->getActionName().'_layout', null, 'sift/action/view');
  }

  /**
   * Changes the default view class used for rendering the template associated with the current action.
   *
   * @param string View class name
   */
  public function setViewClass($class)
  {
    sfConfig::set('mod_'.strtolower($this->getModuleName()).'_view_class', $class);
  }

  /**
   * Redirect to referer
   *
   * @param string $default Route which will be used when no referer is set
   */
  public function redirectToReferer($default = '@homepage')
  {
    $url = $this->getRequest()->getReferer();

    // try to get uri from request
    if(!$url)
    {
      $parameters = array(
        'uri', 'return_url', 'return_uri'
      );

      foreach($parameters as $parameter)
      {
        if($this->getRequest()->hasParameter($parameter))
        {
          $url = $this->getRequest()->getParameter($parameter);
          // is this url encoded using sfSafeUrl::encode()?
          if(sfSafeUrl::decode($url))
          {
            $url = sfSafeUrl::decode($url);
          }
          break;
        }
      }
    }

    // check against open redirect attacks
    if($url && sfSecurity::isRedirectUrlValid($url))
    {
      $url = urldecode($url);
    }

    return $this->redirect($url ? $url : $default);
  }

  /**
   * Returns the value of a request parameter.
   *
   * @param  string The parameter name
   * @param  mixed Default value returned
   * @param  string Perform any cleanup on parameter?
   * @return string The request parameter value
   */
  public function getRequestParameter($name, $default = null, $clean_method = null)
  {
    if($clean_method)
    {
      switch(strtolower($clean_method))
      {
        case 'array':
          return $this->getRequest()->getArray($name, $default);
        break;

        case 'string':
          return $this->getRequest()->getString($name, $default);
        break;

        case 'string_array':
          return $this->getRequest()->getStringArray($name, $default);
        break;

        case 'int':
        case 'integer':
          return $this->getRequest()->getInt($name, $default);
        break;

        case 'int_array':
        case 'integer_array':
          return $this->getRequest()->getIntArray($name, $default);
        break;

        case 'float':
        case 'double':
        case 'real':
          return $this->getRequest()->getFloat($name, $default);
        break;

        case 'float_array':
        case 'double_array':
        case 'real_array':
          return $this->getRequest()->getFloatArray($name, $default);
        break;

        case 'bool':
        case 'boolean':
          return $this->getRequest()->getBool($name, $default);
        break;

        case 'bool_array':
        case 'boolean_array':
          return $this->getRequest()->getBoolArray($name, $default);
        break;

        default:
          throw new InvalidArgumentException(sprintf('Invalid clean "%s" method given', $clean_method));
        break;
      }
    }

    return $this->getRequest()->getParameter($name, $default);
  }

  /**
   * Disables layout and turns web debug
   *
   * @param boolean Switch also web debug?
   */
  public function disableLayout($turn_debug_off = true)
  {
    $this->setLayout(false);
    if($turn_debug_off)
    {
      sfConfig::set('sf_web_debug', false);
    }
  }

  /**
   * Renders json
   *
   * @param $data
   * @param $encode
   * @return sfView::NONE
   */
  public function renderJson($data, $encode = true)
  {
    if($encode)
    {
      $data = sfJson::encode($data);
    }
    $response = $this->getResponse();
    $response->setContent('');

    // set content type only if there is no content type set
    if(!$response->getHttpHeader('Content-Type'))
    {
      $response->setContentType('application/json');
    }

    if(!$response->getHttpHeader('X-Content-Type-Options'))
    {
      $this->getResponse()->setHttpHeader('X-Content-Type-Options', 'nosniff');
    }

    return $this->renderText($data);
  }

  /**
   * Allows an action to send code that will render the response when called
   * rather than returning a pre-rendered string. This allows content rendering
   * to be deferred until sendContent() is called, which can result in less
   * memory usage if you are streaming files or similar.
   *
   * @param callable $callable
   * @return int
   */
  public function renderCallable($callable)
  {
    if($callable instanceof sfCallable)
    {
      $callable = $callable->getCallable();
    }

    if(!sfToolkit::isCallable($callable, false, $callableName))
    {
      throw new InvalidArgumentException(sprintf('Invalid callable "%s" given.', $callableName));
    }

    $this->getResponse()->setContent($callable);

    return sfView::NONE;
  }

  /**
   * Sends an image to browser
   *
   * @param sfImage $image
   * @param string $contentType
   * @return void
   */
  protected function renderImage(sfImage $image, $contentType = 'image/png')
  {
    $response = $this->getResponse();
    $response->setContentType($contentType);

    return $this->renderText($image->toString());
  }

  /**
   * Renders as XML
   *
   * @param $data
   */
  public function renderXml($xml)
  {
    $response = $this->getResponse();
    $response->setContentType('text/xml');

    return $this->renderText($xml);
  }

  /**
   * Proxy action method to translate a string
   *
   * @param  string     $string
   * @param  array      $args
   * @param  $catalogue $catalogue
   * @return string
   */
  protected function __($string, $args = array(), $catalogue = 'messages')
  {
    return $this->getI18N()->__($string, $args, $catalogue);
  }

  /**
   * Return sfI18N object instance
   *
   * @return sfI18N
   */
  protected function getI18N()
  {
    return $this->getContext()->getI18N();
  }

  /**
   * Setup layout to $layout. The layout will be set only if the layout template does exist
   * in the decorator directory(ies).
   *
   * @param string $layout Layout.
   */
  protected function setupLayout($layout = 'minimal')
  {
    $extension = '.php';
    if($class = sfConfig::get('mod_'.strtolower($this->getModuleName()).'_view_class'))
    {
      $view = $this->getContext()->getServiceContainer()->createObject(sprintf('%sView', $class));
      $extension = $view->getExtension();
    }
    if(sfLoader::getDecoratorDir($layout . $extension))
    {
      $this->setLayout($layout);
    }
  }

  /**
   * Returns the partial rendered content.
   *
   * If the vars parameter is omitted, the action's internal variables
   * will be passed, just as it would to a normal template.
   *
   * If the vars parameter is set then only those values are
   * available in the partial.
   *
   * @param  string $templateName partial name
   * @param  array  $vars         vars
   *
   * @return string The partial content
   */
  public function getPartial($templateName, $vars = null, $viewName = null)
  {
    sfLoader::loadHelpers('Partial');
    $vars = !is_null($vars) ? $vars : $this->varHolder->getAll();

    return get_partial($templateName, $vars, $viewName);
  }

  /**
   * Appends the result of the given partial execution to the response content.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderPartial('foo/bar')</code>
   *
   * @param  string $templateName partial name
   * @param  array  $vars         vars
   *
   * @return sfView::NONE
   *
   * @see    getPartial
   */
  public function renderPartial($templateName, $vars = null, $viewName = null)
  {
    return $this->renderText($this->getPartial($templateName, $vars, $viewName = null));
  }

  /**
   * Returns the component rendered content.
   *
   * If the vars parameter is omitted, the action's internal variables
   * will be passed, just as it would to a normal template.
   *
   * If the vars parameter is set then only those values are
   * available in the component.
   *
   * @param  string  $moduleName    module name
   * @param  string  $componentNae  component name
   * @param  array   $vars          vars
   *
   * @return string  The component rendered content
   */
  public function getComponent($moduleName, $componentName, $vars = null, $viewName = null)
  {
    sfLoader::loadHelpers('Partial');
    $vars = !is_null($vars) ? $vars : $this->varHolder->getAll();

    return get_component($moduleName, $componentName, $vars, $viewName);
  }

  /**
   * Appends the result of the given component execution to the response content.
   *
   * This method must be called as with a return:
   *
   * <code>return $this->renderComponent('foo', 'bar')</code>
   *
   * @param  string  $moduleName    module name
   * @param  string  $componentNae  component name
   * @param  array   $vars          vars
   *
   * @return sfView::NONE
   *
   * @see    getComponent
   */
  public function renderComponent($moduleName, $componentName, $vars = null, $viewName = null)
  {
    return $this->renderText($this->getComponent($moduleName, $componentName, $vars, $viewName));
  }

  /**
   * Returns text body used for email part (plain or html)
   *
   * This methods renders a partial with the sfPartialMailView class.
   *
   * @param  string A module name
   * @param  string An action name
   *
   * @return string The generated mail content
   *
   * @see sfMailView, getPresentationFor(), sfController
   */
  public function getMailBody($partial, sfMailerMessage $message, $vars = null, $type = 'plain')
  {
    $this->logMessage('{sfAction} getMailBody() is deprecated. Use $mail_message->setBodyFromPartial() instead.', sfILogger::ERROR);

    // validate email type
    if(!in_array($type, array('plain', 'html')))
    {
      throw new sfConfigurationException(sprintf('Invalid email type passed ("%s"). Valid types are "plain" or "html".', $type));
    }

    if(is_null($vars))
    {
      $vars = array();
    }

    $vars['sf_email_type']      = $type;
    $vars['sf_mailer_message']  = $message;

    return $this->getPartial($partial, $vars, 'sfPartialMail');
  }

  /**
   * Returns myMailer instance
   *
   * @return myMailer
   */
  public function getMailer()
  {
    return $this->getContext()->getMailer();
  }

  /**
   * Download a file using its absolute path
   *
   * @param string $file Absolute path to a file
   * @param array $options array of options
   */
  protected function downloadFile($file, array $options = array())
  {
    $downloader = new sfHttpDownload($options, $this->getRequest(), $this->getResponse(),
                                     $this->getEventDispatcher(), $this->getLogger());
    $downloader->setFile($file);

    return $this->renderCallable(array($downloader, 'send'));
  }

  /**
   * Download raw data data
   *
   * @param string $data
   * @param array $options array of options
   */
  protected function downloadData($data, array $options = array())
  {
    $downloader = new sfHttpDownload($options, $this->getRequest(), $this->getResponse(),
                                     $this->getEventDispatcher(), $this->getLogger());
    $downloader->setData($data);

    return $this->renderCallable(array($downloader, 'send'));
  }

}
