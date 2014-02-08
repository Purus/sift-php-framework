<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nNumber validates an number (integer or float). It handles culture specific number formatting.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorI18nNumber extends sfValidatorNumber
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * culture: culture of the number
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   * @see sfValidatorNumber
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('culture', null);
    // strict mode, which allows only culture specifics
    $this->addOption('strict_mode', true);
    $this->addMessage('format', 'Number has a wrong format: %value%');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if(!sfToolkit::isBlank($value))
    {
      try
      {
        $value = sfI18nNumberFormat::getNumber($value, $this->getCulture());
      }
      catch(Exception $e)
      {
        // we have a strict mode, it means, that the value is not formatted
        // for current culture, throw an error!
        // or
        // this value does not look like float, its all wrong
        if($this->getOption('strict_mode') || !$this->isFloat($value))
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }
      }
    }

    return parent::doClean($value);
  }

  public function getJavascriptValidationRules()
  {
    return array();
  }

  public function getJavascriptValidationMessages()
  {
    return array();
  }
}
