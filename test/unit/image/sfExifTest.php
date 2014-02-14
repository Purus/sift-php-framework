<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(108, new lime_output_color());

$fixturesDir = dirname(__FILE__).'/fixtures';

function __($msg, $params = array())
{
  return $msg;
}

try { 
  
  sfExif::factory('invalid_exif_adapter', array());
  
  $t->fail('Factory method throws exception when using invalid adapter');
}
catch(InvalidArgumentException $e)
{
  $t->pass('Factory method throws exception when using invalid adapter');
}


class sfExifAdapterMyExifTool extends sfExifAdapter {
  
  public function getData($file)
  {
    return 'EXIF DATA COOL';
  }
  
  public function supportedCategories()
  {
    return array('EXIF');
  }  
  
}

$exif = new sfExif('MyExifTool', array());
$t->is(get_class($exif), 'sfExif', 'sfExif is correctly created');
$t->is(get_class($exif->getAdapter()), 'sfExifAdapterMyExifTool', 'Adapter is correctly created');
$t->is($exif->getData('fooFile'), 'EXIF DATA COOL', 'Method is passed to the adapter');
$t->isa_ok($exif->supportedCategories(), 'array', '->supportedCategories() returns an array');

$t->is($exif->supportedCategories(), array('EXIF'), '->supportedCategories() returns an array');

try {
  
  $exif->invalidMethod('foobar');  
  $t->fail('Call of invalid method throws an exception');  
}
catch(BadMethodCallException $e)
{
  $t->pass('Call of invalid method throws an exception');
}

// 16 images
$images = array(
    'Landscape_1.jpg' => 1,
    'Landscape_2.jpg' => 2,
    'Landscape_3.jpg' => 3,
    'Landscape_4.jpg' => 4,
    'Landscape_5.jpg' => 5,
    'Landscape_6.jpg' => 6,
    'Landscape_7.jpg' => 7,
    'Landscape_8.jpg' => 8,
    'Portrait_1.jpg' => 1,
    'Portrait_2.jpg' => 2,
    'Portrait_3.jpg' => 3,
    'Portrait_4.jpg' => 4,
    'Portrait_5.jpg' => 5,
    'Portrait_6.jpg' => 6,
    'Portrait_7.jpg' => 7,
    'Portrait_8.jpg' => 8,
);

$return = null;
$output = array();
@exec('exiftool', $output, $return);

if ($return) {
  $t->skip('exiftool is not installed in the PATH', 85);
} else {
    // real test, requires exiftool executable to be installed
    $exif = new sfExif('ExifTool', array('exiftool_executable' => 'exiftool'));

    $data = $exif->getData(dirname(__FILE__).'/fixtures/bible.jpg');

    $t->isa_ok($data, 'array', 'getData() returned array');

    $t->is($data, array(
        'FileSize' => 387760,
        'XResolution' => 100,
        'YResolution' => 100,
        'ResolutionUnit' => 0,
    ), 'getData() returned array');

    $data = $exif->getData(dirname(__FILE__).'/fixtures/ck.jpg');
    $t->isa_ok($data, 'array', 'getData() returned array');
    $t->isa_ok(isset($data['Orientation']), true, 'getData() returned orientation information');

    $data = $exif->getData(dirname(__FILE__).'/fixtures/gps.jpg');
    $t->isa_ok(isset($data['GPSLatitude']) && isset($data['GPSLongitude']), true, 'getData() returned GPS information');

    $fields = $exif->getSupportedFields();

    foreach($data as $field => $value)
    {
        $t->isa_ok($fields[$field], 'array', sprintf('The field "%s" is known field.' , $fields[$field]['description']));
        $humanValue = sfExif::getHumanReadable($field, $value);
        $t->isa_ok(!empty($humanValue), true, sprintf('Returns human readable format for "%s"', $field));
    }

    foreach($images as $image => $expectedValue)
    {
        $data = $exif->getData($fixturesDir.'/orientation/'.$image);
        $t->is($data['Orientation'], $expectedValue, sprintf('Orientation is read successfully and correctly for "%s"', $image));
    }
}

// test is with native php
if (!function_exists('exif_read_data')) {
  $t->skip('Exif read data is not installed', 16);
} else {

    $t->diag('native adapter');

    $exif = new sfExif('native');

    foreach($images as $image => $expectedValue)
    {
        $data = $exif->getData($fixturesDir.'/orientation/'.$image);
        $t->is($data['Orientation'], $expectedValue, sprintf('Orientation is read successfully and correctly for "%s"', $image));
    }
}
