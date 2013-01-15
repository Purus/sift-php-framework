<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(22, new lime_output_color());

$context = new sfContext();
$v = new sfUrlValidator();
$v->initialize($context);

// ->execute()
$t->diag('->execute()');

$validUrls = array(
  'http://www.google.com',
  'https://google.com/',
  'http://www.symfony-project.com/',
  'ftp://www.symfony-project.com/file.tgz',
  'http://www.google.com:8080', 
  'http://192.168.1.1', 
);

$invalidUrls = array(
  'google.com',
  'http:/google.com',
  'http://www.symfony-project,com/', 
  'http://www.symfony-project@com', 
  'http://www.symfony-project@com foobar', 
);

$v->initialize($context);
foreach ($validUrls as $value)
{
  $error = null;
  $t->ok($v->execute($value, $error), sprintf('->execute() returns true for a valid URL "%s"', $value));
  $t->is($error, null, '->execute() doesn\'t change "$error" if it returns true');
}

foreach ($invalidUrls as $value)
{
  $error = null;
  $t->ok(!$v->execute($value, $error), sprintf('->execute() returns false for an invalid URL "%s"', $value));
  $t->isnt($error, null, '->execute() changes "$error" with a default message if it returns false');
}
