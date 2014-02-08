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
class sfWebDebugPanelUser extends sfWebDebugPanel
{
  /**
   *
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    if (!$context = $this->webDebug->getContext()) {
      return;
    }

    if ($context->getUser()->isAuthenticated()) {
      return sprintf('Authenticated: %s', (string) $context->getUser());
    }

    return 'Anonymous';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return 'User';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getIcon()
  {
    return sfWebDebugIcon::get('user');
  }

  /**
   *
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    if (!$context = $this->webDebug->getContext()) {
      return;
    }
    $user = $context->getUser();

    return $this->webDebug->render($this->getOption('template_dir').'/panel/user.php', array(
      'culture' => $user->getCulture(),
      'timezone' => $user->getTimezone(),
      'credentials' => $user instanceof sfISecurityUser ? $user->getCredentials() : array(),
      'attributes' => sfDebug::removeObjects(sfDebug::flattenParameterHolder($user->getAttributeHolder()))
    ));
  }

}
