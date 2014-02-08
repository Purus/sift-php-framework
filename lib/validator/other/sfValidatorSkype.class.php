<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSkype validates skype username
 *
 * @package Sift
 * @subpackage validator
 */
class sfValidatorSkype extends sfValidatorRegex
{
  /**
   * Skype name matching pattern
   *
   * @var string
   * @link http://stackoverflow.com/questions/12746862/regular-expressions-for-skype-name-in-php
   */
  public static $pattern = '/^[a-z][a-z0-9\.,\-_]{5,31}$/i';

  /**
   * Configures the current validator.
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorRegex
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setMessage('invalid', 'Skype username "%value%" is not valid.');
    $this->setOption('pattern', self::$pattern);
  }

}
