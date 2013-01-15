<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

class sfSanitizer
{
  public static function sanitize($text, $type = 'strict')
  {
    require_once dirname(__FILE__) . '/../../../lib/vendor/htmlpurifier/HTMLPurifier.auto.php';
    require_once dirname(__FILE__) . '/../../../lib/vendor/htmlpurifier/HTMLPurifier.auto.php';

    // Create a new configuration object
    $config = HTMLPurifier_Config::createDefault();

    $settings =  sfYaml::load(dirname(__FILE__) . '/../../../data/config/sanitize.yml');
    
    // Load the settings
    $config->loadArray($settings[$type]);

    $purifier = new HTMLPurifier($config);

    return $purifier->purify($text);
  }

  public static function clean($text, $type = 'strict')
  {
    return self::sanitize($text, $type);
  }
  
}

$fixturesDir = dirname(__FILE__) . '/fixtures';
$html        = file_get_contents($fixturesDir . '/word_11.txt');
$result      = file_get_contents($fixturesDir . '/word_11_result.txt');

// file_put_contents($fixturesDir . '/word_11_result.txt', sfWordHtmlCleaner::clean($html));

$t->isa_ok(sfWordHtmlCleaner::clean($html), 'string', '->clean() returns string');
$t->is(sfWordHtmlCleaner::clean($html), $result, '->clean() cleans up ugly html correctly');

$html        = file_get_contents($fixturesDir . '/openoffice.txt');
$result      = file_get_contents($fixturesDir . '/openoffice_result.txt');

// file_put_contents($fixturesDir . '/openoffice_result.txt', sfWordHtmlCleaner::clean($html));

$t->is(sfWordHtmlCleaner::clean($html), $result, '->clean() cleans up ugly html correctly');
