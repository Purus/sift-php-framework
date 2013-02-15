<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(40, new lime_output_color());

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

// orientation test
$t->diag('Orientation tests');

$images = array(
  'Landscape_1.jpg' => array(600, 450),
  'Landscape_2.jpg' => array(600, 450),
  'Landscape_3.jpg' => array(600, 450),
  'Landscape_4.jpg' => array(600, 450),
  'Landscape_5.jpg' => array(600, 450),
  'Landscape_6.jpg' => array(600, 450),
  'Landscape_7.jpg' => array(600, 450),
  'Landscape_8.jpg' => array(600, 450),
  'Portrait_1.jpg' => array(450, 600),
  'Portrait_2.jpg' => array(450, 600),
  'Portrait_3.jpg' => array(450, 600),
  'Portrait_4.jpg' => array(450, 600),
  'Portrait_5.jpg' => array(450, 600),
  'Portrait_6.jpg' => array(450, 600),
  'Portrait_7.jpg' => array(450, 600),
  'Portrait_8.jpg' => array(450, 600),    
);

// clear directory
sfToolkit::clearDirectory($fixturesDir . '/orientation/result/native');
sfToolkit::clearDirectory($fixturesDir . '/orientation/result/exiftool');

// test using native adapter
sfConfig::set('sf_image_exif_adapter', 'native');

$t->diag('Using Native adapter');

foreach($images as $imageSrc => $expectedValue)
{ 
  // load image with fix orientation enabled
  $image = new sfImage($fixturesDir . '/orientation/' . $imageSrc);
  $image->fixOrientation()
        ->setQuality(95)
        ->saveAs($fixturesDir . '/orientation/result/native/'.$imageSrc);
  
  $t->is($image->getWidth() . 'x' . $image->getHeight(), 
         $expectedValue[0] . 'x' . $expectedValue[1], sprintf('the dimensions of the result image "%s" are ok', $imageSrc));
  
}

$t->diag('Using ExifTool');

sfConfig::set('sf_image_exif_adapter', 'ExifTool');
sfConfig::set('sf_image_exif_adapter_options', array('exiftool_executable' => 'exiftool'));

foreach($images as $imageSrc => $expectedValue)
{ 
  // load image with fix orientation enabled
  $image = new sfImage($fixturesDir . '/orientation/' . $imageSrc);
  $image->fixOrientation()
        ->setQuality(95)
        ->saveAs($fixturesDir . '/orientation/result/exiftool/'.$imageSrc);
  
  $t->is($image->getWidth() . 'x' . $image->getHeight(), 
         $expectedValue[0] . 'x' . $expectedValue[1], sprintf('the dimensions of the result image "%s" are ok', $imageSrc));
  
}

$t->diag('');
$t->diag('');
$t->diag(sprintf('Please check the results in "%s" since I cannot see them. I am only a computer, but you have been created for a relation with your Creator, God.', $fixturesDir.'/orientation/result/native'));
$t->diag('Jesus Christ is the Only Way to Him!');
$t->diag('');
