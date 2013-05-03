<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelCache adds a panel to the web debug toolbar with a link to ignore the cache
 * on the next request.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelCache extends sfWebDebugPanel
{
  public function getTitle()
  {
    return 'reload';
  }

  public function getTitleUrl()
  {
    $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

    if (false === strpos($queryString, '_sf_ignore_cache'))
    {
      return sprintf('?%s_sf_ignore_cache=1', $queryString ? $queryString.'&' : '');
    }
    else
    {
      return '?'.$queryString;
    }
  }

  public function getPanelTitle()
  {
    return 'Reload and ignore cache';
  }

  public function getPanelContent()
  {
  }

}
