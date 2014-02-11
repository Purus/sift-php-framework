<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfMimeType.class.php');

$t = new lime_test(48, new lime_output_color());

$t->diag('->fixMimeType()');

$mime = 'image/jpeg';

$t->is(sfMimeType::fixMimeType($mime), 'image/jpeg', 'fixMimeType() works ok');

$t->diag('->getExtensionFromType()');

$mimeTypes = array(
  'image/jpeg' => '.jpeg',
  'application/x-gzip' => '.gz',
  'application/pdf' => '.pdf',
  'application/vnd.ms-excel' => '.xls',
  'audio/mpeg' => '.mp3'
);

foreach($mimeTypes as $mime => $expected)
{
  $t->is(sfMimeType::getExtensionFromType($mime), $expected, sprintf('getExtensionFromType() works ok for "%s"', $mime));
}

$mimeTypes = array(
  'image/jpeg' => 'jpeg',
  'application/gzip' => 'gz',
  'application/pdf' => 'pdf',
  'application/vnd.ms-excel' => 'xls',
);

foreach($mimeTypes as $mime => $expected)
{
  $t->is(sfMimeType::getExtensionFromType($mime, '', false), $expected, sprintf('getExtensionFromType() works ok for "%s"', $mime));
}

$t->diag('->getTypeFromExtension()');

foreach(array_flip($mimeTypes) as $extension => $expected)
{
  $t->is(sfMimeType::getTypeFromExtension($extension), $expected, sprintf('getTypeFromExtension() works ok for "%s"', $extension));
}

$t->diag('->getNameFromType()');

$mimeTypes = array(
  'image/jpeg' => 'JPEG image',
  'application/gzip' => 'Gzip archive',
  'application/pdf' => 'PDF document',
  'application/vnd.ms-excel' => 'Excel spreadsheet',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel 2007 spreadsheet',
  'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'PowerPoint 2007 show'
);

foreach($mimeTypes as $mime => $expected)
{
  $t->is(sfMimeType::getNameFromType($mime), $expected, sprintf('getNameFromType() works ok for "%s"', $mime));
}

$t->diag('->getTypeFromFile()');

$files = array( 
  'sample.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'sample.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',  
  'test.zip' => 'application/zip',      
  'ooo-6.0.doc' => 'application/msword',
  'ooo-test.odt' => 'application/vnd.oasis.opendocument.text',
  'foo.doc' => 'application/msword',
  'googleearth.kml' => 'application/vnd.google-earth.kml+xml',    
  'vector.svg' => 'image/svg+xml',
  'test.php' => 'application/x-php',
  'survey.js' => 'application/javascript',
  'example.json' => 'application/json',
  '560051.rss' => 'application/rss+xml',
  'my_jsme_stvoreni_k_jeho_obrazu.mp3' => 'audio/mpeg',
  'video.wmv' => 'video/x-ms-wmv',
);

$fixtures = dirname(__FILE__) . '/fixtures/mime';

foreach($files as $file => $expected)
{
  $t->is(sfMimeType::getTypeFromFile($fixtures . '/' . $file), $expected, sprintf('getTypeFromFile() works ok for "%s"', $file));
}

$t->diag('getTypeFromString()');

foreach($files as $file => $expected)
{
  $t->is(sfMimeType::getTypeFromString(file_get_contents($fixtures . '/' . $file), 'application/octet-stream', $file), $expected, sprintf('getTypeFromString() works ok for "%s"', $expected));
}

