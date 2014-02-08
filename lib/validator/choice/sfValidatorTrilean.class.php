<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorTrilean validates trilean values (yes, no, '')
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorTrilean extends sfValidatorBoolean
{
  /**
   * @see sfValidatorBase
   */
  public function doClean($value)
  {
    if (in_array($value, $this->getOption('true_values'))) {
      return true;
    }

    if (in_array($value, $this->getOption('false_values'))) {
      return false;
    }

    return null;
  }

  /**
   * @see sfValidatorBase
   */
  public function isEmpty($value)
  {
    return false;
  }

}
