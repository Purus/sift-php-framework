<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfError404Exception is thrown when a 404 error occurs in an action.
 *
 * @package    Sift
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfError404Exception extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfError404Exception');
    if($message == null && sfContext::hasInstance())
    {
      $request = sfContext::getInstance()->getRequest();
      if(method_exists($request, 'getUri'))
      {
        $message = sprintf('Url: "%s"', $request->getUri());
      }
    }
    parent::__construct($message, $code);
  }

  /**
   * Forwards to the 404 action.
   *
   * @param Exception An Exception implementation instance
   */
  public function printStackTrace(Exception $exception = null)
  {
    sfContext::getInstance()->getController()->forward(
            sfConfig::get('sf_error_404_module'), 
            sfConfig::get('sf_error_404_action'));
  }
}
