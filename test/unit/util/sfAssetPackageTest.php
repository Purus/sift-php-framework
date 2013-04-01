<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfContextMock.class.php');
require_once(dirname(__FILE__).'/../sfCoreMock.class.php');


$t = new lime_test(14, new lime_output_color());

class myAssetPackage extends sfAssetPackage {

  public static function getConfig()
  {
    return self::$config;
  }
}

sfConfig::set('sf_sift_web_dir', '/sf/foobar');
sfConfig::set('sf_culture', 'en');

$config = sfYaml::load(dirname(__FILE__) . '/fixtures/asset_packages.yml');

myAssetPackage::setConfig($config['default']);

$t->isa_ok(myAssetPackage::getConfig(), 'array', '::getConfig() returns array');

$t->isa_ok(myAssetPackage::getJavascripts('date_picker'), 'array', '::getJavascripts() returns array');
$t->isa_ok(myAssetPackage::getStylesheets('date_picker'), 'array', '::getStylesheets() returns array');

$t->isa_ok(myAssetPackage::getAllPackages(), 'array', '::getAllPackages() returns array');
$t->is(count(myAssetPackage::getAllPackages()), 15, '::getAllPackages() returns packages');

$t->isa_ok(myAssetPackage::hasPackage('ui'), 'boolean', '::hasPackage() returns boolean');
$t->is(myAssetPackage::hasPackage('ui'), true, '::hasPackage() returns boolean');
$t->is(myAssetPackage::hasPackage('invalid_package'), false, '::hasPackage() returns boolean');

$t->is(myAssetPackage::getJavascripts('date_picker'), array(
    '//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js',
    '/sf/foobar/js/bootstrap/bootstrap-datetimepicker.js',
    '/sf/foobar/js/bootstrap/locales/bootstrap-datetimepicker.en.js'
), '::getJavascripts() returns array');

$t->is(myAssetPackage::getStylesheets('date_picker'), array(
    0 => array(
      '/sf/foobar/css/_/bootstrap/datetimepicker.print.less' => array('media' => 'print')
    ),
    1 => array(
      '/sf/foobar/css/_/bootstrap/datetimepicker.less' => array('media' => 'screen,projection,tv')
    )
), '::getStylesheets() returns array');


$t->is(myAssetPackage::getJavascripts('validation'), array(
  0 => '//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js',
  1 => '/sf/foobar/js/validate/jquery.validate.min.js',
  2 => '/sf/foobar/js/validate/additional-methods.min.js',
  3 => '/sf/foobar/js/validate/jquery.validate.custom_callback.min.js',
  4 => '/sf/foobar/js/validate/localization/messages_en.js',
), '::getJavascripts() returns array');

$t->is(myAssetPackage::getStylesheets('validation'), array(), '::getStylesheets() returns array');

$t->is(myAssetPackage::getJavascripts('dynamic_api'), array(
  0 => '//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js',
  1 =>
  array (
    '/sf/foobar/js/hover_intent/jquery.hoverIntent.minified.js' =>
    array (
      'generated' => true,
    ),
  )
), '::getJavascripts() returns array');


$t->is(myAssetPackage::getJavascripts('fancybox'), array(
  0 => '//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js',
  1 => '/js/jquery/fancybox/jquery.fancybox-1.3.4.js',
  2 =>
  array (
    '/js/jquery/fancybox/ie_hacks.js' =>
    array (
      'ie_condition' => 'lte IE 10',
    ),
  ),
), '::getJavascripts() returns array');

// http://trac.symfony-project.org/attachment/ticket/7588/AssetHelperPatch.diff
// http://exacttarget.github.com/fuelux/#spinner
