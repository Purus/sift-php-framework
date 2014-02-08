<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCompanyVat validates a value form company VAT numbers. Only czech
 * and slovak companies are supported.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorCompanyVat extends sfValidatorBase {

  /**
   * @see sfValidatorBase
   */
  public function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->setMessage('invalid', '"%value%" is invalid company VAT number.');
  }

  /**
   * @see sfValidatorBase
   */
  public function doClean($value)
  {
    // be liberal in what you recieve
    $value = preg_replace('#\s+#', '', $value);

    $prefix = substr($value, 0, 2);

    if(in_array(strtoupper($prefix), array('CZ', 'SK')))
    {
      $v = substr($value, 2);
      $length = strlen($v);
      if($length < 8 || $length > 11)
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
      else if($length == 8)
      {
        if(!sfValidatorTools::validateCompanyIn($v))
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }
      }
      else if(!sfValidatorTools::verifyBirthNumber($v))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }
    else // unsupported country
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $value;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = parent::getJavascriptValidationRules();

    $rules[sfFormJavascriptValidation::CUSTOM_CALLBACK] = array('callback' => (
"function(value, element, params) {

  public function testICO(x)
  {
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
      if(parseInt(b[ 7]) != c) throw(1);
    }
    catch(e)
    {
      return false;
    }

    return true;
  };

  public function testRC(x, age)
  {
    if(!age) age = 0;
    try
    {
      if(x.length == 0) return true;
      if(x.length < 9) throw 1;
      var year = parseInt(x.substr(0, 2), 10);
      var month = parseInt(x.substr(2, 2), 10);
      var day = parseInt( x.substr(4, 2), 10);
      var ext = parseInt(x.substr(6, 3), 10);
      if((x.length == 9) && (year < 54)) return true;
      var c = 0;
      if(x.length == 10) c = parseInt(x.substr(9, 1));
      var m = parseInt( x.substr(0, 9)) % 11;
      if(m == 10) m = 0;
      if(m != c) throw 1;
      year += (year < 54) ? 2000 : 1900;
      if((month > 70) && (year > 2003)) month -= 70;
      else if (month > 50) month -= 50;
      else if ((month > 20) && (year > 2003)) month -= 20;
      var d = new Date();
      if((year + age) > d.getFullYear()) throw 1;
      if(month == 0) throw 1;
      if(month > 12) throw 1;
      if(day == 0) throw 1;
      if(day > 31) throw 1;
    }
    catch(e)
    {
      return false;
    }

    return true;
  };

  var x = value;
  try
  {
    if(x.length == 0) return true;
    var id = x.substr(0, 2).toUpperCase();
    x = x.substr(2);
    if((id == 'CZ') || (id == 'SK'))
    {
      if(x.length < 8) throw 1;
      if(x.length > 11) throw 1;
      if(x.length == 8)
      {
        return testICO(x);
      }
      else
      {
        return testRC(x, 18);
      }
    }
    throw 1;
  }
  catch(e)
  {
    return false;
  }
}"));

    return $rules;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    $messages = parent::getJavascriptValidationMessages();
    $messages[sfFormJavascriptValidation::CUSTOM_CALLBACK] =
            sfFormJavascriptValidation::fixValidationMessage($this, 'invalid');

    return $messages;
  }

}
