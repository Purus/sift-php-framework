<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorTools provides various utility methods for usage inside
 * custom validators
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorTools {

  public function __construct()
  {
    throw new sfException('This is not an validator. Just an utility library.');
  }

  /**
   * Validates czech company IN number
   *
   * @param string $in
   * @return boolean
   * @see http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
   */
  public static function validateCompanyIn($in)
  {
    // "be liberal in what you receive"
    $in = preg_replace('#\s+#', '', $in);

    // má požadovaný tvar?
    if(!preg_match('#^\d{8}$#', $in))
    {
      return false;
    }

    // kontrolní součet
    $a = 0;
    for($i = 0; $i < 7; $i++)
    {
      $a += $in[$i] * (8 - $i);
    }

    $a = $a % 11;

    if($a === 0)
      $c = 1;
    elseif($a === 10)
      $c = 1;
    elseif($a === 1)
      $c = 0;
    else
      $c = 11 - $a;
    return (int) $in[7] === $c;
  }

  /**
   * Validates birht number. Only czech numbers are supported.
   *
   * @param string $rc
   * @return boolean
   */
  public static function verifyBirthNumber($rc)
  {
    // "be liberal in what you receive"
    if(!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches))
    {
      return false;
    }

    list(, $year, $month, $day, $ext, $c) = $matches;

    // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
    if($c === '')
    {
      return $year < 54;
    }

    // kontrolní číslice
    $mod = ($year . $month . $day . $ext) % 11;
    if($mod === 10)
      $mod = 0;
    if($mod !== (int) $c)
    {
      return false;
    }

    // kontrola data
    $year += $year < 54 ? 2000 : 1900;

    // k měsíci může být připočteno 20, 50 nebo 70
    if($month > 70 && $year > 2003)
      $month -= 70;
    elseif($month > 50)
      $month -= 50;
    elseif($month > 20 && $year > 2003)
      $month -= 20;

    if(!checkdate($month, $day, $year))
    {
      return false;
    }

    // cislo je OK
    return true;
  }

}