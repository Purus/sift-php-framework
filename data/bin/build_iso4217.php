<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds ISO 4217
 *
 * @package    Sift
 * @subpackage script
 */

print "Building ISO data...\n";

$targetDir = realpath(dirname(__FILE__) . '/../data');
$isoDatabase = realpath(dirname(__FILE__) . '/../../build/iso_4217.xml');
$libDir = realpath(dirname(__FILE__) . '/../../lib');
$isoClass = $libDir . '/i18n/iso/sfISO4217.class.php';

require_once $libDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$xml = simplexml_load_file($isoDatabase);

$currencies = array();
foreach($xml->xpath('/ISO_4217/CcyTbl/CcyNtry') as $line)
{
  $code = (string)current($line->xpath('Ccy'));
  $name = (string)current($line->xpath('CcyNm'));

  if(strlen($code) !== 3)
  {
    continue;
  }

  $currencies[$code] = sfUtf8::ucwords(sfUtf8::lower($name));
}

ksort($currencies);

$php = '';
foreach($currencies as $code => $name)
{
  $php .= sprintf("    '%s', // %s\n", $code, $name);
}

$content = preg_replace('/protected static \$currencies = array *\(.*?\);/s',
    sprintf("protected static \$currencies = array(\n%s  );", $php), file_get_contents($isoClass));

file_put_contents($isoClass, $content);

printf("Found %s currency codes.\n", count($currencies));
print "Done.\n";
