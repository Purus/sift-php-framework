<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filter interface
 *
 * @package    Sift
 * @subpackage filter
 */
interface sfIFilter {

  /**
   * Excecutes the filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute(sfFilterChain $filterChain);

  /**
   * Initializes the filter
   *
   * @param sfContext $context The current context
   * @param array $parameters
   */
  public function initialize(sfContext $context, $parameters = array());

}
