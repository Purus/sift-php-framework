<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrencyEUR represents EUR currency
 *
 * @package Sift
 * @subpackage money
 */
class sfMoneyCurrencyEUR extends sfMoneyCurrency {

  /**
   * Currency name
   *
   * @var string
   */
  protected $name = 'EUR';

  /**
   * Currency scale
   *
   * @var integer
   */
  public static $scale = 2;

  /**
   * Empty constructor
   */
  public function __construct()
  {
  }
  
}
