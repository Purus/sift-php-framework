<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates passwords.
 *
 * @package Sift
 * @subpackage util
 */
class sfPasswordTools {

  /**
   * Pronounceable password
   */
  const PASSWORD_PRONOUNCEABLE = 'pronounceable';

  /**
   * Unpronounceable password
   */
  const PASSWORD_UNPRONOUNCEABLE = 'unpronounceable';

  /**
   * List of vowels and vowel sounds
   *
   * @var array
   */
  protected static $vowels = array(
    'a', 'e', 'i', 'o', 'u', 'ae',
    'ou', 'io', 'ea', 'ou', 'ia', 'ai');

  /**
   * List of consonants and consonant sounds
   *
   * @var array
   */
  protected static $consonants = array(
    'b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm',
    'n', 'p', 'r', 's', 't', 'u', 'v', 'w',
    'tr', 'cr', 'fr', 'dr', 'wr', 'pr', 'th',
    'ch', 'ph', 'st', 'sl', 'cl');

  /**
   * Generates random password
   *
   * @param integer $length Password length
   * @param integer or string $type Type of password (pronounceable or unpronounceable)
   * @return string
   */
  public static function generatePassword($length = 8, $type = self::PASSWORD_PRONOUNCEABLE)
  {
    $password = '';

    switch($type)
    {
      case self::PASSWORD_PRONOUNCEABLE:

        $v_count = count(self::$vowels);
        $c_count = count(self::$consonants);

        for($i = 0; $i < $length; $i++)
        {
          $password .= self::$consonants[mt_rand(0, $c_count - 1)] . self::$vowels[mt_rand(0, $v_count - 1)];
        }

        $password = substr($password, 0, $length);

      break;

      case self::PASSWORD_UNPRONOUNCEABLE:
      default:

        $pass_rnd = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        shuffle($pass_rnd);
        for($i = 0; $i < $length; $i++)
        {
          $password .= $pass_rnd[array_rand($pass_rnd)];
        }

      break;
    }

    return $password;
  }

}
