<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Partial view interface
 *
 * @package Sift
 * @subpackage helper
 */
interface sfIPartialView extends sfIView {

  /**
   * Set partial vars
   *
   * @param array $partialVars
   */
  public function setPartialVars(array $partialVars);

}
