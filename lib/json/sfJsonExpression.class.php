<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfJsonExpression class is a simple wrapper around JS code.
 *
 * This class simply holds a string with a native Javascript Expression,
 * so objects | arrays to be encoded with sfJson can contain native
 * Javascript Expressions.
 *
 * Example:
 * <code>
 * $foo = array(
 *     'integer' => 9,
 *     'string'  => 'test string',
 *     'function' => sfJsonExpression(
 *         'function(){ window.alert("javascript function encoded by sfJson") }'
 *     ),
 * );
 *
 * sfJson::encode($foo, true));
 * // it will returns json encoded string:
 * // {"integer":9,"string":"test string","function":function(){window.alert("javascript function encoded by sfJson")}}
 * </code>
 *
 * @package    Sift
 * @subpackage json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */
class sfJsonExpression {

  /**
   * Storage for javascript expression.
   *
   * @var string
   */
  protected $_expression;

  /**
   * Constructor
   *
   * @param  string $expression the expression to hold.
   * @return void
   */
  public function __construct($expression)
  {
    $this->_expression = (string) $expression;
  }

  /**
   * Cast to string
   *
   * @return string holded javascript expression.
   */
  public function __toString()
  {
    return $this->_expression;
  }

}
