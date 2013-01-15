<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$zip_fixtures = dirname(__FILE__) . '/fixtures/zip';

$zip = new sfZipArchive();

// opens zip archive
try {
  $zip->open($zip_fixtures.'/nonexisten_test.zip');
  $t->fail('->open() throws an exception for non existent file');
}
catch(sfPhpErrorException $e)
{
  $t->pass('->open() throws an exception for non existent file');
}

try {
  $zip->open($zip_fixtures.'/test.zip');
  $t->pass('->open() does not throw an exception for real file');
}
catch(sfPhpErrorException $e)
{
  $t->fail('->open() throws an exception for real file');
}

// create new archive

$zip->open($zip_fixtures . '/new.zip', sfZipArchive::CREATE);
$zip->addFromString('test.txt', 'file content goes here');
$zip->close();

if(file_exists($zip_fixtures . '/new.zip'))
{
  $t->pass('->open() with create flag creates new zip file');
  unlink($zip_fixtures . '/new.zip');
}
else
{
  $t->fail('->open() with create flag creates new zip file');
}

$zip->create($zip_fixtures . '/test2.zip');
$result = $zip->addDir($zip_fixtures . '/test', 'test');
$zip->close();

$zip->open($zip_fixtures . '/test2.zip');
$index = $zip->locateName('test\test.txt');

$t->is($index, 0, '->addDir() works correctly');

$zip->close();

unlink($zip_fixtures . '/test2.zip');

// static method
$t->is(sfZipArchive::extract($zip_fixtures . '/test.zip', sfToolkit::getTmpDir().'/zip'), true, '->extract() static call works ok');
