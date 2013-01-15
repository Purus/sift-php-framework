<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfHttpDownloadException is thrown when an error occurs in a http download.
 *
 * @package    Sift
 * @subpackage exception
 * @author     Mishal.cz <mishal at mishal dot cz>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 */
class sfHttpDownloadException extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfHttpDownloadException');
    parent::__construct($message, $code);
  }
}
