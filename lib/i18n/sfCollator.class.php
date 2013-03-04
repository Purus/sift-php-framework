<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides string comparison capability with support for appropriate locale-sensitive sort orderings.
 *
 * @package    Sift
 * @subpackage text
 */
class sfCollator {

  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Collator instance if running on php > 5.3
   *
   * @var Collator
   */
  protected $collator;

  /**
   * Current culture
   *
   * @var string
   */
  protected $culture;

  /**
   * Returns an instance of the collator for given culture
   *
   * @param string $culture The locale whose collation rules should be used.
   * @return sfCollator
   */
  public static function getInstance($culture)
  {
    if(!isset(self::$instances[$culture]))
    {
      self::$instances[$culture] = new sfCollator($culture);
    }
    return self::$instances[$culture];
  }

  /**
   * Constructs the object
   *
   * @param string $culture The locale whose collation rules should be used.
   * @throws RuntimeException If collator instance cannot be created
   */
  public function __construct($culture)
  {
    if(class_exists('Collator', false))
    {
      $this->collator = new Collator($culture);

      // see http://stackoverflow.com/questions/13571273/sorting-array-with-collation
      if(intl_is_failure($this->collator->getErrorCode()) || strpos($culture, $this->collator->getLocale(Locale::VALID_LOCALE)) === false)
      {
        throw new RuntimeException(sprintf('Error creating collator for culture "%s". %s', $culture, intl_get_error_message()));
      }

      $this->culture = $this->collator->getLocale(Locale::VALID_LOCALE);
    }
    else
    {
      $this->culture = $culture;
    }
  }

  /**
   * Actual culture
   *
   * @return string
   */
  public function getCulture()
  {
    return $this->culture;
  }

  /**
   * Compares the string
   *
   * @param string $string1
   * @param string $string2
   */
  public function compare($string1, $string2)
  {
    if($this->collator)
    {
      return $this->collator->compare($string1, $string2);
    }

    // we will try to do it without collator
    $old = setlocale(LC_COLLATE, 0);

    // running on windows
    // since utf8 is not available there
    if(strpos(PHP_OS, 'WIN') !== false)
    {
      // try to set windows-1250 locale
      setlocale(LC_COLLATE, sprintf('%s.1250', $this->getWindowsLocale($this->getCulture())), 
              $this->getCulture(), sprintf('%s.1250', $this->getCulture()), 
              sprintf('%s.1250', $this->getCulture()));

      // convert to windows 1250 and compare
      $result = strcoll(iconv('UTF-8', 'WINDOWS-1250', $string1), iconv('UTF-8', 'WINDOWS-1250', $string2));
    }
    else
    {
      // set new locale
      setlocale(LC_COLLATE, sprintf('%s.utf8', $this->getCulture()), sprintf('%s.UTF-8', $this->getCulture()));
      $result = strcoll($string1, $string2);
    }

    setlocale(LC_COLLATE, $old);
    return $result;
  }

  /**
   * Sort array maintaining index association. This is an equivalent to standard
   * PHP asort() function.
   *
   * @param array $array
   * @param integer $flag SORT_REGULAR, SORT_NUMERIC or SORT_STRING
   */
  public function asort(&$array, $flag = SORT_REGULAR)
  {
    if($this->collator)
    {
      return $this->collator->asort($array, $flag);
    }

    uasort($array, array($this, 'compare'));
  }

  /**
   * Sorts an array. This is an equivalent to standard PHP sort().
   *
   * @param array $array
   * @param integer $flag
   * @return boolean
   */
  public function sort(&$array, $flag = SORT_REGULAR)
  {
    if($this->collator)
    {
      return $this->collator->sort($array, $flag);
    }

    return usort($array, array($this, 'compare'));
  }

  /**
   * Sort array using specified sort keys
   *
   * @param array $array
   * @return boolean True on success, false on failure
   */
  public function sortWithSortKeys(&$array)
  {
    if($this->collator)
    {
      return $this->collator->sortWithSortKeys($array);
    }

    return $this->sort($array);
  }

  /**
   * Magic method passes all to Collator object
   *
   * @param string $method
   * @param array $aguments
   * @return boolean
   */
  public function __call($method, $arguments)
  {
    if($this->collator && method_exists($this->collator, $method))
    {
      return call_user_func_array(array($this->collator, $method), $arguments);
    }

    return false;
  }

  /**
   * Returns windows locale for given culture
   *
   * @param string $culture
   */
  protected function getWindowsLocale($culture)
  {
    $culture = str_replace('-', '_', $culture);
    $parts = explode('_', $culture);
    $name = ucfirst(sfISO639::code2ToName($parts[0]));

    if(!isset($parts[1]))
    {
      return $name;
    }

    // we have something like cs_CZ
    // english name
    $countries = sfCulture::getInstance('en')->getCountries();
    $country = strtoupper($parts[1]);
    if(isset($countries[$country]))
    {
      $country = $countries[$country];
    }

    return sprintf('%s_%s', $name, $country);
  }

}
