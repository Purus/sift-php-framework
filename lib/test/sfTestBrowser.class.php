<?php

require_once(dirname(__FILE__).'/../vendor/lime/lime.php');

/**
 * sfTestBrowser simulates a browser which can test a symfony application.
 *
 * sfTestFunctional is backward compatible class for symfony 1.0, and 1.1.
 * For new code, you can use the sfTestFunctional class directly.
 *
 * @package    symfony
 * @subpackage test
 */
class sfTestBrowser extends sfTestFunctional
{
  /**
   * Initializes the browser tester instance.
   *
   * @param string $hostname  Hostname to browse
   * @param string $remote    Remote address to spook
   * @param array  $options   Options for sfBrowser
   */
  public function __construct($hostname = null, $remote = null, $options = array())
  {
    if (is_object($hostname))
    {
      // new signature
      parent::__construct($hostname, $remote);
    }
    else
    {
      $browser = new sfBrowser($hostname, $remote, $options);

      if (null === self::$test)
      {
        $lime = new lime_test(null, isset($options['output']) ? $options['output'] : null);
      }
      else
      {
        $lime = null;
      }

      parent::__construct($browser, $lime);
    }
  }
}
