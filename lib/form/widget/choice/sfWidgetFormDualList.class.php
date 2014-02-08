<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormDualList represents a dual list.
 *
 * @package    Sift
 * @subpackage form_widget
 *
 * @see http://ux.stackexchange.com/questions/6122/does-the-average-user-understand-the-standard-html-multiple-select-box
 * @see http://stackoverflow.com/questions/2852995/whats-this-ui-pattern-called
 * @see http://ux.stackexchange.com/questions/3418/what-is-the-best-ui-for-multi-select-from-a-list
 * @see http://ux.stackexchange.com/questions/2065/alternatives-to-a-dual-list-for-selecting-a-bunch-of-items-from-a-long-list
 */
class sfWidgetFormDualList extends sfWidgetFormChoice {

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:                  An array of possible choices (required)
   *  * truncate:                 Truncate the choice text? Integer (of false to disable)
   *  * class:                    The main class of the widget
   *  * class_select:             The class for the two select tags
   *  * label_available:          The label for available
   *  * label_associated:         The label for associated
   *  * label_select_all          The label for select all filter
   *  * label_unselect_all        The label for unselect all filter
   *  * label_inverse_selection   The label for inverse selection filter
   *  * label_filter_placeholder: The label for filter input
   *  * available_button:         The HTML for available button
   *  * associate_button:         The HTML for associate button
   *  * associated_first:         Whether the associated list if first (true by default)
   *  * template:                 The HTML template to use to render this widget
   *                              The available placeholders are:
   *                                * associated_list
   *                                * available_list
   *                                * label_associated
   *                                * label_available
   *                                * label_select_all
   *                                * label_unselect_all
   *                                * label_inverse_selection
   *                                * label_filter_placeholder
   *                                * available_button
   *                                * associate_button
   *                                * class
   *                                * associated_count
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormChoice
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('class', 'dual-list');
    $this->addOption('associated_first', true);
    $this->addOption('label_available', 'Available');
    $this->addOption('label_associated', 'Selected');
    $this->addOption('label_select_all', 'Select all');
    $this->addOption('label_unselect_all', 'Unselect all');
    $this->addOption('label_inverse_selection', 'Inverse selection');
    $this->addOption('label_filter_placeholder', 'Filter');

    $this->addOption('asset_package', 'dual_list');

    // truncate the choice text?
    $this->addOption('truncate', 30);

    // javascript options which will be exported as data-dual-list-options
    // attribute for the div.dual-list (can be configured via option)
    $this->addOption('javascript_options', array());

    $associated_first = isset($options['associated_first']) ? $options['associated_first'] : true;

    if($associated_first)
    {
      $associatedClass = 'left';
      $availableClass = 'right';
      $iconAssociated = 'arrow-right';
      $iconUnAssociated = 'arrow-left';
    }
    else
    {
      $associatedClass = 'right';
      $availableClass = 'left';
      $iconAssociated = 'arrow-left';
      $iconUnAssociated = 'arrow-right';
    }

    $this->addOption('available_button', sprintf('<button type="button" class="btn %s-move-associated"><i class="icon-%s"></i></button>',
            $this->getOption('class'), $iconAssociated));
    $this->addOption('associate_button', sprintf('<button type="button" class="btn %s-move-available"><i class="icon-%s"></i></button>',
            $this->getOption('class'), $iconUnAssociated));

    $this->addOption('template', <<<EOF
  <div class="%class%-inner">
    <div class="%class%-available %class%-$availableClass">
      <div class="%class%-header">
        <h4 class="%class%-label">%label_available% <span class="%class%-available-count">%available_count%</span></h4>
        <div class="btn-group">
          <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li>
              <a href="#" class="dual-list-select-all">%label_select_all%</a>
              <a href="#" class="dual-list-unselect-all">%label_unselect_all%</a>
              <a href="#" class="dual-list-invert-selection">%label_inverse_selection%</a>
            </li>
          </ul>
        </div>
        <div class="%class%-filters input-append">
          <input type="text" class="ignore" placeholder="%label_filter_placeholder%" />
          <button type="button" class="btn"><i class="icon-remove-sign"></i></button>
        </div>
      </div>
      <div class="%class%-items %class%-items">
      %available_list%
      </div>
      <div class="%class%-footer"></div>
      <div class="ui-resizable-handle ui-resizable-s dual-list-resizable-handle"></div>
    </div>
    <div class="%class%-buttons">
      <div>
        %associate_button%
        %available_button%
      </div>
    </div>
    <div class="%class%-associated %class%-$associatedClass">
      <div class="%class%-header">
        <h4 class="%class%-label">%label_associated% <span class="%class%-associated-count">%associated_count%</span></h4>
        <div class="btn-group">
          <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li>
              <a href="#" class="dual-list-select-all">%label_select_all%</a>
              <a href="#" class="dual-list-unselect-all">%label_unselect_all%</a>
              <a href="#" class="dual-list-invert-selection">%label_inverse_selection%</a>
            </li>
          </ul>
        </div>
        <div class="%class%-filters input-append">
          <input type="text" class="ignore" placeholder="%label_filter_placeholder%"  />
          <button type="button" class="btn"><i class="icon-remove-sign"></i></button>
        </div>
      </div>
      <div class="%class%-items">
      %associated_list%
      </div>
      <div class="%class%-footer"></div>
      <div class="ui-resizable-handle ui-resizable-s dual-list-resizable-handle"></div>
    </div>
  </div>
EOF
    );
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $selectedValues The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $selectedValues = null, $attributes = array(), $errors = array())
  {
    if('[]' != substr($name, -2))
    {
      $name .= '[]';
    }

    if(is_null($selectedValues))
    {
      $selectedValues = array();
    }

    $choices = $this->getOption('choices');
    if($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $associated = array();
    $unassociated = array();
    $positions = array();

    $i = 1;
    foreach($choices as $choice)
    {
      $positions[$choice] = $i;
      $i++;
    }

    $associatedChoices = array();

    // set associated
    foreach($selectedValues as $key => $selectedValue)
    {
      $associated[$selectedValue] = array(
          'value' => $choices[$selectedValue],
          'position' => $positions[$choices[$selectedValue]]
      );
      $associatedChoices[] = $choices[$selectedValue];
    }

    // set unassociated
    foreach($choices as $key => $choice)
    {
      if(!in_array($choice, $associatedChoices))
      {
        $unassociated[$key] = array(
          'value' => $choice,
          'position' => $positions[$choice]
        );
      }
    }

    $associatedHtml = array();
    foreach($associated as $key => $item)
    {
      $associatedHtml[] = $this->renderItem($key, $item, $name, true);
    }

    $unAssociatedHtml = array();
    foreach($unassociated as $key => $value)
    {
      $unAssociatedHtml[] = $this->renderItem($key, $value, $name);
    }

    $attributes = array(
      'class' => $this->getOption('class')
    );

    if($jsOptions = $this->getOption('javascript_options'))
    {
      $attributes['data-dual-list-options'] = sfJson::encode($jsOptions);
    }

    return $this->renderContentTag('div',
            strtr($this->getOption('template'), array(
              '%class%' => $this->getOption('class'),
              '%id%' => $this->generateId($name),
              '%available_count%' => count($unassociated),
              '%associated_count%' => count($associated),
              '%label_associated%' => $this->translate($this->getOption('label_associated')),
              '%label_available%' => $this->translate($this->getOption('label_available')),
              '%label_select_all%' => $this->translate($this->getOption('label_select_all')),
              '%label_unselect_all%' => $this->translate($this->getOption('label_unselect_all')),
              '%label_filter_placeholder%' => $this->escapeOnce($this->translate($this->getOption('label_filter_placeholder'))),
              '%label_inverse_selection%' => $this->translate($this->getOption('label_inverse_selection')),
              '%associate_button%' => $this->getOption('associate_button'),
              '%available_button%' => $this->getOption('available_button'),
              '%associated_list%' => $this->renderContentTag('ul', join("\n", $associatedHtml)),
              '%available_list%' => $this->renderContentTag('ul', join("\n", $unAssociatedHtml))
    )), $attributes);
  }

  /**
   * Renders an item
   *
   * @param string $key
   * @param string $value
   * @param string $name
   * @return string
   */
  protected function renderItem($key, $item, $name, $associated = false)
  {
    $checkboxAttributes = array(
      'type' => 'checkbox',
      'name' => $name,
      'value' => $key,
    );

    if($associated)
    {
      $checkboxAttributes['checked'] = 'checked';
    }

    $truncate = $this->getOption('truncate');
    $value = (string)$item['value'];

    return $this->renderContentTag('li',
              $this->renderTag('input', $checkboxAttributes) . "\n" .
              ($truncate ? sfText::truncate($value, $truncate) : $value), array(
                // FIXME: make this configurable and export to javascript options
                // for sorting
                'data-position' => $item['position'],
                'title' => $value
              )
          );
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return sfAssetPackage::getJavascripts($this->getOption('asset_package'));
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getStylesheets()
  {
    return sfAssetPackage::getStylesheets($this->getOption('asset_package'));
  }

  /**
   * @see sfWidgetForm
   */
  public function isLabelable()
  {
    return false;
  }

  public function __clone()
  {
    if($this->getOption('choices') instanceof sfCallable)
    {
      $callable = $this->getOption('choices')->getCallable();
      if(is_array($callable))
      {
        $callable[0] = $this;
        $this->setOption('choices', new sfCallable($callable));
      }
    }
  }

}
