<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(22, new lime_output_color());

$fixturesDir = dirname(__FILE__) . '/fixtures';

class myTestResponse extends sfWebResponse {

  public $headers = array();

  public function setHttpHeader($name, $value, $replace = true)
  {
    if(!$replace && isset($this->headers[$name]))
    {
      $this->headers[$name] = $this->headers[$name] . ','. $value;
    }
    else
    {
      $this->headers[$name] = $value;
    }
  }

  public function getHttpHeaders()
  {
    return $this->headers;
  }

}

$dispatcher = new sfEventDispatcher();
$request = new sfWebRequest();
$response = new sfWebResponse();
$logger = new sfConsoleLogger();

$download = new sfHttpDownload(array(), $request, $response, $dispatcher, $logger);

$t->isa_ok($download->getOptions(), 'array', 'download returns an array of default options');

$t->diag('->useResume()');

$t->is_deeply($download->useResume(), true, 'useResume() works ok');
$t->is_deeply($download->useResume(false), true, 'useResume() returns the old value');
$t->is_deeply($download->useResume(), false, 'useResume() switched the option');

$t->diag('->setBufferSize() ->getBufferSize()');

$t->is_deeply($download->getBufferSize(), 2097152, 'getBufferSize() returns default value');

try
{
  $download->setBufferSize('foo');
  $t->fail('setBufferSize() throws an exception if the size is not integer and greater than zero');
}
catch(InvalidArgumentException $e)
{
 $t->pass('setBufferSize() throws an exception if the size is not integer and greater than zero');
}

$download->setBufferSize(50);

$t->is_deeply($download->getBufferSize(), 50, 'getBufferSize() returns the value in bytes');

$t->diag('->limitSpeed()');

$t->is_deeply($download->limitSpeed(), false, 'limitSpeed() works ok');
$t->is_deeply($download->limitSpeed(150), false, 'limitSpeed() returns the old value');
$t->is_deeply($download->limitSpeed(), true, 'limitSpeed() switched the option');

$t->diag('->setFilename() ->getFilename()');

$download->setFilename('foo.txt');
$t->is_deeply($download->getFilename(), 'foo.txt', 'setFilename() sets the filename');

$t->diag('->setLastModified() ->getLastModified()');

$time = time();
$download->setLastModified($time);
$t->is_deeply($download->getLastModified(), $time, 'setLastModified() set the timestamp');

$time = new DateTime();
$download->setLastModified($time);

$t->is_deeply($download->getLastModified(), $time->format('U'), 'setLastModified() accepts DateTime object');

$time = new sfDate();
$download->setLastModified($time);

$t->is_deeply($download->getLastModified(), $time->format('U'), 'setLastModified() accepts sfDate object');

$t->diag('->setFile()');

try
{
  $download->setFile('a');
  $t->fail('setFile() throws sfFileException if the files does not exits');
}
catch(sfFileException $e)
{
  $t->pass('setFile() throws sfFileException if the files does not exits');
}

$download->setFile($fixturesDir.'/download.dat');
$t->is_deeply($download->getFileSize(), 31, 'setFilename() sets the file size');
$t->ok($download->getLastModified() > 0, 'setFilename() sets the last modified information');

$t->diag('->setData()');

$download->setData(file_get_contents($fixturesDir.'/download.dat') . 'additionalbytes');
$t->is_deeply($download->getFileSize(), 46, 'setData() sets the file size');
$t->ok($download->getLastModified() > 0, 'setFilename() sets the last modified information');

$t->diag('->setCacheControl() ->getCacheControl()');

$download->setCacheControl('public');

$t->is_deeply($download->getCacheControl(), 'public', 'setCacheControl works ok');

$t->diag('->setContentDisposition() ->getContentDisposition()');

$download->setContentDisposition('inline');

$t->is_deeply($download->getContentDisposition(), 'inline', 'setContentDisposition works ok');

$t->diag('->setContentType() ->getContentType()');

$download->setContentType('image/jpeg');

$t->is_deeply($download->getContentType(), 'image/jpeg', 'setContentType works ok');

$t->diag('->send() is tested in functional tests');


