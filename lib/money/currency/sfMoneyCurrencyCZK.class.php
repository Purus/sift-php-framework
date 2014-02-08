<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyCurrencyCZK represents czech "koruna" currency
 *
 * @package    Sift
 * @subpackage money
 */
class sfMoneyCurrencyCZK extends sfMoneyCurrency
{
    /**
     * Currency name
     *
     * @var string
     */
    protected $name = 'CZK';

    /**
     * Currency scale for CZK
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
