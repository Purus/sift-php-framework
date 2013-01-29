<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * sfBrowser simulates a browser which can surf a application.
 *
 * @package    Sift
 * @subpackage util
 */
class sfPrefetchBrowser extends sfBrowser
{
  protected
    $listeners        = array(),
    $context          = null,
    $currentException = null;

  /**
   * Calls a request to a uri.
   */
  protected function doCall()
  {
    // do not recycle 
    $this->context = sfContext::getInstance();
    
    $this->resetCurrentException();
    
    // dispatch our request
    ob_start();
    $this->context->getController()->dispatch();
    $retval = ob_get_clean();

    // append retval to the response content
    $this->context->getResponse()->setContent($retval);

    // manually shutdown user to save current session data
    if ($this->context->getUser())
    {
      $this->context->getUser()->shutdown();
      $this->context->getStorage()->shutdown();
    }
  }


  /**
   * Shutdown function to clean up and remove sessions
   *
   * @return void
   */
  public function shutdown()
  {
    parent::shutdown();
    // we remove all session data
    // sfToolkit::clearDirectory(sfConfig::get('sf_test_cache_dir').'/sessions');
  }

}
