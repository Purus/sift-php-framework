<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$value = 'This is a simple text with <script>alert(document.cookie);</script>';

class myHtmlPurifier extends sfHtmlPurifier {

  protected function loadSettings()
  {
    return array(
        'strict' => array(
          'Cache.SerializerPath' => sys_get_temp_dir(),
          'HTML.AllowedElements' => array()
        ),
        'word' => array(
          'Cache.SerializerPath' => sys_get_temp_dir(),
          'HTML.Trusted' => false,
          'AutoFormat.RemoveEmpty.RemoveNbsp' =>  true,
          'AutoFormat.RemoveSpansWithoutAttributes' =>  true,
          'HTML.TidyLevel' => 'heavy',
          'HTML.ForbiddenElements' =>  array('div', 'col'),
          'HTML.ForbiddenAttributes' => array('style', 'class'),
          'Output.TidyFormat' => true,
          'AutoFormat.RemoveEmpty' => true,
          'AutoFormat.AutoParagraph' => true,
          'Core.NormalizeNewlines' => false
        )
    );
  }
}

class mySanitizer extends sfSanitizer {

  public static function getHtmlPurifier($type = 'strict')
  {
    return new myHtmlPurifier($type);
  }

  // we support 5.2, so cannot use static:: inside the purifier
  public static function xssClean($value, $type = 'strict')
  {
    return self::getHtmlPurifier($type)->purify($value);
  }

  public static function sanitize($value, $type = 'strict')
  {
    return self::xssClean($value, $type);
  }

}

$sanitized = mySanitizer::sanitize($value);

$t->is($sanitized, 'This is a simple text with ', 'sanitize() sanitize string');

$t->is(array($sanitized), array('This is a simple text with '), 'sanitize() can sanitize an array');

$t->diag('->getHtmlPurifier()');

$t->isa_ok(mySanitizer::getHtmlPurifier('strict'), 'myHtmlPurifier', 'getHtmlPurifier() return sfHtmlPurifier object');

$fixturesDir = dirname(__FILE__) . '/fixtures';
$html = file_get_contents($fixturesDir . '/word_11.txt');
$result = file_get_contents($fixturesDir . '/word_11_result.txt');

$t->diag('sfWordHtmlCleaner tests');

class myWordHtmlCleaner extends sfWordHtmlCleaner {

  /**
   * Cleans up word html, also convert to utf8 (second argument)
   *
   * @param string $html
   * @param boolean $convertToUtf8
   * @return string
   */
  public static function clean($html, $convertToUtf8 = true)
  {
    if($convertToUtf8)
    {
      $html = self::convertToUtf8($html);
    }
    // return self::fixNewLines(
    return mySanitizer::sanitize($html, 'word');
    //);
  }
}

$t->isa_ok(myWordHtmlCleaner::clean($html), 'string', '->clean() returns string');
$t->is(myWordHtmlCleaner::clean($html), $result, '->clean() cleans up ugly html correctly');

$html = file_get_contents($fixturesDir . '/openoffice.txt');
$result = file_get_contents($fixturesDir . '/openoffice_result.txt');

$t->is(myWordHtmlCleaner::clean($html), $result, '->clean() cleans up ugly html correctly');
