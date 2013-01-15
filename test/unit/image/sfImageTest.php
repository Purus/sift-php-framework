<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(8, new lime_output_color());

$image = new sfImage();

$t->diag('load()');

try {
  $image->load('unknown');  
  $t->fail('load() throws an exception if the file does not exist');
}
catch(sfImageTransformException $e)
{
  $t->pass('load() throws an exception if the file does not exist');
}

$fixturesDir = dirname(__FILE__) . '/fixtures';
$image->load($fixturesDir . '/bible.jpg');
$mimeType = $image->getMIMEType();
$t->is($mimeType, 'image/jpeg', 'getMIMEType() returns correct result.');

$image->load($fixturesDir . '/bible_cover.tmp');
$mimeType = $image->getMIMEType();
$t->is($mimeType, 'image/jpeg', 'getMIMEType() returns correct result.');

$image->load($fixturesDir . '/bible_cover_png.jpe');
$mimeType = $image->getMIMEType();
$t->is($mimeType, 'image/png', 'getMIMEType() returns correct result with file wrong extension.');

$image->load($fixturesDir . '/bible_cover_png.jpe', 'image/png');
$mimeType = $image->getMIMEType();
$t->is($mimeType, 'image/png', 'getMIMEType() returns correct result with file wrong extension.');

$t->diag('saveAs()');

$tmp = tempnam(sys_get_temp_dir(), 'sfImage');
$image->saveAs($tmp);
        
$t->is(is_readable($tmp), true, 'saveAs() saves the file with its own default mime type.');
// cleanup
unlink($tmp);

$tmp = tempnam(sys_get_temp_dir(), 'sfImage');

$image->saveAs($tmp, 'image/jpeg');

// check of it has correct mime
$info = getimagesize($tmp);
// cleanup
unlink($tmp);

$t->is($info['mime'], 'image/jpeg', 'saveAs() saves the file with given mime type.');

$t->diag('Creating new images');

$tmp = sys_get_temp_dir() . '/sfImage.png';

$image = new sfImage();
$image->resize(100, null);
$image->saveAs($tmp);

$t->is(is_readable($tmp), true, 'saveAs() saves the file.');

// cleanup
unlink($tmp);
