<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormMetaTitleModeChoice represents a choice widget for meta title modes
 *
 * @package Sift
 * @subpackage form
 */
class sfWidgetFormMetaTitleModeChoice extends sfWidgetFormChoice {

  /**
   *
   * @see sfWidgetFormChoice
   */
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('add_empty', true);
    $this->addOption('translate_choices', true);
    $this->setOption('choices', new sfCallable(array($this, 'getChoices')));
  }

  /**
   * Returns the choices associated to the model.
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = array();

    if(false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->translate($this->getOption('add_empty'));
    }

    $choices = $choices + array(
      'append' => $this->translate('append'),
      'prepend' => $this->translate('prepend'),
      'replace' => $this->translate('replace')
    );

    return $choices;
  }

}
