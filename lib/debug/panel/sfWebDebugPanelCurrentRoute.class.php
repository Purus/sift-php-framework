<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelMemory adds an info about current module/action and route
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelCurrentRoute extends sfWebDebugPanel {

  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    $module = sfContext::getInstance()->getModuleName();
    $action = sfContext::getInstance()->getActionName();
    $route = htmlspecialchars(sfRouting::getInstance()->getCurrentInternalUri(true));

    return $module && $action ?
            sprintf('<span title="Current module/action: %s/%s, Route: %s">%s/%s</span>',
                    $module, $action,
                    $route ? $route : 'n/a',
                    $module, $action) : 'n/a';
  }

  public function getPanelTitle()
  {
  }

  public function getPanelContent()
  {
  }

}
