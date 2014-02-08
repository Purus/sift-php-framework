<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrency
 *
 * @package Sift
 * @subpackage money
 * @link http://verraes.net/2011/04/fowler-money-pattern-in-php/
 */
class sfMoneyCurrency {

  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Name
   *
   * @var string
   */
  protected $name;

  /**
   * Scale
   *
   * @var integer
   */
  public static $scale = 2;

  /**
   * Constructs the currency
   *
   * @param string $name
   * @throws InvalidArgumentException
   */
  public function __construct($name)
  {
    $name = strtoupper($name);

    if(!self::isValid($name))
    {
      throw new InvalidArgumentException(sprintf('Invalid currency "%s" given.', $name));
    }

    $this->name = $name;
  }

  /**
   * Creates new currency
   *
   * @param string $name Name of the currency
   * @param integer $scale Scale
   * @return sfMoneyCurrency
   */
  public static function create($name)
  {
    if(!empty($name) && class_exists($class = sprintf('sfMoneyCurrency%s', $name)))
    {
      return new $class();
    }

    return new self($name);
  }

  /**
   * Check if the given $currency is valid.
   *
   * @param string $currency The currency ISO code
   * @return boolean True if is valid, false otherwise
   */
  public static function isValid($currency)
  {
    return sfISO4217::isValidCode($currency);
  }

  /**
   * Returns an instance of the currency
   *
   * @param string $name
   * @param integer $scale
   * @return sfMoneyCurrency
   */
  public static function getInstance($name, $scale = null)
  {
    $key = $name . $scale;
    if(!isset(self::$instances[$key]))
    {
      self::$instances[$key] = self::create($name, $scale);
    }

    return self::$instances[$key];
  }

  /**
   * Returns currency name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns scale
   *
   * @return string
   */
  public function getScale()
  {
    return self::$scale;
  }

  /**
   * To string
   *
   * @return string
   */
  public function toString()
  {
    return $this->name;
  }

  /**
   * To string magic method
   *
   * @return string
   */
  public function __toString()
  {
    return $this->name;
  }

  /**
   * Is the $other currency the same?
   *
   * @param sfCurrency $other
   * @return boolean
   */
  public function equals(sfMoneyCurrency $other)
  {
    return $this->getName() === $other->getName();
  }

}
