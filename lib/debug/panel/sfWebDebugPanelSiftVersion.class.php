<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelSiftVersion adds a panel to the web debug toolbar with the Sift version.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelSiftVersion extends sfWebDebugPanel
{

  public function getTitle()
  {
    return '<span id="sfWebDebugSiftVersion">'.sfCore::getVersion().'</span>';
  }

  public function getPanelTitle()
  {
  }

  public function getPanelContent()
  {
  }

}
