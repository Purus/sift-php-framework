<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTreeChoice represents a choice widget where you can select multiple values
 * within sfMenu object which is rendered as tree.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormTreeChoice extends sfWidgetFormChoice
{
    /**
     * @param array $options    An array of options
     * @param array $attributes An array of default HTML attributes
     *
     * @see sfWidgetFormChoice
     */
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);

        $this->addOption('multiple', isset($options['multiple']) ? (boolean)$options['multiple'] : true);

        // asset package
        $this->addOption('asset_package', 'tree');

        // multiple is only for checkboxes
        $this->addOption(
            'renderer_class',
            $this->getOption('multiple') ?
                'sfWidgetFormTreeSelectCheckbox' : 'sfWidgetFormTreeSelectRadio'
        );
    }

    /**
     * Returns the translated choices configured for this widget
     *
     * @return array  An array of strings
     */
    public function getChoices()
    {
        $choices = $this->getOption('choices');

        if ($choices instanceof sfCallable) {
            $choices = $choices->call();
        }

        if (!$this->getOption('translate_choices')) {
            return $choices;
        }

        return $choices;

        $results = array();
        foreach ($choices as $key => $choice) {
            if (is_array($choice)) {
                $results[$this->translate($key)] = $this->translateAll($choice);
            } else {
                $results[$key] = $this->translate($choice);
            }
        }

        return $results;
    }

    /**
     * @see sfWidgetForm
     */
    public function isLabelable()
    {
        return false;
    }

    public function getJavaScripts()
    {
        return sfAssetPackage::getJavascripts($this->getOption('asset_package'));
    }

    public function getStylesheets()
    {
        return sfAssetPackage::getStylesheets($this->getOption('asset_package'));
    }

}
