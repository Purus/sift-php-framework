<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nChoiceCurrency validates than the value is a valid currency code.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nChoiceCurrency extends sfValidatorChoice
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * countries: An array of country codes to use (ISO 3166)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorChoice
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('currencies');

    // populate choices with all countries
    $currencies = array_keys(sfCulture::getInstance()->getCurrencies());

    // restrict countries to a sub-set
    if (isset($options['currencies'])) {
      if ($problems = array_diff($options['currencies'], $currencies)) {
        throw new InvalidArgumentException(sprintf('The following currencies do not exist: %s.', implode(', ', $problems)));
      }

      $currencies = $options['currencies'];
    }

    sort($currencies);

    $this->setOption('choices', $currencies);
  }

}
