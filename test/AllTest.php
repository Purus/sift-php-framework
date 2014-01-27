<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/sfPHPUnitTestCase.php';
require_once(dirname(__FILE__).'/../lib/vendor/lime/lime.php');

/**
 * This is a wrapper to lime tests. All new tests should be created
 * as regular PHPUnit test.
 *
 */
class AllTest extends sfPHPUnitTestCase {

  public function testAll()
  {
    $h = new lime_harness(new lime_output_color());

    $h->base_dir = realpath(dirname(__FILE__));

    // cache autoload files
    require_once($h->base_dir.'/bootstrap/unit.php');

    testAutoloader::initialize(true);

    // unit tests
    $h->register_glob($h->base_dir.'/unit/*/*Test.php');

    // functional tests
    $h->register_glob($h->base_dir.'/functional/*Test.php');
    $h->register_glob($h->base_dir.'/functional/*/*Test.php');

    // other tests
    $h->register_glob($h->base_dir.'/other/*Test.php');

    $ret = $h->run();

    $this->assertEquals($ret, true, 'All tests passed');
  }

  public function tearDown()
  {
    parent::tearDown();
    testAutoloader::removeCache();
  }

}