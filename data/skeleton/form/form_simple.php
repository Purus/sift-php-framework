<?php

/**
 * ##FORM_CLASS_NAME##
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 */
class ##FORM_CLASS_NAME## extends ##FORM_BASE_CLASS_NAME##
{
    /**
     * Translation catalogue
     *
     * @var string
     */
    protected $translationCatalogue = '%SF_DATA_DIR%/i18n/##FORM_UNDERSCORED_NAME##';

    public function configure()
    {
        // add widgets here
        $this->setWidget('foo', new sfWidgetFormInput());
        $this->setValidator(
            'foo',
            new sfValidatorString(array(// array of options
            ), array(// array of messages
            ))
        );
    }

    /**
     * Final javascript validation
     *
     * @param sfFormJavascriptValidationRulesCollection    $rules    Validation rules
     * @param sfFormJavascriptValidationMessagesCollection $messages Validation messages
     */
    public function getJavascriptFinalValidation(
        sfFormJavascriptValidationRulesCollection &$rules,
        sfFormJavascriptValidationMessagesCollection &$messages
    ) {
    }

}
