<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSelectCheckbox represents an array of checkboxes.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormSelectCheckbox extends sfWidgetFormChoiceBase
{
    /**
     * Constructor.
     *
     * Available options:
     *
     *  * choices:         An array of possible choices (required)
     *  * label_separator: The separator to use between the input checkbox and the label
     *  * class:           The class to use for the main <ul> tag
     *  * separator:       The separator to use between each input checkbox
     *  * formatter:       A callable to call to format the checkbox choices
     *                     The formatter callable receives the widget and the array of inputs as arguments
     *  * template:        The template to use when grouping option in groups (%group% %options%)
     *
     * @param array $options    An array of options
     * @param array $attributes An array of default HTML attributes
     *
     * @see sfWidgetFormChoiceBase
     */
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);

        $this->addOption('class', 'checkbox-list');
        $this->addOption('label_separator', ' ');
        $this->addOption('separator', "\n");
        $this->addOption('formatter', array($this, 'formatter'));
        $this->addOption('template', '%group% %options%');
    }

    /**
     * Renders the widget.
     *
     * @param  string $name       The element name
     * @param  string $value      The value selected in this widget
     * @param  array  $attributes An array of HTML attributes to be merged with the default HTML attributes
     * @param  array  $errors     An array of errors for the field
     *
     * @return string An HTML tag string
     *
     * @see sfWidgetForm
     */
    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        if ('[]' != substr($name, -2)) {
            $name .= '[]';
        }

        if (null === $value) {
            $value = array();
        }

        $choices = $this->getChoices();

        // with groups?
        if (count($choices) && is_array(current($choices))) {
            $parts = array();
            foreach ($choices as $key => $option) {
                $parts[] = strtr(
                    $this->getOption('template'),
                    array('%group%' => $key, '%options%' => $this->formatChoices($name, $value, $option, $attributes))
                );
            }

            return implode("\n", $parts);
        } else {
            return $this->formatChoices($name, $value, $choices, $attributes);
        }
    }

    protected function formatChoices($name, $value, $choices, $attributes)
    {
        // reset attributes, fixes problems with
        // attributes like "disabled" to appear in the label tag!
        $this->attributes = array();

        $inputs = array();
        foreach ($choices as $key => $option) {
            $baseAttributes = array(
                'name'  => $name,
                'type'  => 'checkbox',
                'value' => self::escapeOnce($key),
                'id'    => $id = $this->generateId($name, self::escapeOnce($key)),
            );

            if ((is_array($value) && in_array(strval($key), $value))
                || (is_string($value)
                    && strval($key) == strval($value))
            ) {
                $baseAttributes['checked'] = 'checked';
            }

            $labelAttributes = array(
                'for' => $id
            );

            if (sfWidget::isAriaEnabled()) {
                $labelId = sprintf('%s_label', $id);
                // overwrite attribute!
                $attributes['aria-labelledby'] = $labelId;
                $labelAttributes['id'] = $labelId;
            }

            $inputs[$id] = array(
                'input'   => $this->renderTag('input', array_merge($baseAttributes, $attributes)),
                'checked' => isset($baseAttributes['checked']) ? true : false,
                'label'   => $this->renderContentTag('label', self::escapeOnce($option), $labelAttributes),
                'option'  => $option
            );

        }

        return call_user_func($this->getOption('formatter'), $this, $inputs);
    }

    public function formatter($widget, $inputs)
    {
        $attributes = array();
        $listAtttibutes = array();

        if (sfWidget::isAriaEnabled()) {
            $attributes['role'] = 'list';
            $listAtttibutes['role'] = 'listitem';
        }

        if ($class = $this->getOption('class')) {
            $attributes['class'] = $class;
        }

        $rows = array();
        foreach ($inputs as $input) {
            $rows[] = $this->renderContentTag(
                'li',
                $input['input'] . $this->getOption('label_separator') . $input['label'],
                $listAtttibutes
            );
        }

        return !$rows ? '' : $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), $attributes);
    }

}
