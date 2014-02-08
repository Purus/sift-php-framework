<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatormMetaTitleMode validates title modes
 *
 * @package    Sift
 * @subpackage form
 */
class sfValidatormMetaTitleMode extends sfValidatorChoice
{
    /**
     *
     * @see sfValidatorChoice
     */
    public function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);

        $this->addOption('add_empty', true);
        $this->addOption('translate_choices', true);

        $this->setOption('choices', new sfCallable(array($this, 'getModes')));
        $this->setMessage('invalid', 'Selected title mode is invalid.');
    }

    /**
     * Returns the choices associated to the model.
     *
     * @return array An array of choices
     */
    public function getChoices()
    {
        return array(
            'append',
            'prepend',
            'replace',
        );
    }

}
