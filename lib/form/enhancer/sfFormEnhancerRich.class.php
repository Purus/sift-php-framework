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
 * @subpackage form_enhancer
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
   * Enhances i18n aggregate widget
   *
   * @param sfWidgetFormI18nAggregate $widget
   * @param sfValidatorBase|null $validator
   */
  public function enhanceWidgetI18nAggregate(sfWidgetFormI18nAggregate $widget, $validator)
  {
    // enhances child widget
    $this->enhanceWidget($widget->getOption('widget'));
  }

  public function enhanceWidgetFilterDate(sfWidgetFormFilterDate $widget, $validator)
  {
    $this->enhanceWidget($widget->getOption('to'));
    $this->enhanceWidget($widget->getOption('from'));
  }

  public function enhanceWidgetFilterDateTime(sfWidgetFormFilterDateTime $widget, $validator)
  {
    $this->enhanceWidget($widget->getOption('to'));
    $this->enhanceWidget($widget->getOption('from'));
  }

  public function enhanceWidgetDateRange(sfWidgetFormDateRange $widget, $validator)
  {
    $this->enhanceWidget($widget->getOption('to'));
    $this->enhanceWidget($widget->getOption('from'));
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
    list($dateFormat, $timeFormat) = self::convertDateFormat(
                                        $widget->getOption('input_pattern'),
                                        $widget->getOption('format_pattern')
                                      );

    $options = array(
      'dateFormat' => $dateFormat
    );

    if($widget instanceof sfWidgetFormDateTime)
    {
      $options['timeFormat'] = $timeFormat;
    }

    if($validator)
    {
      switch(get_class($validator))
      {
        case 'sfValidatorDate':

          if($min = $validator->getOption('min'))
          {
            // export as miliseconds so the javascript date can be succesfully created
            $options['minDate'] = strtotime($min) * 1000;
          }

          if($max = $validator->getOption('max'))
          {
            // export as miliseconds so the javascript date can be succesfully created
            $options['maxDate'] = strtotime($max) * 1000;
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
   * Converts dateformat from I18n format to jQuery UI datepicker format
   *
   * @param string $format
   * @link http://api.jqueryui.com/datepicker/#utility-formatDate
   * @link http://snipplr.com/view/41329/
   * @link http://trac.symfony-project.org/wiki/formatDateHowTo
   * @todo Implement more accurate conversion
   * @return array($dateFormat, $timeFormat)
   */
  public static function convertDateFormat($format)
  {
    $knownFormats = array(
      // input pattern: d
      // 25.5.2013 	d.M.yyyy
      'd.M.yyyy' => array('d.m.yy', ''),
      // input pattern g
      // 25.5.2013 10:15 d.M.yyyy H:mm
      'd.M.yyyy H:mm' => array('d.m.yy', 'H:mm'),
    );

    if(isset($knownFormats[$format]))
    {
      return $knownFormats[$format];
    }

    return array($format, $format);
  }

}
