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
class sfBrowser extends sfBrowserBase
{
  protected $listeners = array();
  protected $context;
  protected $currentException;

  /**
   * Calls a request to a uri.
   */
  protected function doCall()
  {
    // recycle our context object
    $this->context = $this->getContext(true);

    sfConfig::set('sf_test', true);

    // we register a fake rendering filter
    sfConfig::set('sf_rendering_filter', array('sfTestRenderingFilter', null));

    $this->resetCurrentException();

    // dispatch our request
    ob_start();
    $this->context->getController()->dispatch();
    $retval = ob_get_clean();

    // handle content-encoding first
    if ($encoding = $this->context->getResponse()->getHttpHeader('Content-Encoding')) {
      switch (strtolower($encoding)) {
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
    if (null === $this->context || $forceReload) {
      $isContextEmpty = null === $this->context;
      $context = $isContextEmpty ? sfContext::getInstance() : $this->context;

      // create configuration
      $currentApplication = $context->getApplication();

      $application = sfCore::getApplication($currentApplication->getOption('sf_app'),
              $currentApplication->getEnvironment(), $currentApplication->isDebug(), true);

      // connect listeners
      $application->getEventDispatcher()->connect('application.throw_exception', array($this, 'listenToException'));
      foreach ($this->listeners as $name => $listener) {
        $application->getEventDispatcher()->connect($name, $listener);
      }

      // create context
      $this->context = sfContext::createInstance($application);
      unset($application);

      if (!$isContextEmpty) {
        sfConfig::clear();
        sfConfig::add($this->rawConfiguration);
      } else {
        $this->rawConfiguration = sfConfig::getAll();
      }
    }

    return $this->context;
  }

  public function addListener($name, $listener)
  {
    $this->listeners[$name] = $listener;
  }

  /**
   * Gets response.
   *
   * @return sfWebResponse
   */
  public function getResponse()
  {
    return $this->context->getResponse();
  }

  /**
   * Gets request.
   *
   * @return sfWebRequest
   */
  public function getRequest()
  {
    return $this->context->getRequest();
  }

  /**
   * Gets user.
   *
   * @return sfUser
   */
  public function getUser()
  {
    return $this->context->getUser();
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
    sfToolkit::clearDirectory(sfConfig::get('sf_test_cache_dir').'/sessions');
  }

  /**
   * Listener for exceptions
   *
   * @param  sfEvent $event  The event to handle
   *
   * @return void
   */
  public function listenToException(sfEvent $event)
  {
    $this->setCurrentException($event['exception']);
  }

  /**
   * Decodes gzip-encoded content.
   *
   * @param string $text The text to decode
   * @return string
   * @throws Exception
   */
  protected function decodeGzip($text)
  {
    $decoded = @gzinflate(substr($text, 10));
    if ($decoded === false) {
      throw new Exception('Could not decode GZIPed response');
    }

    return $decoded;
  }

  /**
   * Decodes deflate-encoded content.
   *
   * @param string $text The text to decode
   * @return string
   */
  protected function decodeDeflate($text)
  {
    $header = unpack('n', substr($text, 0, 2));

    return (0 == $header[1] % 31) ? gzuncompress($text) : gzinflate($text);
  }

}
