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
  /**
   * The application name
   *
   * @var string
   */
  protected $application;

  /**
   * The environment
   *
   * @var string
   */
  protected $environment;

  /**
   * Constructor
   *
   * @param string $app The application name
   * @param string $env The environment
   * @param string $hostname The hostname
   * @param string $remote The ip address
   * @param array $options Array of options
   */
  public function __construct($app, $env = 'prod', $hostname = null, $remote = null, $options = array())
  {
    $this->application = $app;
    $this->environment = $env;
    parent::__construct($hostname, $remote, $options);
  }

  /**
   * Calls a request to a uri.
   */
  protected function doCall()
  {
    // reload the context everytime the call is called
    $this->context = $this->getContext(true);
    $this->resetCurrentException();
    // dispatch our request
    ob_start();
    $this->context->getController()->dispatch();
    $retval = ob_get_clean();

    // handle content-encoding first
    if($encoding = $this->context->getResponse()->getHttpHeader('Content-Encoding'))
    {
      switch(strtolower($encoding))
      {
        // Handle gzip encoding
        case 'gzip':
          $retval = $this->decodeGzip($retval);
        break;

        // Handle deflate encoding
        case 'deflate':
          $retval = $this->decodeDeflate($retval);
        break;

        default:
          throw new LogicException(sprintf('Cannot decompress encoding "%s" of the response.', $encoding));
      }
    }

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
   * Returns the current application context.
   *
   * @param  bool $forceReload  true to force context reload, false otherwise
   *
   * @return sfContext
   */
  public function getContext($forceReload = false)
  {
    if(null === $this->context || $forceReload)
    {
      sfContext::createInstance(
        sfCore::getApplication($this->application, $this->environment, false)
      );
      $this->context = sfContext::getInstance();
    }
    return $this->context;
  }

  /**
   * Shutdown function to clean up
   *
   * @return void
   */
  public function shutdown()
  {
    $this->checkCurrentExceptionIsEmpty();
  }

}
