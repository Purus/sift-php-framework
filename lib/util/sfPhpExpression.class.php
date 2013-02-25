<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPhpExpression class is a simple wrapper around PHP code.
 *
 * This class simply holds a string with a native PHP Expression,
 * so objects or arrays to be exported as native PHP expressions without the
 * call to __set_state() static method.
 *
 * This is a workaround for a need for __set_state() methods.
 *
 * Example:
 * <code>
 * $foo = array(
 *     'integer' => 1,
 *     'string'  => 'test string',
 *     'validator' => sfPhpExpression('new sfValidatorString()')
 * );
 *
 * </code>
 *
 * @package    Sift
 * @subpackage util
 * @see        sfToolkit::varExport()
 * @link http://www.php.net/manual/en/language.oop5.magic.php#object.set-state
 */
class sfPhpExpression {

  /**
   * Storage for php expression.
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
   * @return string holded php expression.
   */
  public function __toString()
  {
    return $this->_expression;
  }

}
