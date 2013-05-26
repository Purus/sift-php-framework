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
    $html = array();

    $html[] = '<tr><td><h2>Credentials:</h2></td></tr>';

    $credentials = $user->getCredentials();
    if(count($credentials))
    {
      foreach($credentials as $credential)
      {
        $html[] = sprintf('<tr><td>%s</td></tr>', $credential);
      }
    }
    else
    {
      $html[] = '<tr><td>No credentials</td></tr>';
    }

    $html[] = '<tr><td><h2>Attributes:</h2>';

    $attributes = sfDebug::flattenParameterHolder($user->getAttributeHolder());

    foreach($attributes as $name => $attributes)
    {
      if(!count($attributes))
      {
        continue;
      }

      $html[] = sprintf('<h3>%s:</h3>', $name);
      if(is_array($attributes))
      {
        $html[] = '<ul>';
        foreach($attributes as $attributeName => $attribute)
        {
          if(is_object($attribute))
          {
            if(method_exists($attribute, '__toString'))
            {
              $attribute = $attribute->__toString();
            }
            else
            {
              $attribute = sprintf('object [%s]', get_class($attribute));
            }
          }

          $html[] = sprintf('<li>%s: %s</li>', $attributeName, is_array($attribute) ? var_export($attribute, true) : $attribute);
        }
        $html[] = '</ul>';
      }
      else
      {
        $html[] = $attributes;
      }
    }

    $html[] = sprintf('</td></tr>');
    return sprintf('<table><tbody>%s</tbody></table>', join("\n", $html));
  }

}
