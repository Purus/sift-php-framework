<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../../lib/util/data_uri/sfDataUri.class.php');

$t = new lime_test(33);

$dataUri = new sfDataUri();

$t->is(sfDataUri::DEFAULT_TYPE, $dataUri->getMediaType(), 'getMediaType() returns default media type');

$dataUri->setMediaType('image/gif');
$t->is('image/gif', $dataUri->getMediaType());

$dataUri->setMediaType('image/png');
$t->is('image/png', $dataUri->getMediaType());

$dataUri->setMediaType('');
$t->is(sfDataUri::DEFAULT_TYPE, $dataUri->getMediaType());

$t->is(sfDataUri::ENCODING_URL_ENCODED_OCTETS, $dataUri->getEncoding());

$dataUri->setEncodedData(sfDataUri::ENCODING_BASE64, '');
$t->is(sfDataUri::ENCODING_BASE64, $dataUri->getEncoding());

$dataUri->setEncodedData(sfDataUri::ENCODING_BASE64, 'Example');
$t->is('Example', $dataUri->getEncodedData());

$dataUri->setEncodedData(sfDataUri::ENCODING_BASE64, 'Example');
$t->is('Example', $dataUri->getEncodedData());

try {
  // @expectedException InvalidArgumentException
  $dataUri->setEncodedData(null, 'Example');
  $t->fail('InvalidArgumentException is thrown when setting invalid encoding');
}
catch(InvalidArgumentException $e)
{
  $t->pass('InvalidArgumentException is thrown when setting invalid encoding');
}

$dataUri->setData('', sfDataUri::ENCODING_BASE64);
$t->is('', $dataUri->getEncodedData());

$dataUri->setData('ABC<>\/.?^%L');
$t->is(rawurlencode('ABC<>\/.?^%L'), $dataUri->getEncodedData());

$dataUri->setData('KFJ%&L"%*||`', sfDataUri::ENCODING_URL_ENCODED_OCTETS);
$t->is(rawurlencode('KFJ%&L"%*||`'), $dataUri->getEncodedData());

$dataUri->setData('~:{}[123S', sfDataUri::ENCODING_BASE64);
$t->is(base64_encode('~:{}[123S'), $dataUri->getEncodedData());

$dataUri->setData('', sfDataUri::ENCODING_URL_ENCODED_OCTETS);
$t->is('', $dataUri->getEncodedData());

// @expectedException InvalidArgumentException
try {
  $dataUri->setEncodedData('', null);
  $t->fail();
}
catch(InvalidArgumentException $e)
{
  $t->pass();
}

$dataUri->setData('', sfDataUri::ENCODING_BASE64);
$t->is_deeply($decodedData = $dataUri->getDecodedData(), '');

// Default encoding type
$dataUri->setData('ABC<>\/.?^%L');
$t->is_deeply($decodedData = $dataUri->getDecodedData(), 'ABC<>\/.?^%L');

// URL encoded octet encoding with value
$dataUri->setData('KFJ%&L"%*||`', sfDataUri::ENCODING_URL_ENCODED_OCTETS);
$t->is_deeply($decodedData = $dataUri->getDecodedData(), 'KFJ%&L"%*||`');

// Base64 with value
$dataUri->setData('~:{}[123S', sfDataUri::ENCODING_BASE64);
$t->is($decodedData = $dataUri->getDecodedData(), '~:{}[123S');

// URL encoded octet with emtpy value
$dataUri->setData('', sfDataUri::ENCODING_URL_ENCODED_OCTETS);
$t->is_deeply($decodedData = $dataUri->getDecodedData($decodedData), '');

// Encoded data set through DataUri::setEncodedData()
$dataUri->setEncodedData(sfDataUri::ENCODING_BASE64, base64_encode('MGH4%"L4;FF'));
$t->is_deeply($decodedData = $dataUri->getDecodedData($decodedData), 'MGH4%"L4;FF');

$dataUri = new sfDataUri();
$t->is_deeply('data:,', $dataUri->toString());

$dataUri->setMediaType('image/png');
$dataUri->setData('HG2/$%&L"34A', sfDataUri::ENCODING_BASE64);

$encoded = base64_encode('HG2/$%&L"34A');
$t->is_deeply("data:image/png;base64,{$encoded}",
$dataUri->toString());

//$dataUri->setEncodedData(DataUri::ENCODING_BASE64, null);
//$decodedData = null;
//$this->isFalse($dataUri->tryDecodeData($decodedData));

$t->is_deeply(sfDataUri::isParsable(''), false);
$t->is_deeply(sfDataUri::isParsable('data:,'), true);
$t->is_deeply(sfDataUri::isParsable('data:text/plain;charset=US-ASCII;base64,ABC'), true);

$t->diag('tryParse');
$t->is_deeply(sfDataUri::tryParse(''), false);

$parsed = sfDataUri::tryParse('data:,');
$t->ok($parsed == new sfDataUri());

$t->isa_ok($dataUri, 'sfDataUri');

$parsed = sfDataUri::tryParse('data:image/png;base64,');
$t->ok($parsed == new sfDataUri('image/png', '', sfDataUri::ENCODING_BASE64));

try
{
  sfDataUri::tryParse('dataimage/png;base64,', true);
  $t->fail('sfParseException is thrown when the data uri cannot be parsed an throw exception is true');
}
catch(sfParseException $e)
{
  $t->pass('sfParseException is thrown when the data uri cannot be parsed an throw exception is true');
}

$t->diag('tryParse() with parameters');

$t->isa_ok(sfDataUri::tryParse("data:text/plain;charset=utf-8,%23%24%25"), 'sfDataUri', 'DataUri with parameters works ok');

$i = 0;
$string = '';
while($i < sfDataURI::ATTS_TAG_LIMIT + 1)
{
  $string .= 'x';
  $i++;
}

try
{
  sfDataUri::tryParse('data:image/png;base64,'.$string, true);
  $t->fail('InvalidArgumentException if thrown when the data is too long');
}
catch(InvalidArgumentException $e)
{
  $t->pass('InvalidArgumentException if thrown when the data is too long');
}


