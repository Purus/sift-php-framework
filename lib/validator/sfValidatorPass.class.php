<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPass is an identity validator. It simply returns the value unmodified. 
 *
 * @package    Sift
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorPass extends sfValidatorBase
{
  /**
   * Dummy constructor, which does nothing
   * 
   * @param array $options
   * @param array $messages 
   */
  public function __construct($options = array(), $messages = array())
  {    
  }
  
  /**
   * @see sfValidatorBase
   */
  public function clean($value)
  {
    return $this->doClean($value);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    return $value;
  }
    
  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    return array();
  }
  
}
