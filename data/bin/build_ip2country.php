<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds ip2country sqlite database
 *
 * @package    Sift
 * @subpackage script
 */

print "Building IP to country IP database. Please wait. This takes a long time...\n";

$csvDatabase = realpath(dirname(__FILE__) . '/../../build/IpToCountry.csv');
$database = dirname(__FILE__) . '/../../data/data/ip2country.db';
$libDir = realpath(dirname(__FILE__) . '/../../lib');

require_once $libDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

if(is_readable($database))
{
  unlink($database);
}

$db = new sfPDO(sprintf('sqlite:%s', $database));

$statements = array(
  'CREATE TABLE [ip2country] (
    [ip_from]  INTEGER UNSIGNED,
    [ip_to]    INTEGER UNSIGNED,
    [code]  CHAR(3)
  )',
  'CREATE INDEX [from_idx] ON [ip2country] ([ip_from])',
  'CREATE INDEX [to_idx] ON [ip2country] ([ip_to])'
);

foreach($statements as $statement)
{
  if(!$db->query($statement))
  {
    throw new Exception($db->lastError());
  }
}

$i = $invalid = 0;
$f = fopen($csvDatabase, 'r');

while(!feof($f))
{
  $s = fgets($f);
  if(substr($s, 0, 1) == '#')
  {
    continue;
  }

  $temp = explode(',', $s);
  if(count($temp) < 7)
  {
    continue;
  }

  list($from, $to,,,$code) = $temp;

  $from = trim($from, '"');
  $to = trim($to, '"');
  $code = trim($code, '"');

  if(!sfISO3166::isValidCode($code))
  {
    $invalid++;
    continue;
  }

  $stm = $db->prepare('INSERT INTO ip2country VALUES(?, ?, ?)');
  $stm->bindParam(1, $from);
  $stm->bindParam(2, $to);
  $stm->bindParam(3, $code);
  $stm->execute();

  $i++;

  if($i % 100 === 0)
  {
    printf("Inserted %s records.\n", $i);
  }

}

printf("Inserted %s records.\n", $i);
printf("Skipped %s invalid records.\n", $invalid);
