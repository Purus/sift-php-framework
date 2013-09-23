<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(7, new lime_output_color());

define('TEMP_DIR', sys_get_temp_dir());

sfFileSafeStreamWrapper::register();

$tmp = tempnam(sys_get_temp_dir(), 'safe');

unlink($tmp);

// actually it creates temporary file
$handle = fopen('safe://' . $tmp, 'x');

fwrite($handle, 'atomic and safe');
// and now rename it
fclose($handle);

$contents = file_get_contents($tmp);

$t->is($contents, 'atomic and safe', 'data were written to the file');

// removes file thread-safe way
unlink('safe://' . $tmp);

$t->is(file_exists($tmp), false, 'unlink() deleted the file');

function randomStr()
{
  $s = str_repeat('Jesus is Lord', rand(100, 20000));
  return md5($s, true) . $s;
}

function checkStr($s)
{
  return substr($s, 0, 16) === md5(substr($s, 16), true);
}

define('COUNT_FILES', 3);
set_time_limit(0);

// clear playground
for($i = 0; $i <= COUNT_FILES; $i++)
{
  file_put_contents('safe://' . TEMP_DIR . '/testfile' . $i, randomStr());
}

// test loop
$hits = array('ok' => 0, 'notfound' => 0, 'error' => 0, 'cantwrite' => 0, 'cantdelete' => 0);

for($counter = 0; $counter < 1000; $counter++)
{
  // write
  $ok = file_put_contents('safe://' . TEMP_DIR . '/testfile' . rand(0, COUNT_FILES), randomStr());
  if($ok === false)
  {
    $hits['cantwrite']++;
  }

  // delete
  /*
  $ok = unlink('safe://' . TEMP_DIR . '/testfile' . rand(0, COUNT_FILES));
  if(!$ok)
  {
    $hits['cantdelete']++;
  }
  */

  // read
  $res = file_get_contents('safe://' . TEMP_DIR . '/testfile' . rand(0, COUNT_FILES));

  // compare
  if($res === false)
  {
    $hits['notfound']++;
  }
  elseif(checkStr($res))
  {
    $hits['ok']++;
  }
  else
  {
    $hits['error']++;
  }
}

$t->is($hits['ok'], 1000); // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
$t->is($hits['notfound'], 0); // means 'file not found', should be 0 if unlink() is not used
$t->is($hits['error'], 0); // means 'file contents is damaged', MUST be 0
$t->is($hits['cantwrite'], 0); // means 'somebody else is writing this file'
$t->is($hits['cantdelete'], 0); // means 'unlink() has timeout',  should be 0

// cleanup after the test
for($i = 0; $i <= COUNT_FILES; $i++)
{
  unlink(TEMP_DIR . '/testfile' . $i);
}
