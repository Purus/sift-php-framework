<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$config = new sfAssetPackagesConfigHandler();
$config->initialize();

$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfAssetPackagesConfigHandler'.DIRECTORY_SEPARATOR;

// dev environment
sfConfig::set('sf_environment', 'dev');
$result = $config->evaluate( array($dir.'asset_packages.yml'));

$t->isa_ok($result['packages']['jquery']['javascripts'], 'array', '->evaluate() returns configuration file as an array');

$t->is($result['packages']['jquery']['javascripts'], array(
  0 =>
  array (
    '%SF_SIFT_WEB_DIR%/js/jquery/jquery-1.9.1.min.js' =>
    array (
      'position' => 'first',
    ),
  ),
  1 =>
  array (
    '//code.jquery.com/jquery-migrate-1.2.1.min.js' =>
    array (
      'position' => 'first',
    ),
  ),
  2 => 'only_dev_hooks.js',
), '->evaluate() returns configuration file as an array');


sfConfig::set('sf_environment', 'prod');
$result = $config->evaluate( array($dir.'asset_packages.yml'));

$t->isa_ok($result['packages']['jquery']['javascripts'], 'array', '->evaluate() returns configuration file as an array');

$t->is($result['packages']['jquery']['javascripts'], array(
  0 =>
  array (
    '//code.jquery.com/jquery-1.9.1.min.js' =>
    array (
      'position' => 'first',
    ),
  ),
  1 =>
  array (
    '//code.jquery.com/jquery-migrate-1.2.1.min.js' =>
    array (
      'position' => 'first',
    ),
  )
), '->evaluate() returns configuration file as an array');
