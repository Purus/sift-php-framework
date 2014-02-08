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
 */
class sfValidatorPassword extends sfValidatorString {

  /**
   * @see sfValidatorBase
   */
  public function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setMessage('required', 'Password is required.');
    $this->setMessage('invalid', 'Password is invalid.');

    $this->addMessage('max_length', 'Password is too long (%max_length% characters max).');
    $this->addMessage('min_length', 'Password is too short (%min_length% characters min).');

    $this->addMessage('strength_error', 'Password is too weak.');

    $this->setOption('min_length', 8);

    // check password strength
    $this->addOption('strength_check', false);
    // minimal password strength (a number between 0 and 100)
    $this->addOption('min_strength', 10);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $value = parent::doClean($value);

    if($this->getOption('strength_check'))
    {
      $strength = self::getPasswordStrength($value);
      if($strength < $this->getOption('min_strength'))
      {
        throw new sfValidatorError($this, 'strength_error');
      }
    }

    return $value;
  }

  /**
   * Calculate password strength
   *
   * @param string $password The password to check
   * @return integer
   */
  public static function getPasswordStrength($password)
  {
    $strength = 0;
    $password_length = self::getStringLength($password);

    if($password_length > 9)
    {
      $strength += 10;
    }

    for($i = 2; $i <= 4; $i++)
    {
      $temp = self::splitString($password, $i);
      $strength -= (ceil($password_length / $i) - count(array_unique($temp)));
    }

    preg_match_all('/[0-9]/', $password, $numbers);
    if(!empty($numbers))
    {
      $numbers = count($numbers[0]);
      if($numbers >= 2)
      {
        $strength += 5;
      }
    }
    else
    {
      $numbers = 0;
    }

    if(self::hasOrderedCharacters($password))
    {
      $strength -= 20;
    }

    // symbols
    preg_match_all('~[!@#$%^&*()_+{}:"<>?\|\[\];\',./`\~]~', $password, $symbols);
    if(!empty($symbols))
    {
      $symbols = count($symbols[0]);
      if($symbols > 0)
      {
        // each symbol is + 7 points
        $strength += $symbols * 7;
      }
    }
    else
    {
      $symbols = 0;
    }

    preg_match_all('/[a-z]/u', $password, $lowercaseCharacters);
    preg_match_all('/[A-Z]/u', $password, $uppercaseCharacters);
    // Extended Latin A, parts OF Latin-1 Supplement
    preg_match_all('/([\x{0100}-\x{017F}\x{00C0}-\x{00CF}\x{00E0}-\x{00FF}])/u', $password, $utf8Characters);

    if(!empty($lowercaseCharacters))
    {
      $lowercaseCharacters = count($lowercaseCharacters[0]);
    }
    else
    {
      $lowercaseCharacters = 0;
    }

    if(!empty($uppercaseCharacters))
    {
      $uppercaseCharacters = count($uppercaseCharacters[0]);
    }
    else
    {
      $uppercaseCharacters = 0;
    }

    if(!empty($utf8Characters))
    {
      $utf8Characters = count($utf8Characters[0]);
    }
    else
    {
      $utf8Characters = 0;
    }

    if(($lowercaseCharacters > 0) && ($uppercaseCharacters > 0))
    {
      $strength += 10;
    }

    if($utf8Characters > 0)
    {
      $strength += 10 * $utf8Characters;
    }

    $characters = $lowercaseCharacters + $uppercaseCharacters + $utf8Characters;

    if(($numbers > 0) && ($symbols > 0))
    {
      $strength += 15;
    }

    if(($numbers > 0) && ($characters > 0))
    {
      $strength += 15;
    }

    if(($symbols > 0) && ($characters > 0))
    {
      $strength += 15;
    }

    if(($numbers == 0) && ($symbols == 0))
    {
      $strength -= 10;
    }

    if(($symbols == 0) && ($characters == 0))
    {
      $strength -= 10;
    }

    if($strength < 0)
    {
      $strength = 0;
    }
    elseif($strength > 100)
    {
      $strength = 100;
    }

    return $strength;
  }

  /**
   * Check a string for alphabetically ordered characters
   *
   * @param string $string
   * @param integer $number
   * @return boolean
   * @see http://stackoverflow.com/questions/12124803/check-a-string-for-alphabetically-ordered-characters
   */
  public static function hasOrderedCharacters($string, $number = 3)
  {
    $len = self::getStringLength($string);
    $count = 0;
    $last = 0;
    for($i = 0; $i < $len; $i++)
    {
      $current = ord($string[$i]);
      if($current == $last + 1)
      {
        $count++;
        if($count >= $number)
        {
          return true;
        }
      }
      else
      {
        $count = 1;
      }
      $last = $current;
    }

    return false;
  }

  /**
   * Return string length
   *
   * @param string $string
   * @return integer
   */
  protected static function getStringLength($string)
  {
    return function_exists('mb_strlen') ? mb_strlen($string, self::getCharset()) : strlen($string);
  }

  /**
   * Split string (UTF-8 safe)
   *
   * @param string $string
   * @param integer $length
   * @return array
   */
  protected static function splitString($string, $length = 0)
  {
    if($length > 0)
    {
      $ret = array();
      $len = self::getStringLength($string);
      for($i = 0; $i < $len; $i += $length)
      {
        $ret[] = function_exists('mb_substr') ? mb_substr($string, $i, $length, self::getCharset()) :
          substr($string, $i, $length);
      }

      return $ret;
    }

    return preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationRules()
  {
    $rules = array();

    $minLength = $this->hasOption('min_length') ?
            $this->getOption('min_length') : 0;

    $maxLength = $this->hasOption('max_length') ?
            $this->getOption('max_length') : 0;

    if($this->getOption('required'))
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

    // strength check is enabled
    if($this->getOption('strength_check'))
    {
      $rules[sfFormJavascriptValidation::PASSWORD_STRENGTH] = array(
        'minStrength' => $this->getOption('min_strength')
      );
    }

    return $rules;
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessages()
  {
    $messages = array();

    $minLength = $this->hasOption('min_length') ?
            $this->getOption('min_length') : 0;

    $maxLength = $this->hasOption('max_length') ?
            $this->getOption('max_length') : 0;

    if($this->getOption('required'))
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

    // strength check is enabled
    if($this->getOption('strength_check'))
    {
      $messages[sfFormJavascriptValidation::PASSWORD_STRENGTH] =
          sfFormJavascriptValidation::fixValidationMessage($this, 'strength_error');
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
    if($this->getOption('strength_check'))
    {
      $messages[] = $this->getMessage('strength_error');
    }

    return $messages;
  }

}
