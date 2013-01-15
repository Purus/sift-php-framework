<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaForEach duplicates a given widget multiple times in a widget schema.
 *
 * @package    Sift
 * @subpackage form_widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfWidgetFormSchemaForEach extends sfWidgetFormSchema
{
  /**
   * Constructor.
   *
   * @param sfWidgetFormSchema $widget      An sfWidgetFormSchema instance
   * @param integer            $count       The number of times to duplicate the widget
   * @param array              $options     An array of options
   * @param array              $attributes  An array of default HTML attributes
   * @param array              $labels      An array of HTML labels
   *
   * @see sfWidgetFormSchema
   */
  public function __construct(sfWidgetFormSchema $widget, $count, $options = array(), $attributes = array(), $labels = array())
  {
    parent::__construct(array_fill(0, $count, $widget), $options, $attributes, $labels);
  }
}