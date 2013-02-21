<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
interface sfIGenerator {
  
  /**
   * Generates classes and templates.
   *
   * @param array An array of parameters
   *
   * @return string The cache for the configuration file
   */  
  public function generate($params = array());
  
}
