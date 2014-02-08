<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nAggregate validates an input value for supplied cultures and validators
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nAggregate extends sfValidatorAnd
{
    /**
     * Available options:
     *
     * * all_need_to_pass:   If set to true, all cultures need to pass the validation process.
     *                       If set to false(default) the error is thrown only if all are invalid.
     *
     * @see sfValidatorAnd
     */
    public function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);

        $this->addRequiredOption('cultures');

        // all validators need to pass
        $this->addOption('all_need_to_pass', true);
        // breaks on the first validator for each culture
        // error are collected for all cultures!
        $this->addOption('halt_on_error', false);
    }

    /**
     * Cleans the $value
     *
     * @param array $value
     *
     * @return array
     * @throws sfValidatorError
     * @throws sfValidatorErrorSchema
     */
    public function doClean($value)
    {
        $cultures = $this->getCultures();

        $clean = $value;
        $errors = array();

        $validators = $this->getValidators();

        $first = true;
        // loop all cultures and validate the value
        foreach ($cultures as $culture => $cultureName) {
            if (is_numeric($culture)) {
                $culture = $cultureName;
            }

            foreach ($validators as $validator) {
                if (!$first) {
                    // dynamically update validator option,
                    // so only the first is required
                    if (!$this->getOption('all_need_to_pass') && $validator->getOption('required')) {
                        $validator->setOption('required', false);
                    }
                }

                try {
                    $clean[$culture] = $validator->clean(isset($clean[$culture]) ? $clean[$culture] : null);
                } catch (sfValidatorError $e) {
                    // repack error
                    $error = new sfValidatorError($validator, $e->getCode(), array_merge(
                        $e->getArguments(true),
                        array('culture_name' => $cultureName, 'culture' => $culture)
                    ));

                    $errors[] = $error;

                    if ($this->getOption('halt_on_error')) {
                        break;
                    }
                }
            }

            $first = false;
        }

        // we have some errors
        if (count($errors)) {
            $this->throwError($errors, $value);
        }

        return $clean;
    }

    /**
     * Throws error
     *
     * @param array $errors
     * @param array $value
     *
     * @throws sfValidatorError
     * @throws sfValidatorErrorSchema
     */
    protected function throwError($errors, $value)
    {
        if ($this->getMessage('invalid')) {
            throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }

        throw new sfValidatorErrorSchema($this, $errors);
    }

    /**
     * Returns cultures
     *
     * @return array
     */
    protected function getCultures()
    {
        $cultures = $this->getOption('cultures');

        if ($cultures instanceof sfCallable) {
            $cultures = $cultures->call();
        }

        return $cultures;
    }

    /**
     * @see sfValidatorBase
     */
    public function getJavascriptValidationRules()
    {
        return array();
    }

    /**
     * @see sfValidatorBase
     */
    public function getJavascriptValidationMessages()
    {
        return array();
    }

}
