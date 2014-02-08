<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__) . '/../vendor/lime/lime.php');

/**
 * sfTestBrowser simulates a browser which can test a Sift application.
 *
 * @package    Sift
 * @subpackage test
 */
class sfTestBrowser extends sfTestFunctional
{
    /**
     * Initializes the browser tester instance.
     *
     * @param string $hostname Hostname to browse
     * @param string $remote   Remote address to spook
     * @param array  $options  Options for sfBrowser
     */
    public function __construct($hostname = null, $remote = null, $options = array())
    {
        if (is_object($hostname)) {
            // new signature
            parent::__construct($hostname, $remote);
        } else {
            $browser = new sfBrowser($hostname, $remote, $options);

            if (null === self::$test) {
                $lime = new lime_test(null, isset($options['output']) ? $options['output'] : null);
            } else {
                $lime = null;
            }

            parent::__construct($browser, $lime);
        }
    }
}
