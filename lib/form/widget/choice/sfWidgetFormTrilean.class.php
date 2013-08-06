<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTrilean represents a select tag for values: yes, no and null.
 *
 * @package Sift
 * @subpackage form_widget
 */
class sfWidgetFormTrilean extends sfWidgetFormChoice {

  /**
   *
   */
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('label_no', 'no');
    $this->addOption('label_yes', 'yes');
    $this->addOption('label_undefined', '');

    $this->setOption('choices', new sfCallable(array($this, 'getChoices')));
  }

  /**
   * Returns choices
   *
   * @return array
   */
  public function getChoices()
  {
    return array(
      '' => $this->getOption('label_undefined') ? $this->translate($this->getOption('label_undefined')) : $this->getOption('label_undefined'),
       1 => $this->translate($this->getOption('label_yes')),
       0 => $this->translate($this->getOption('label_no'))
    );
  }

}
