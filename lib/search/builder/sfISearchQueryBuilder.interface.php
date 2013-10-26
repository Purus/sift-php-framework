<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfISearchQueryBuilder interface.
 *
 * @package Sift
 * @subpackage search
 */
interface sfISearchQueryBuilder {

  /**
   * Sets the expression
   *
   * @param sfSearchQueryExpression $expression
   */
  public function setExpression(sfSearchQueryExpression $expression);

  /**
   * Returns the expression
   *
   * @return sfSearchQueryExpression
   */
  public function getExpression();

  /**
   * Returns the result as string
   *
   * @return string
   */
  public function getResult();

}
