<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nEnabledCultures represents a widget for selecting application's enabled cultures
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormI18nChoiceEnabledLanguages extends sfWidgetFormChoice
{
  /**
   *
   * @see sfWidgetForm
   */
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $cultures = sfConfig::get('sf_i18n_enabled_cultures', array());

    // populate choices with all languages
    $culture = isset($options['culture']) ? $options['culture'] :
      (sfContext::hasInstance() ? sfContext::getInstance()->getUser()->getCulture() : 'en');

    $allLanguages = sfCulture::getInstance($culture)->getLanguages();
    $languages = array();
    foreach ($cultures as $culture) {
      $languages[$culture] = $allLanguages[substr($culture, 0, 2)];
    }

    $this->setOption('translate_choices', false);

    $this->addOption('culture');
    $this->addOption('add_empty', false);

    $addEmpty = isset($options['add_empty']) ? $options['add_empty'] : false;
    if (false !== $addEmpty) {
      $languages = array_merge(array('' => true === $addEmpty ? '' : $addEmpty), $languages);
    }

    $this->setOption('choices', $languages);
  }

}
