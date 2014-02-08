<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AssetHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Returns front application url
 *
 * @return string
 */
function admin_app_url()
{
  $url    = 'admin.' . sfContext::getInstance()->getRequest()->getBaseDomain();
  $script = $_SERVER['SCRIPT_NAME'];
  if($script == '/index.php')
  {
    $script = '/';
  }
  return 'http://' . $url . $script;
}

