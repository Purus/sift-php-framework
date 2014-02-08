<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorBlacklist validates than the value is not one of the configured
 * forbidden values. This is a kind of opposite of the sfValidatorChoice
 * validator. Uses regular expressions to match the value.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorBlacklistRegex extends sfValidatorBlacklist
{
    /**
     * @see sfValidatorBase
     */
    protected function doClean($value)
    {
        $forbiddenValues = $this->getOption('forbidden_values');
        if ($forbiddenValues instanceof sfCallable) {
            $forbiddenValues = $forbiddenValues->call();
        }

        foreach ($forbiddenValues as $regex) {
            $regexp = '@^' . $regex . '$@';
            if (false === $this->getOption('case_sensitive')) {
                $regexp .= 'i';
            }
            if (@preg_match($regexp, $value)) {
                throw new sfValidatorError($this, 'forbidden', array('value' => $value));
            }
        }

        return $value;
    }
}
