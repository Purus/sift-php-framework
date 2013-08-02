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
    $password_length = function_exists('mb_strlen') ? mb_strlen($password, self::getCharset()) : strlen($password);

    for($i = 2; $i <= 4; $i++)
    {
      $temp = str_split($password, $i);
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
      $strength -= 10;
    }

    preg_match_all('~[!@#$%^&*()_+{}:"<>?\|\[\];\',./`\~]~', $password, $symbols);

    if(!empty($symbols))
    {
      $symbols = count($symbols[0]);
      if($symbols >= 1)
      {
        $strength += 5;
      }
    }
    else
    {
      $symbols = 0;
    }

    preg_match_all('/[a-z]/', $password, $lowercase_characters);
    preg_match_all('/[A-Z]/', $password, $uppercase_characters);

    if(!empty($lowercase_characters))
    {
      $lowercase_characters = count($lowercase_characters[0]);
    }
    else
    {
      $lowercase_characters = 0;
    }

    if(!empty($uppercase_characters))
    {
      $uppercase_characters = count($uppercase_characters[0]);
    }
    else
    {
      $uppercase_characters = 0;
    }

    if(($lowercase_characters > 0) && ($uppercase_characters > 0))
    {
      $strength += 10;
    }

    $characters = $lowercase_characters + $uppercase_characters;

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
  protected static function hasOrderedCharacters($string, $number = 4)
  {
    $i = 0;
    $j = function_exists('mb_strlen') ? mb_strlen($string, self::getCharset()) : strlen($string);
    $chars = array();
    foreach(str_split($string, 1) as $m)
    {
      $chars[] = chr((ord($m[0]) + $j--) % 256) . chr((ord($m[0]) + $i++) % 256);
    }
    $str = implode('', $chars);
    return preg_match('#(.)(.\1){' . ($number - 1) . '}#', $str);
  }

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidation()
  {
    $rules = array();

    $minLength = $this->hasOption('min_length') ?
            $this->getOption('min_length') : 0;

    $maxLength = $this->hasOption('max_length') ?
            $this->getOption('max_length') : 0;

    if($this->hasOption('required') && $this->getOption('required'))
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

  /**
   * @see sfValidatorBase
   */
  public function getJavascriptValidationMessage()
  {
    $messages = array();

    $minLength = $this->hasOption('min_length') ?
            $this->getOption('min_length') : 0;

    $maxLength = $this->hasOption('max_length') ?
            $this->getOption('max_length') : 0;

    if($this->hasOption('required') && $this->getOption('required'))
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
