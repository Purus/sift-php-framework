<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormComponent renders a component
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormComponent extends sfWidgetForm
{
  /**
   * Constructor
   *
   * @param string|array $options Component moduleName/component or array('component' => array('moduleName', 'component'))
   * @param array $attributes Array of partial variables
   */
  public function __construct($options = array(), $attributes = array())
  {
    if(!is_array($options))
    {
      list($module, $component) = explode('/', $options);
      $options = array(
        'component' => array($module, $component)
      );
    }

    // short option like: moduleName/component will be converted
    // to array (moduleName, component)
    if(isset($options['component']) && !is_array($options['component']))
    {
      list($module, $component) = explode('/', $options['component']);
      $options['component'] = array($module, $component);
    }

    parent::__construct($options, $attributes);
  }

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * partial:      The partial name
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidget
   */
  public function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('component');
    $this->addOption('view_name');
  }

  /**
   *
   * @see sfWidget
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes['name'] = $name;
    $attributes['value'] = $value;
    $attributes['errors'] = $errors;

    $variables = array_merge($this->getAttributes(), $attributes);
    $component = $this->getOption('component');

    return get_component($component[0], $component[1], $variables, $this->getOption('view_name'));
  }

  /**
   * @see sfWidget
   */
  public function isLabelable()
  {
    return false;
  }

}
