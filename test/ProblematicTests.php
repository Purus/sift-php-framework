<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// ----------------------------
// PHP 5.2
// ----------------------------
/*
Failed Test Stat Total Fail Errors List of Failed
--------------------------------------------------------------------------
unit/cache/sfFileCacheTest 255 16 0 0
unit/date/sfDateTimeTest 255 7 0 0
unit/helper/DateHelperTest 255 62 0 0
unit/i18n/sfCollatorTest 255 7 5 0 2 6 7 8 9
t/i18n/sfI18nDateFormatterTest 255 5 0 0
unit/i18n/sfI18nTest 255 1 1 0 0
unit/image/sfExifTest 255 7 0 1
unit/image/sfImageTest 0 57 8 0 29 30 31 32 37 38 39 40
unit/minifier/sfMinifierTest 255 3 0 1
unit/request/sfWebRequestTest 0 67 1 0 60
unit/security/sfSanitizerTest 0 6 2 0 5 6
t/text/sfTextMacroRegistryTest 255 13 0 0
unit/util/sfMimeTypeTest 0 48 2 0 24 38
unit/yaml/sfYamlInlineTest 0 124 1 0 13
*/

require_once(dirname(__FILE__).'/../lib/vendor/lime/lime.php');

/**
 * Problematic tests for PHP 5.2
 *
 */
class ProblematicsTests extends PHPUnit_Framework_TestCase {

  public function testAll()
  {
    require_once(dirname(__FILE__).'/bootstrap/unit.php');
    testAutoloader::initialize(true);

    include dirname(__FILE__) . '/../data/bin/check.php';

    $problematic = array(
      'unit/cache/sfFileCacheTest.php'
    );

    $dir = dirname(__FILE__);
    foreach($problematic as $script)
    {
      echo "Launching test " . $script . PHP_EOL;
      include $dir . '/' . $script;
    }
  }

}