<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryLexer class.
 *
 * @package Sift
 * @subpackage search
 */
class sfSearchQueryLexer implements sfISearchQueryLexer
{
  /**
   * Token stack
   *
   * @var array
   */
  private $tokenStack = array();

  /**
   * Executes the query
   *
   * @param string $query
   */
  public function execute($query)
  {
    $this->tokenStack = array();

    $map = array(
        ' ' => sfSearchQueryToken::SPACE,
        '\t' => sfSearchQueryToken::SPACE,
        '"' => sfSearchQueryToken::QUOTE,
        '+' => sfSearchQueryToken::PLUS,
        '-' => sfSearchQueryToken::MINUS,
        '(' => sfSearchQueryToken::BRACE_OPEN,
        ')' => sfSearchQueryToken::BRACE_CLOSE,
        'and' => sfSearchQueryToken::LOGICAL_AND,
        'or' => sfSearchQueryToken::LOGICAL_OR,
        ':' => sfSearchQueryToken::COLON,
    );

    // balance the query (fix errors)
    $query = $this->balanceTokens(array(
        '(' => ')',
        '"' => '"',
        '\'' => '\''), $query);

    $tokenArray = preg_split('@(\s)|(["+():-])@', $query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    foreach ($tokenArray as $token) {
      if (isset($map[strtolower($token)])) {
        $this->tokenStack[] = new sfSearchQueryToken($map[strtolower($token)], $token);
      } else {
        $this->tokenStack[] = new sfSearchQueryToken(sfSearchQueryToken::STRING, $token);
      }
    }
  }

  /**
   * Balances the query tokens.
   *
   * @param array $tokens
   * @param string $query
   * @return string
   * @see http://stackoverflow.com/a/11075823/515871
   */
  protected function balanceTokens($tokens, $query)
  {
    $closeTokens = array_flip($tokens);
    $stringTokens = array('"' => true, '"' => true);
    $stack = array();

    for ($i = 0, $l = sfUtf8::len($query); $i < $l; ++$i) {
      $c = sfUtf8::sub($query, $i, 1);
      // push opening tokens to the stack (for " and ' only if there is no " or ' opened yet)
      if (isset($tokens[$c]) && (!isset($stringTokens[$c]) || end($stack) != $c)) {
        $stack[] = $c;
        // closing tokens have to be matched up with the stack elements
      } elseif (isset($closeTokens[$c])) {
        $matched = false;
        while ($top = array_pop($stack)) {
          // stack has matching opening for current closing
          if ($top == $closeTokens[$c]) {
            $matched = true;
            break;
          }
          // stack has unmatched opening, insert closing at current pos
          $code = sfUtf8::subReplace($query, $tokens[$top], $i, 0);
          $i++;
          $l++;
        }
        // unmatched closing, insert opening at start
        if (!$matched) {
          $code = $closeTokens[$c] . $query;
          $i++;
          $l++;
        }
      }
    }

    // any elements still on the stack are unmatched opening, so insert closing
    while ($top = array_pop($stack)) {
      $query .= $tokens[$top];
    }

    return $query;
  }

  /**
   * Returns an array of tokens
   *
   * @return array
   */
  public function getTokens()
  {
    return $this->tokenStack;
  }

}
