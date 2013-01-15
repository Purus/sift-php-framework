<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Request for console usage.
 *
 * @package    Sift
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfConsoleRequest extends sfRequest
{
  /**
   * Initializes this sfRequest.
   *
   * @param sfContext A sfContext instance
   * @param array   An associative array of initialization parameters
   * @param array   An associative array of initialization attributes
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws sfInitializationException If an error occurs while initializing this Request
   */
  public function initialize($context, $parameters = array(), $attributes = array())
  {
    parent::initialize($context, $parameters, $attributes);

    $this->getParameterHolder()->add($_SERVER['argv']);
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
  }

  public function getIp()
  {
    return false;
  }

  public function getIpForwardedFor()
  {
    return false;
  }

  public function getHostname()
  {
    return false;
  }
  
  public function getHost()
  {
    return false;
  }

  public function getUserAgent()
  {
    return false;
  }

  public function getCookie()
  {
    return false;
  }

  public function isAjax()
  {
    return false;
  }
  
}
