<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormPartial renders a partial
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormPartial extends sfWidgetForm
{
  /**
   * Constructor
   *
   * @param string|array $options Partial name or array('partial' => $partialName)
   * @param array $attributes Array of partial variables
   */
  public function __construct($options = array(), $attributes = array())
  {
    if(!is_array($options))
    {
      $options = array(
          'partial' => $options
      );
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

    $this->addRequiredOption('partial');
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

    return get_partial($this->getOption('partial'), $variables, $this->getOption('view_name'));
  }

  /**
   * @see sfWidget
   */
  public function isLabelable()
  {
    return false;
  }

}
