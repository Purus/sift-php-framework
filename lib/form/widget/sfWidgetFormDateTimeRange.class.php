<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDateTimeRange renders a date time range
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormDateTimeRange extends sfWidgetForm {

  /**
   * 
   * @see sfWidget
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('from_date');
    $this->addRequiredOption('from_time');
    $this->addRequiredOption('to_date');
    $this->addRequiredOption('to_time');
    $this->addOption('template', 
      '<div class="datetime-range">'.
      '<span class="from">%from_date% %from_time%</span><span><strong> - </strong></span>'.
      '<span class="to">%to_date% %to_time%</span>'.
      '</div>');
  }

  /**
   * 
   * @see sfWidget
   */  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $values = array_merge(array('from_date' => '', 'to_date' => '', 'from_time' => '', 'to_time' => ''), is_array($value) ? $value : array());

    return strtr($this->translate($this->getOption('template')), array(
        '%from_date%' => $this->getOption('from_date')->render($name . '[from_date]', $value['from_date']),
        '%from_time%' => $this->getOption('from_time')->render($name . '[from_time]', $value['from_time']),
        '%to_date%' => $this->getOption('to_date')->render($name . '[to_date]', $value['to_date']),
        '%to_time%' => $this->getOption('to_time')->render($name . '[to_time]', $value['to_time']),
    ));
  }

  /**
   * 
   * @see sfWidget
   */  
  public function getStylesheets()
  {
    return array_unique(array_merge(
                    $this->getOption('from_date')->getStylesheets(),
                    $this->getOption('from_time')->getStylesheets(),
                    $this->getOption('to_date')->getStylesheets(),
                    $this->getOption('to_time')->getStylesheets()
    ));
  }

  /**
   * 
   * @see sfWidget
   */  
  public function getJavaScripts()
  {
    return array_unique(array_merge(
                    $this->getOption('from_date')->getJavaScripts(),
                    $this->getOption('from_time')->getJavaScripts(),
                    $this->getOption('to_date')->getJavaScripts(),
                    $this->getOption('to_time')->getJavaScripts()
    ));
  }

}