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
  
  public function __construct(sfSearchQueryExpression $expression);  
  public function processExpression(sfSearchQueryExpression $expression);
  public function getResult();
  
}
