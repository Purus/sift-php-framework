<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaFormatterDiv forms the form using div layout.
 *
 * @package    Sift
 * @subpackage form
 */
class sfWidgetFormSchemaFormatterDiv extends sfWidgetFormSchemaFormatter
{

 protected
  $rowFormat       = '', // unused
  $errorRowFormat  = "%errors%\n",
  $errorListFormatInARow = '%errors%',
  $helpFormat      = ' <span class="form-help-contextual" title="%help%"><span class="icon-question-sign"></span></span>',
  $decoratorFormat = "\n    %content%",
  $errorRowFormatInARow = "<label %attributes%>%error%</label>\n",
  $namedErrorRowFormatInARow = "%name%: %error%\n",
  $errorCssClass             = 'form-error';

  protected $inlineWidgets = array(
    'sfWidgetFormInputCheckbox', //'sfWidgetFormNoInput'
  );

  protected $specialWidgets = array(
    'sfWidgetFormPartial', 'sfWidgetFormComponent', 'sfWidgetFormDualList'
  );

  protected $largeWidgets = array(
    'sfWidgetFormDualList', 'sfWidgetFormI18nAggregate'
  );

  /**
   * Generates a label for the given field name.
   *
   * @param  string $name        The field name
   * @param  array  $attributes  Optional html attributes for the label tag
   *
   * @return string The label tag
   */
  public function generateLabel($name, $attributes = array())
  {
    $inline = in_array(get_class($this->widgetSchema[$name]), $this->inlineWidgets);

    if($inline)
    {
      isset($attributes['class']) ?
        $attributes['class'] .= 'inline' :
        $attributes['class'] = 'inline';
    }

    return parent::generateLabel($name, $attributes);
  }

  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null,
          $widgetAttributes = array(), sfWidgetForm $widget = null)
  {
    $inline = false;
    $large = false;
    foreach($this->largeWidgets as $largeWidget)
    {
      if($widget instanceof $largeWidget)
      {
        $large= true;
        break;
      }
    }

    foreach($this->inlineWidgets as $inlineWidget)
    {
      if($widget instanceof $inlineWidget)
      {
        $inline = true;
        break;
      }
    }

    $special = false;
    foreach($this->specialWidgets as $inlineWidget)
    {
      if($widget instanceof $inlineWidget)
      {
        $special = true;
        break;
      }
    }

    $html = array();

    $html[] = ($inline) ? '<div class="form-row inline">' : '<div class="form-row">';

    // we need to make it inline!
    $html[] = ($inline) ? '<div class="form-field-wrapper inline">' : '<div class="form-field-wrapper">';

    // inline widget like checkbox is rendered first
    if($inline)
    {
      $html[] = $field;

      if(!$special)
      {
        $html[] = $label;
      }
    }
    else
    {
      if(!$special)
      {
        $html[] = $label;
      }

      // render help
      if($large)
      {
        $html[] = $this->formatHelp($help);
      }

      $html[] = $field;

    }

    if($errors)
    {
      if(!is_array($errors))
      {
        $errors = array($errors);
      }

      // we render error only for small widgets
      // since large widgets are large :)
      if(!$large)
      {
        $html[] = strtr($this->getErrorListFormatInARow(), array(
          '%errors%'    => implode('', $this->unnestErrors($errors, '', $widgetAttributes)),
          '%field_id%'  => $widgetAttributes['id']
        ));
      }
    }

    $html[] = '</div>';

    // render help
    if(!$large)
    {
      $html[] = $this->formatHelp($help);
    }

    $html[] = '</div>';

    // place a placeholder for hidden fields if hiddenFiels is null
    $html[] = null === $hiddenFields ? '%hidden_fields%' : $hiddenFields;

    return join("\n", $html);
  }

}
