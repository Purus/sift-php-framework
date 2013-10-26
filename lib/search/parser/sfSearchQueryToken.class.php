<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchQueryToken class.
 *
 * @package Sift
 * @subpackage search
 */
class sfSearchQueryToken {

  const STRING = 1;
  const SPACE = 2;
  const QUOTE = 3;
  const PLUS = 4;
  const MINUS = 5;
  const BRACE_OPEN = 6;
  const BRACE_CLOSE = 7;
  const LOGICAL_AND = 8;
  const LOGICAL_OR = 9;
  const COLON = 10;

  /**
   * Token type
   *
   * @var int
   */
  public $type;

  /**
   * Token contents
   *
   * @var string
   */
  public $token;

  /**
   * Contructs a new sfSearchQueryToken
   *
   * @param int $type
   * @param string $token
   */
  public function __construct($type, $token)
  {
    $this->type = $type;
    $this->token = $token;
  }

}
