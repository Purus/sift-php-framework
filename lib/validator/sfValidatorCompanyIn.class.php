<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCompanyIn validates a value for company identifier number.
 * Only czech companies are currently supported.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Mishal <mishal at mishal dot cz>
 */
class sfValidatorCompanyIn extends sfValidatorBase
{
  
  public function __construct($options = array(), $messages = array())
  {
    // check against public database 
    $this->addOption('public_database_check', isset($options['public_database_check']) ? 
            $options['public_database_check'] : 'ares');
    
    parent::__construct($options, $messages);
  }
  
  /**
   * @see sfValidatorString
   */
  public function doClean($value)
  {
    // be liberal in what you recieve
    $value = preg_replace('#\s+#', '', $value);
    
    if(!sfValidatorTools::validateCompanyIn($value))
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    
    if($database = $this->getOption('public_database_check'))
    {      
      // FIXME: pass as option
      $options = array();
      $this->validateUsingPublicDatabaseApi($value, $database, $options);
    }
    
    return $value;
  }
  
  
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();
    
    // $rules[sfFormJavascriptValidation::NUMBER] = true;
    $rules[sfFormJavascriptValidation::CUSTOM_CALLBACK] = array('callback' => (
"function(value, element, params) {   
  var x = value;
  try
  {
    var a = 0;
    if(x.length == 0) return true;
    if(x.length != 8) throw 1;
    var b = x.split('');
    var c = 0;
    for(var i = 0; i < 7; i++) a += (parseInt(b[i]) * (8 - i));
    a = a % 11;
    c = 11 - a;
    if(a == 1) c = 0;
    if(a == 0) c = 1;
    if(a == 10) c = 1;
    if(parseInt(b[7]) != c) throw 1;
  }
  catch(e)
  {    
    return false;
  }
  return true;
}"));
    
    return $rules;
  }
  
  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();    
    $messages[sfFormJavascriptValidation::CUSTOM_CALLBACK] = 
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');
    return $messages;
  }
  
  /**
   * Validates against ARES
   */
  protected function validateUsingPublicDatabaseApi($value, $driver = 'ares', 
          $driverOptions = array())
  {
    $driver = sprintf('sfValidatorCompanyInDriver%s', ucfirst($driver));
    if(!class_exists($driver))
    {
      throw new InvalidArgumentException(sprintf('Invalid driver "%s"', $driver));
    }
    
    $checker = new $driver($driverOptions);
    
    $valid = $checker->validate($value);
    
    if(!$valid)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    
    return $value;
  }
   
}
