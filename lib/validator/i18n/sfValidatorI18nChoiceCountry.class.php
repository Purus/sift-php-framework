<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nChoiceCountry validates than the value is a valid country.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nChoiceCountry extends sfValidatorChoice
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

    $this->addOption('culture');
    $this->addOption('countries');

    // populate choices with all countries
    $countries = array_keys(sfCulture::getInstance($this->getCulture())->getCountries(
      isset($options['countries']) ?
            $options['countries'] : null
    ));

    $this->setOption('choices', $countries);
  }
}
