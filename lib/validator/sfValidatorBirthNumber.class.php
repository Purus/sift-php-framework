<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorBirthNumber validates a value for valid birth number. Only czech
 * and slovak numbers are supported.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Mishal <mishal at mishal dot cz>
 */
class sfValidatorBirthNumber extends sfValidatorBase {
    
  public function doClean($value)
  {
    if(!sfValidatorTools::verifyBirthNumber($value))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    return $value;
  }
  
}
