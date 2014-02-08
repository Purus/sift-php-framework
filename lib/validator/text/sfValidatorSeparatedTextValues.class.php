<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorSeparatedTextValue validates text separated with "," (comma)
 * or other separator. Usefull when validating object tags.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorSeparatedTextValues extends sfValidatorBase
{
    /**
     * Configures the current validator.
     *
     * Available options:
     *
     *  * separator:  Values separator (defaults to ",")
     *  * trim:  Trim whitespace from values?
     *  * sanitize:  Sanitize HTML code from values?
     *
     * @param array $options  An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorBase
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);

        $this->addOption('separator', ',');
        $this->addOption('clean_whitespace', true);
        $this->addOption('sanitize', true);

        $this->addOption('lowercase', true);

        $this->setOption('empty_value', '');
    }

    /**
     * @see sfValidatorBase
     */
    protected function doClean($value)
    {
        // convert to string
        $clean = (string)$value;

        $separator = $this->getOption('separator');

        // trim separator from end and beginning
        $clean = trim(trim($clean), $separator);
        $values = explode($separator, $value);

        if ($this->getOption('clean_whitespace')) {
            $values = sfInputFilters::filterVar($values, array('sfValidatorSeparatedTextValues::cleanWhitespace'));
        }

        if ($this->getOption('lowercase')) {
            $values = sfInputFilters::filterVar($values, array(sfUtf8::lower));
        }

        foreach ($values as $v => $value) {
            if (empty($value)) {
                unset($values[$v]);
            }
        }

        // glue it back to one string
        $clean = join($separator, $values);

        return $clean;
    }

    public static function cleanWhitespace($value)
    {
        return trim($value);
    }

}
