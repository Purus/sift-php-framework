<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBasicSecurityFilter checks security by calling the getCredential() method
 * of the action. Once the credential has been acquired, sfBasicSecurityFilter
 * verifies the user has the same credential by calling the hasCredential()
 * method of SecurityUser.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfBasicSecurityFilter extends sfSecurityFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // get the cool stuff
    $context    = $this->getContext();
    $controller = $context->getController();
    $user       = $context->getUser();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // disable security on [sf_login_module] / [sf_login_action]
    if (
      (sfConfig::get('sf_login_module') == $context->getModuleName()) && (sfConfig::get('sf_login_action') == $context->getActionName())
      ||
      (sfConfig::get('sf_secure_module') == $context->getModuleName()) && (sfConfig::get('sf_secure_action') == $context->getActionName())
    )
    {
      $filterChain->execute();

      return;
    }

    // get the credential required for this action
    $credential = $actionInstance->getCredential();

    // for this filter, the credentials are a simple privilege array
    // where the first index is the privilege name and the second index
    // is the privilege namespace
    //
    // NOTE: the nice thing about the Action class is that getCredential()
    //       is vague enough to describe any level of security and can be
    //       used to retrieve such data and should never have to be altered
    if ($user->isAuthenticated())
    {
      // the user is authenticated
      if ($credential === null || $user->hasCredential($credential))
      {
        // the user has access, continue
        $filterChain->execute();
      }
      else
      {
        // 403 (Forbidden)
        if($context->getRequest()->isAjax())
        {
          return $this->handle403Ajax();
        }
        
        // the user doesn't have access, exit stage left
        $controller->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
      }
    }
    else
    {
      // 401 (Not authorized)
      if($context->getRequest()->isAjax())
      {
        return $this->handle401Ajax();
      }
      
      if(sfConfig::get('sf_use_flash'))
      {
        // set flash error, so the user knows whats going on
        $user->setAttribute('error', __('You have to be logged in to access this page.', array(),                 
                sfConfig::get('sf_sift_data_dir') . '/i18n/catalogues/action'), 'sift/flash');
      }
      
      // the user is not authenticated
      $controller->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

      throw new sfStopException();
    }
  }
  
  /**
   * Handles 401 (Not authorized) case when requested via Ajax
   * 
   * @return void
   */
  protected function handle401Ajax()
  {
    $response = $this->getContext()->getResponse();        
    // Using 401 status code without "WWW-Authenticate" header
    // violates the RFC 
    // @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    $response->setStatusCode(401);
    $response->setHeaderOnly(true);        
    $response->send();
    return;      
  }
  
  /**
   * Handles 403 (Forbidden) case when requested via Ajax
   * 
   * @return void
   */
  protected function handle403Ajax()
  {
    $response = $this->getContext()->getResponse();
    $response->setStatusCode(403);          
    $response->setHeaderOnly(true); 
    $response->send();          
    return;    
  }
  
}