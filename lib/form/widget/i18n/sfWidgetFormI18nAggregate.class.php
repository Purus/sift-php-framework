<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nAggregate represents a widget which agregates other widget
 * for i18n usage.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormI18nAggregate extends sfWidgetForm
{
  /**
   * Configures the widget. Available options:
   *
   *  * widget:                   Instance of sfFormWidgetXYZ object (required)
   *  * cultures:                 Array of cultures to repeat the widge. sfCallable is possible too. (required)
   *  * widget_template:          Template for the widget
   *  * standalone_template:      Template for the first widget
   *  * widgets_wrapper_template: Widgets wrapper template
   *  * wrapper_template:         Wraps whole widget (%widget% placeholder)
   *  * flag_template:            Flag template (will be added to the label of the widget of add_flag is true)
   *  * add_flag:                 Add flag to the label?
   *
   * @param array $options
   * @param array $attributes
   */
  public function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('widget');
    $this->addRequiredOption('cultures');

    // widget template
    $this->addOption('widget_template', '<div>%label% %widget%%error%</div>');

    // standalone template for first culture
    $this->addOption('standalone_template', '<div class="form-i18n-standalone">%label% %widget%%error%</div>');
    // widgets wrapper template
    $this->addOption('widgets_wrapper_template', '<div class="form-i18n-aggregate">%widgets%</div>');

    // wrapper template, this wraps whole widget
    $this->addOption('wrapper_template', '%widget%');

    // flag template
    $this->addOption('flag_template', '<span class="flag flag-%culture%"></span>');

    // add flag for each?
    $this->addOption('add_flag', true);
  }

  /**
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $widget = $this->getOption('widget');

    if(!$widget || !$widget instanceof sfWidgetForm)
    {
      throw new InvalidArgumentException('Widget is missing or is not a valid widget');
    }

    // we need to set parent, so the translation work
    $widget->setParent($this->getParent());

    $cultures = $this->getOption('cultures');

    if($cultures instanceof sfCallable)
    {
      $cultures = $cultures->call();
    }

    if(!count($cultures))
    {
      throw new InvalidArgumentException('Cultures are invalid.');
    }

    if(!is_array($value))
    {
      $value = array();
    }

    $widgets = array();

    // id attribute
    $id = isset($attributes['id']) ? $attributes['id'] : $name;

    // render the widget for each culture
    foreach($cultures as $culture => $cultureName)
    {
      if(is_numeric($culture))
      {
        $culture = $cultureName;
        $cultureName = false;
      }

      if(isset($attributes['id']))
      {
        $attributes['id'] = sprintf('%s_%s', $id, $culture);
      }

      $widgetName = sprintf('%s[%s]', $name, $culture);
      $widgetValue = isset($value[$culture]) ? $value[$culture] : null;

      $label = $widget->getLabel();

      if($this->getOption('add_flag'))
      {
        $label = sprintf('%s %s', $this->getFlag($culture), $label);
      }

      if($cultureName)
      {
        $label= sprintf('%s %s', $label, $cultureName);
      }

      $widgets[$culture] = array(
        '%label%'  => $this->renderContentTag('label', $label, array(
          'for' => $this->generateId(isset($attributes['id']) ? $attributes['id'] : $widgetName, $widgetValue)
        )),
        '%widget%' => $widget->render($widgetName, $widgetValue, $attributes),
        '%error%' => $this->getErrorForCulture($name, $errors, $culture)
      );
    }

    // first widget is rendered as standalone
    $widget = array_shift($widgets);

    $html = array();
    $html[] = strtr($this->getOption('standalone_template'), $widget);

    // we have something left
    if(count($widgets))
    {
      $widgetsHtml = array();

      foreach($widgets as $culture => $widget)
      {
        $widgetsHtml[] =  strtr($this->getOption('widget_template'), $widget);
      }

      $html[] = strtr($this->getOption('widgets_wrapper_template'), array(
        '%widgets%' => join("\n", $widgetsHtml)
      ));

      unset($widgetsHtml);
    }

    return strtr($this->getOption('wrapper_template'), array('%widget%' => join("\n", $html)));
  }

  /**
   * Returns an an error for the culture
   *
   * @param string $name Widget name
   * @param array $errors
   * @param string $culture
   * @return string
   */
  protected function getErrorForCulture($name, $errors, $culture)
  {
    if(!count($errors))
    {
      return '';
    }

    foreach($errors as $error)
    {
      /* @var $error sfValidatorError */
      $arguments = $error->getArguments(true);
      if(isset($arguments['culture']) && $arguments['culture'] == $culture)
      {
        return $this->getParent()->getFormFormatter()->formatErrorsForRow($error, array(
          'for' => $this->generateId(sprintf('%s[%s]', $name, $culture), $culture)
        ));
      }
    }
    return '';
  }

  /**
   * Returns a flag markup for given culture
   *
   * @param string $culture
   * @return string
   */
  protected function getFlag($culture)
  {
    if(strlen($culture) == 5)
    {
      $culture = substr($culture, -2, 2);
    }

    $culture = strtolower($culture);

    return strtr($this->getOption('flag_template'), array(
      '%culture%' => $culture
    ));
  }

  /**
   * @see sfWidgetForm
   */
  public function isLabelable()
  {
    return false;
  }

}