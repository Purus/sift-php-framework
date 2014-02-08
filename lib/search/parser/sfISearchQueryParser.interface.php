<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfISearchQueryParser interface.
 *
 * @package Sift
 * @subpackage search
 */
interface sfISearchQueryParser
{
  /**
   * Set the query lexer
   *
   * @param sfISearchQueryLexer $lexer
   */
  public function setLexer(sfISearchQueryLexer $lexer);

  /**
   * Returns the query lexer
   *
   * @return sfISearchQueryLexer
   */
  public function getLexer();

  /**
   * Parse the query
   *
   * @param string $query
   * @return sfSearchQueryExpression
   */
  public function parse($query);

}
