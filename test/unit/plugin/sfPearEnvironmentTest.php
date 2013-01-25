<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3);

@include_once('PEAR.php');
if (!class_exists('PEAR'))
{
  $t->skip('PEAR must be installed', 3);
  return;
}

require_once dirname(__FILE__).'/sfPearDownloaderTest.class.php';
require_once dirname(__FILE__).'/sfPearRestTest.class.php';
require_once dirname(__FILE__).'/sfPluginTestHelper.class.php';

// setup
$temp = tempnam('/tmp/sf_plugin_test', 'tmp');
unlink($temp);
mkdir($temp, 0777, true);

define('SF_PLUGIN_TEST_DIR', $temp);

$options = array(
  'plugin_dir'            => $temp.'/plugins',
  'cache_dir'             => $temp.'/cache',
  'preferred_state'       => 'stable',
  'rest_base_class'       => 'sfPearRestTest',
  'downloader_base_class' => 'sfPearDownloaderTest',
);

$dispatcher = new sfEventDispatcher();

// ->initialize()
$t->diag('->initialize()');

foreach (array('plugin_dir', 'cache_dir') as $option)
{
  try
  {
    $localOptions = $options;
    unset($localOptions[$option]);
    $environment = new sfPearEnvironment($dispatcher, $localOptions);

    $t->fail(sprintf('->initialize() throws an exception if you don\'t pass a "%s" option', $option));
  }
  catch (RuntimeException $e)
  {
    $t->pass(sprintf('->initialize() throws an exception if you don\'t pass a "%s" option', $option));
  }
}

// ->registerChannel()
$t->diag('->addChannel()');
$environment = new sfPearEnvironment($dispatcher, $options);
$environment->addChannel('pear.example.com', true);
$t->pass('->addChannel() registers a PEAR channel');

// teardown
sfToolkit::clearDirectory($temp);
rmdir($temp);
