<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
$t = new lime_test(6);

$fixturesDir = dirname(__FILE__) . '/fixtures';

$zip = new sfZipArchive();

$temp = sys_get_temp_dir();
$outZipPath = $temp . '/test.zip';

if(is_file($outZipPath))
{
  unlink($outZipPath);
}

$opened = $zip->open($outZipPath, sfZipArchive::CREATE);
$t->isa_ok($opened, 'boolean', 'open() returns boolean');
$t->is_deeply($opened, true, 'open() opened the archive');
$zip->addFile($fixturesDir . '/bible.jpg', 'bible.jpg');
$t->is($zip->numFiles, 1, 'file is added to the zip');
$files = array();

for($i = 0; $i < $zip->numFiles; $i++)
{
  $stat = $zip->statIndex($i);
  $files[] = $stat['name'];
}

$t->is($files, array('bible.jpg'), 'The created zip contains the files.');
$zip->close();

unlink($outZipPath);

$opened = $zip->open($fixturesDir.'/bible.zip');

$files = array();

for($i = 0; $i < $zip->numFiles; $i++)
{
  $stat = $zip->statIndex($i);
  $files[] = $stat['name'];
}
$zip->close();
$t->is($files, array('bible.jpg'), 'The existing zip contains the files.');

$zip->open($fixturesDir.'/bible.zip');

try {
  $zip->addFile("foo.dat");
  $t->fail('addFile() throws an exception if the file does not exist');
}
catch(Exception $e)
{
  $t->pass('addFile() throws an exception if the file does not exist');
}

$zip->close();