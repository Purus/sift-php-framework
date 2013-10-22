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
  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    if(!$context = $this->webDebug->getContext())
    {
      return;
    }
    $response = $context->getResponse();
    $code = $response->getStatusCode();
    $title = $response->getStatusText();
    return sprintf('<span class="%s" title="%s">%s</span>', $code == 200 ? 'success' : 'error', $title, $code);
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
