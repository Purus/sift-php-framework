<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nEnabledLanguages validates than the value is enabled language.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nChoiceEnabledLanguages extends sfValidatorChoice
{
    /**
     *
     * @see sfValidatorI18nChoiceLanguage
     */
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $cultures = sfConfig::get('sf_i18n_enabled_cultures', array());
        $this->setOption('choices', $cultures);
    }

}
