<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4);

$executable = getenv('CLAMSCAN_EXECUTABLE');
$database = getenv('CLAMSCAN_DATABASE');

$t->diag('scan()');

if($executable)
{
  $antivir = sfAntivirus::factory('clamav', array(
    'executable' => $executable,
    'database' => $database
  ));

  $clean = $antivir->scan(dirname(__FILE__).'/infected');
  $t->isa_ok($clean, 'array', 'scan() returns an array');

  $t->is($clean, array(
      'INFECTED', array('Eicar-Test-Signature')
  ), 'scan() returns expected result');

  $clean = $antivir->scan(dirname(__FILE__).'/clean');
  $t->isa_ok($clean, 'array', 'scan() returns an array');
  $t->is($clean, array(
      'OK', array()
  ), 'scan() returns expected result');

}
else
{
  $t->skip('Clamav executable is missing in the environment variables. Cannot test.', 4);
}


