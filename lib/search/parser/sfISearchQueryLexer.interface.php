<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfISearchQueryLexer interface.
 *
 * @package Sift
 * @subpackage search 
 */
interface sfISearchQueryLexer {

  public function execute($query);
  public function getTokens();

}
