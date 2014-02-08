<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormChoice represents a choice widget where you can select multiple values.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormChoiceMany extends sfWidgetFormChoice
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

        $this->setOption('multiple', true);
    }

}
