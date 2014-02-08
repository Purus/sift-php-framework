<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCSRFToken checks that the token is valid.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorCSRFToken extends sfValidatorBase
{
  /**
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('token');

    $this->setOption('required', true);

    $this->addMessage('csrf_attack', 'This session has expired. Please reload the page and try again.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if ($value != $this->getOption('token')) {
      throw new sfValidatorError($this, 'csrf_attack');
    }

    return $value;
  }

  /**
   * @return sfValidatorBase
   */
  public function getActiveMessages()
  {
    return array($this->getMessage('csrf_attack'));
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    return false;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    return false;
  }

}
