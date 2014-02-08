<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nChoiceLanguage validates than the value is a valid timezone.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nChoiceTimezone extends sfValidatorChoice
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorChoice
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $allTimezones = sfCulture::getInstance()->getTimeZones();

    $timezones = array();
    foreach ($allTimezones as $group => $groupTimezones) {
      $timezones = array_merge($timezones, array_keys($groupTimezones));
    }
    $this->setOption('choices', $timezones);
  }

}
