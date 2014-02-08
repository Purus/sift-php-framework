<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryBuilderMysqlFulltext class.
 *
 * @package Sift
 * @subpackage search
 */
class sfSearchQueryBuilderMysqlFulltext extends sfSearchQueryBuilder
{
  /**
   * Proccesses the expression and builds the query for MySQL fulltext.
   *
   * @param sfSearchQueryExpression $expression
   * @return string
   * @see http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html
   */
  protected function processExpression(sfSearchQueryExpression $expression)
  {
    $query = '';
    $phrases = $expression->getPhrases();
    $subExpressions = $expression->getSubExpressions();

    foreach ($phrases as $phrase) {
      switch ($phrase->getMode()) {
        case sfSearchQueryPhrase::MODE_AND:
        case sfSearchQueryPhrase::MODE_DEFAULT:
          $phrase->isMultiWord() ? $format = '+"%s" ' : $format = "+%s ";
          break;

        case sfSearchQueryPhrase::MODE_OR:
          $phrase->isMultiWord() ? $format = '"%s" ' : $format = "%s ";
          break;

        case sfSearchQueryPhrase::MODE_EXCLUDE:
          $phrase->isMultiWord() ? $format = '-"%s" ' : $format = "-%s ";
          break;

        default:
          $phrase->isMultiWord() ? $format = '"%s" ' : $format = "%s ";
          break;
      }

      $query .= sprintf($format, str_replace('"', '\"', $phrase));
    }

    foreach ($subExpressions as $subExpression) {
      switch ($subExpression->getMode()) {
        case sfSearchQueryExpression::MODE_OR:
          $query .= sprintf('(%s)', $this->processExpression($subExpression));
          break;

        case sfSearchQueryExpression::MODE_AND:
        case sfSearchQueryExpression::MODE_DEFAULT:
          $query .= sprintf('+(%s)', $this->processExpression($subExpression));
          break;
      }
    }

    $query = preg_replace('/^(\+)/', '', $query);

    return trim($query);
  }

}
