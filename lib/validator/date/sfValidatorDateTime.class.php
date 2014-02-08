<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorDateTime validates a date and a time. It also converts the input value to a valid date.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorDateTime extends sfValidatorDate
{
    /**
     * @see sfValidatorDate
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);

        $this->setOption('with_time', true);
    }
}
