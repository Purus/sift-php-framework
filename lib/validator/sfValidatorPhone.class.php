<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPhone validates a value with a regular expression for phone.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorPhone extends sfValidatorRegex
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * pattern: A regex pattern compatible with PCRE (required)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorString
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    // $this->setPattern();
  }

}
