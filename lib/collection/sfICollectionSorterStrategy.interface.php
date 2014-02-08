<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfICollectionSorterStrategy is an interface for collection sorting strategies
 *
 * @package    Sift
 * @subpackage collection
 */
interface sfICollectionSorterStrategy {

  /**
   * Returns 0 if a == b, -1 if a < b,
   * 1 if a > b
   *
   * @return int
   */
  public function compareTo($a, $b);

}
