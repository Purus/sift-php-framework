<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormEnhancerRich enhances forms with builtin features from jQueryUI
 *
 * @package    Sift
 * @subpackage form
 */
class sfFormEnhancerRich extends sfFormEnhancer {

  /**
   * Enhances widget. Tries to find method for widget class.
   * sfWidgetFormDate will be enhanced by method name: enhanceWidgetDate()
   * with arguments: $widget, $validator.
   *
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   * @return void
   */
  public function enhanceWidget(sfWidget $widget, sfValidatorBase $validator = null)
  {
    $class = str_replace('sfWidgetForm', '', get_class($widget));
    $method = sprintf('enhanceWidget%s', $class);

    if(method_exists($this, $method))
    {
      return call_user_func_array(array($this, $method), array($widget, $validator));
    }

    return parent::enhanceWidget($widget, $validator);
  }

  /**
   * Returns spinner options for the widget
   *
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   * @return string
   */
  public function getSpinnerOptions(sfWidget $widget, $validator = null)
  {
    $options = array();

    if($validator)
    {
      if(($max = $validator->getOption('max')) !== null)
      {
        $options['max'] = $max;
      }

      if(($min = $validator->getOption('min')) !== null)
      {
        $options['min'] = $min;
      }

      // this is a floating number validator
      if($validator instanceof sfValidatorNumber)
      {
        $options['numberFormat'] = $widget instanceof sfWidgetFormPrice ? 'C' : 'n';
      }
    }

    if($step = $widget->getOption('step'))
    {
      $options['step'] = $step;
    }

    return count($options) ? sfJson::encode($options) : false;
  }

  /**
   * Returns datepicker options in JSON format
   *
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   * @return string
   */
  public function getDatePickerOptions(sfWidget $widget, $validator = null)
  {
    $options = array(
      'dateFormat' => $this->convertDateFormat($widget->getOption('input_pattern'))
    );

    if($validator)
    {
      switch(get_class($validator))
      {
        case 'sfValidatorDate':

          if($min = $validator->getOption('min'))
          {
            $options['minDate'] = sprintf('new Date(%s * 1000)', strtotime($min));
          }

          if($max = $validator->getOption('max'))
          {
            $options['maxDate'] = sprintf('new Date(%s * 1000)', strtotime($max));
          }

        break;
      }
    }

    return count($options) ? sfJson::encode($options) : false;
  }

  /**
   * Returns datepicker options in JSON format
   *
   * @param sfWidget $widget
   * @param sfValidatorBase $validator
   * @return string
   */
  public function getSelectOptions(sfWidget $widget, $validator = null)
  {
    $options = array();

    // we mark multiple checkboxes as rich, so the are more user friendly
    $class = $widget->getAttribute('class');
    if($class)
    {
      // preserve existing classes
      $class = join(' ', array_unique(array_merge(explode(' ', $class), array(
        'rich'
      ))));
    }
    else
    {
      $class = 'rich';
    }

    $widget->setAttribute('class', $class);

    return count($options) ? sfJson::encode($options) : false;
  }

  /**
   * Converts dateformat from I18n format to jquery UI datepicker format
   *
   * @param string $format
   * @link http://api.jqueryui.com/datepicker/#utility-formatDate
   */
  public static function convertDateFormat($format)
  {
    static $dateReplacements, $timeReplacements;

    if(!$dateReplacements)
    {
      $dateReplacements = array(
        'EEEE' => 'DD',
        'EE' => 'D',
        'yyyy' => 'yy',
        'Y' => 'yy',
        'yy' => 'y',
        'MMMM' => 'MM',
        'MMM' => 'M',
        'MM' => 'dd',
        'y' => 'yy',
        'M' => 'm',
        'dd' => 'd',
      );
    }

    if(!$timeReplacements)
    {
      $timeReplacements = array(
        'HH:mm:ss' => 'HH:mm:ss',
        'HH' => 'HH',
        'H' => 'H',
        'hh' => 'hh',
        'h' => 'l',
      );
    }

    $dateFormat = strtr($format, $dateReplacements);
    $dateFormat = strtr($dateFormat, array_combine(
            array_keys($timeReplacements), array_fill(0, count($timeReplacements), '')));

    $timeFormat = strtr($format, $timeReplacements);
    $timeFormat = strtr($timeFormat, array_combine(
            array_keys($dateReplacements), array_fill(0, count($dateReplacements), '')));

    return array(trim($dateFormat), trim($timeFormat));
  }

}
