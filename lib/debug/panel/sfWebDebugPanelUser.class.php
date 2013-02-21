<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelUser adds an information about you.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelUser extends sfWebDebugPanel {

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    $user = sfContext::getInstance()->getUser();

    if($user->isAuthenticated())
    {
      return 'Authenticated as: ' . (string) $user;
    }

    return 'Anonymous user';
  }

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return 'User';
  }

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    $user = sfContext::getInstance()->getUser();
    if($user->isAuthenticated())
    {
      $html = array();

      foreach($user->getCredentials() as $credential)
      {
        $html[] = sprintf('<tr><td>%s</td></tr>', $credential);
      }

      return sprintf('<h2>Credentials</h2><table>
        <thead><tr><td>Credential</td></tr></thead>
          <tbody>%s</tbody>
        </table>', join("\n", $html));
    }
  }

}
