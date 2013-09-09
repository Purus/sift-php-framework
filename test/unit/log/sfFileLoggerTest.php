<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(7);

$file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sf_log_file.txt';

if(file_exists($file))
{
  unlink($file);
}
try
{
  $logger = new sfFileLogger();
  $t->fail('constructor options must contain a "file" option');
}
catch(Exception $e)
{
  $t->pass('->initialize() parameters must contain a "file" option');
}

// ->log()
$t->diag('->log()');
$logger = new sfFileLogger(array('file' => $file, 'date_format' => ''));
$logger->log('foo');
$lines = explode("\n", file_get_contents($file));
$t->like($lines[0], '/foo/', '->log() logs a message to the file');
$logger->log('bar');
$lines = explode("\n", file_get_contents($file));
$t->like($lines[1], '/bar/', '->log() logs a message to the file');

class TestLogger extends sfFileLogger
{
  public function getTimeFormat()
  {
    return $this->getOption('time_format');
  }  
}

// option: format
$t->diag('option: format');
// close pointer
$logger->shutdown();
unlink($file);


$logger = new TestLogger(array('file' => $file, 'date_format' => ''));
$logger->log('foo');
$t->is(file_get_contents($file), strftime($logger->getTimeFormat()).' Sift [info] foo'.PHP_EOL, 'contructor can take a format option');

// close pointer
$logger->shutdown();
unlink($file);

$logger = new TestLogger(array('file' => $file, 'format' => '%message%', 'date_format' => ''));
$logger->log('foo');
$t->is(file_get_contents($file), 'foo', 'contructor can take a format option');
$logger->shutdown();

// option: time_format
$t->diag('option: time_format');

unlink($file);

$logger = new TestLogger(array('file' => $file, 'time_format' => '%Y %m %d', 'date_format' => ''));
$logger->log('foo');

$t->is(file_get_contents($file), strftime($logger->getTimeFormat()).' Sift [info] foo'.PHP_EOL, 'contructor can take a format option');

// option: type
$t->diag('option: type');

// close pointer
$logger->shutdown();
unlink($file);
$logger = new TestLogger(array('file' => $file, 'type' => 'foo', 'date_format' => ''));
$logger->log('foo');
$t->is(file_get_contents($file), strftime($logger->getTimeFormat()).' foo [info] foo'.PHP_EOL, 'contructor can take a format option');

// ->shutdown()
$t->diag('->shutdown()');
$logger->shutdown();

unlink($file);