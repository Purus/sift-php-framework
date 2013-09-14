<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nSelectCountry represents a country HTML select tag.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormI18nSelectCountry extends sfWidgetFormSelect {

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * culture:   The culture to use for internationalized strings (required)
   *  * countries: An array of country codes to use (ISO 3166)
   *  * add_empty: Whether to add a first empty value or not (false by default)
   *               If the option is not a Boolean, the value will be used as the text value
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('culture', $this->getCulture());
    $this->addOption('countries');
    $this->addOption('add_empty', false);
    $this->setOption('translate_choices', false);

    // populate choices with all countries
    $culture = $this->getOption('culture');

    $countries = sfCulture::getInstance($culture)->getCountries(isset($options['countries']) ? $options['countries'] : null);

    $addEmpty = isset($options['add_empty']) ? $options['add_empty'] : false;
    if(false !== $addEmpty)
    {
      $countries = array_merge(array('' => true === $addEmpty ? '' : $addEmpty), $countries);
    }

    $this->setOption('choices', $countries);
  }

}
