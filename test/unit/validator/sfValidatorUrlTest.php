<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(17);

$v = new sfValidatorUrl();

// ->clean()
$t->diag('->clean()');
foreach (array(
  'http://www.google.com',
  'https://google.com/',
  'https://google.com:80/',
  'http://www.example.com/',
  'http://127.0.0.1/',
  'http://127.0.0.1:80/',
  // These two seems like valid URLs to me
  'ftp://google.com/foo.tgz',
  'ftps://google.com/foo.tgz',
  'http://google.com/foo.tgz',
  'https://google.com/foo.tgz',
) as $url)
{
  try {
    $v->clean($url);
    $t->pass(sprintf('->clean() checks that the value "%s" is a valid URL', $url));
  }
  catch(Exception $e)
  {
    $t->fail(sprintf('->clean() checks that the value "%s" is a valid URL', $url));
  }

}

foreach (array(
  'google..com',
  'http:/google.com',
  'http://google.com::aa',
) as $nonUrl)
{
  try
  {
    $v->clean($nonUrl);
    $t->fail(sprintf('->clean() throws an sfValidatorError if the value "%s" is not a valid URL', $nonUrl));
    $t->skip('', 1);
  }
  catch (sfValidatorError $e)
  {
    $t->pass(sprintf('->clean() throws an sfValidatorError if the value "%s" is not a valid URL', $nonUrl));
    $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
  }
}

$v = new sfValidatorUrl(array('protocols' => array('http', 'https')));
try
{
  $v->clean('ftp://google.com/foo.tgz');
  $t->fail('->clean() only allows protocols specified in the protocols option');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() only allows protocols specified in the protocols option');
}
