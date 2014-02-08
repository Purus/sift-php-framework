<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Utility class used for loading result from module security.yml
 *
 * @package Sift
 * @subpackage security
 */
class sfSecurityCheckResult
{
  public $security = array();

  /**
   * Contructs the object
   *
   * @param string $module
   */
  public function __construct($module)
  {
    if ($fn = sfConfigCache::getInstance()->checkConfig('modules/' . $module . '/config/security.yml', true)) {
      require($fn);
    }
  }

  /**
   * Returns secutiry setting
   *
   * @return array
   */
  public function getSecurity()
  {
    return $this->security;
  }

}
