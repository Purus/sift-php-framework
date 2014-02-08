<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTime represents a time widget.
 *
 * @package    Sift
 * @subpackage form_widget
 */
class sfWidgetFormTime extends sfWidgetFormDate
{
    /**
     * Constructor.
     *
     * Available options:
     *
     *  * time_pattern:          The time pattern. (short, long, full), or custom format according to sfI18nDateFormatter formats.
     *
     * @param array $options    An array of options
     * @param array $attributes An array of default HTML attributes
     *
     * @see sfWidgetForm
     */
    public function __construct($options = array(), $attributes = array())
    {
        if (!isset($options['format_pattern'])) {
            $options['format_pattern'] = 't';
        }

        switch ($options['format_pattern']) {
            case 'short':
                $options['format_pattern'] = 't';
                break;

            // long time pattern
            case 'long':
                $options['format_pattern'] = 'T';
                break;

            case 'full':
            case 'with_seconds':
                $options['format_pattern'] = 'Q';
                break;
        }

        parent::__construct($options, $attributes);
    }

}
