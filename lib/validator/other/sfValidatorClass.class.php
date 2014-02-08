<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorClass validates a value ot it is an existing PHP class name.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorClass extends sfValidatorString
{
    /**
     * Configures the current validator.
     *
     * Available options:
     *
     *  * extend: Whether the value has to extend given class or interface. String or array of values
     *
     * @param array $options  An array of options
     * @param array $messages An array of error messages
     *
     * @throws sfConfigurationException If extend class(es) are misconfigured.
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);

        // what class(es) should the checked value extend?
        // can be an array
        $this->addOption('extend');

        $this->setMessage('invalid', 'Class "%value%" is invalid.');

        if (isset($options['extend'])) {
            if (!is_array($options['extend'])) {
                $options['extend'] = array($options['extend']);
            }

            foreach ($options['extend'] as $extend) {
                if (!class_exists($extend) && !interface_exists($extend)) {
                    throw new sfConfigurationException(sprintf(
                        'Invalid option "extend". Class or interface "%s" does not exist.',
                        $extend
                    ));
                }
            }
        }

    }

    /**
     * @see   sfValidatorString
     * @throw ReflectionException If requested extend check contains nonexisting class names
     */
    protected function doClean($value)
    {
        $clean = parent::doClean($value);

        if (!class_exists($clean)) {
            throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }

        // we have a check for extend
        if ($extend = $this->getOption('extend')) {
            if (!is_array($extend)) {
                $extend = array($extend);
            }

            $reflection = new sfReflectionClass($clean);
            if (!$reflection->isSubclassOfOrIsEqual($extend)) {
                throw new sfValidatorError($this, 'invalid', array('value' => $value));
            }
        }

        return $clean;
    }

    /**
     * Returns active messages (based on active options). This is usefull for
     * i18n extract task.
     *
     * @return array
     */
    public function getActiveMessages()
    {
        return array($this->getMessage('invalid'));
    }

}
