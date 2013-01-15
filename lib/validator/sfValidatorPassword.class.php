<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPassword validates the passwords.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorPassword extends sfValidatorString
{  
  public function getJavascriptValidation()
  {
    $rules = array();
    
    $minLength = $this->hasOption('min_length') ? 
                 $this->getOption('min_length') : 0;
    
    $maxLength = $this->hasOption('max_length') ? 
                 $this->getOption('max_length') : 0;   
    
    if($this->hasOption('required') 
            && $this->getOption('required'))
    {
      $rules[sfFormJavascriptValidation::REQUIRED] = true;
    }

    // lets build the callback
    if($minLength > 0)
    {
      $rules[sfFormJavascriptValidation::MIN_LENGTH] = $minLength;
    }

    if($maxLength > 0)
    {
      $rules[sfFormJavascriptValidation::MAX_LENGTH] = $maxLength;
    }
    
    return $rules;
  }
  
  public function getJavascriptValidationMessage()
  {
    $messages = array();
    
    $minLength = $this->hasOption('min_length') ? 
                 $this->getOption('min_length') : 0;
    
    $maxLength = $this->hasOption('max_length') ? 
                 $this->getOption('max_length') : 0;   
    
    if($this->hasOption('required') 
            && $this->getOption('required'))
    {
      $messages[sfFormJavascriptValidation::REQUIRED] = 
        sfFormJavascriptValidation::fixValidationMessage($this, 'required');
    }  
    
    if($minLength > 0)
    {
      $messages[sfFormJavascriptValidation::MIN_LENGTH] = 
              sfFormJavascriptValidation::fixValidationMessage($this, 'min_length');
    }

    if($maxLength > 0)
    {
      $messages[sfFormJavascriptValidation::MAX_LENGTH] = 
        sfFormJavascriptValidation::fixValidationMessage($this, 'max_length');
    }
    
    return $messages;
  }
  
  /**
   * @see sfValidatorBase
   */
  public function getActiveMessages()
  {
    $messages = array();
    if($this->getOption('required'))
    {
      $messages[] = $this->getMessage('required');
    }
    if($this->getOption('min_length') > 0)
    {
      $messages[] = $this->getMessage('min_length');
    }
    if($this->getOption('max_length') > 0)
    {
      $messages[] = $this->getMessage('max_length');
    }
    return $messages;
  }    
  
}
