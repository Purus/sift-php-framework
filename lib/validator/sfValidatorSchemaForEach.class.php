<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSchemaForEach wraps a validator multiple times in a single validator.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorSchemaForEach extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param sfValidatorBase $validator  Initial validator
   * @param integer         $count      The number of times to replicate the validator
   * @param array           $options    An array of options
   * @param array           $messages   An array of error messages
   *
   * @see sfValidatorBase
   */
  public function __construct(sfValidatorBase $validator, $count, $options = array(), $messages = array())
  {
    $fields = array();
    for ($i = 0; $i < $count; $i++)
    {
      $fields[$i] = clone $validator;
    }

    parent::__construct($fields, $options, $messages);
  }

  /**
   * @see sfValidatorBase
   */
  public function asString($indent = 0)
  {
    throw new Exception('Unable to convert a sfValidatorSchemaForEach to string.');
  }
}