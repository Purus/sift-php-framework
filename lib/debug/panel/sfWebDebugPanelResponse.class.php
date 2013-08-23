<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelView adds a panel to the web debug toolbar with information about the view layer.
 *
 * @package     Sift
 * @subpackage  debug_panel
 */
class sfWebDebugPanelResponse extends sfWebDebugPanel
{
  protected $response;

  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    $this->response = sfContext::getInstance()->getResponse();

    $code  = $this->response->getStatusCode();
    $title = $this->response->getStatusText();

    $color = '#D25849';
    if($code == 200)
    {
      $color = '#297A50';
    }
    return sprintf('<span style="color:%s" title="%s">%s</span>', $color, $title, $code);
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
  }

}
