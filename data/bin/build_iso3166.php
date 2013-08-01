<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds ISO 3166
 *
 * @package    Sift
 * @subpackage script
 */

print "Building ISO data...\n";

$targetDir = realpath(dirname(__FILE__) . '/../data');
$isoDatabase = realpath(dirname(__FILE__) . '/../../build/country_names_and_code_elements.txt');
$libDir = realpath(dirname(__FILE__) . '/../../lib');
$isoClass = $libDir . '/i18n/iso/sfISO3166.class.php';

require_once $libDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$iso = explode("\n", file_get_contents($isoDatabase));

$countries = array();
foreach($iso as $line)
{
  $line = trim($line);

  if(empty($line))
  {
    continue;
  }

  list($name, $code) = explode(';', $line, 2);

  $name = str_replace('\\\'', '\'', trim($name, '\''));

  if(empty($name) || strlen($code) !== 2)
  {
    continue;
  }

  $countries[$code] = sfUtf8::ucwords(sfUtf8::lower($name));
}

ksort($countries);

$php = '';
foreach($countries as $code => $name)
{
  $php .= sprintf("    '%s', // %s\n", $code, $name);
}

$content = preg_replace('/protected static \$countries = array *\(.*?\);/s',
    sprintf("protected static \$countries = array(\n%s  );", $php), file_get_contents($isoClass));

file_put_contents($isoClass, $content);

printf("Found %s country codes.\n", count($countries));
print "Done.\n";
