<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(12, new lime_output_color());

class myConfigHandler extends sfConfigHandler
{
  public function execute($configFiles) {}
}

$config = new myConfigHandler();
$config->initialize();

// ->initialize()
$t->diag('->initialize()');
$config->initialize(array('foo' => 'bar'));
$t->is($config->getParameterHolder()->get('foo'), 'bar', '->initialize() takes an array of parameters as its first argument');

// ::replaceConstants()
$t->diag('::replaceConstants()');
sfConfig::set('foo', 'bar');
$t->is(sfConfigHandler::replaceConstants('my value with a %foo% constant'), 'my value with a bar constant', '::replaceConstants() replaces constants enclosed in %');

$t->is(sfConfigHandler::replaceConstants('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstants() does not replace unknown constants');

sfConfig::set('foo', 'bar');
$value = array(
  'foo' => 'my value with a %foo% constant',
  'bar' => array(
    'foo' => 'my value with a %foo% constant',
  ),
);
$value = sfConfigHandler::replaceConstants($value);
$t->is($value['foo'], 'my value with a bar constant', '::replaceConstants() replaces constants in arrays recursively');
$t->is($value['bar']['foo'], 'my value with a bar constant', '::replaceConstants() replaces constants in arrays recursively');

// ->getParameterHolder()
$t->diag('->getParameterHolder()');
$t->isa_ok($config->getParameterHolder(), 'sfParameterHolder', "->getParameterHolder() returns a parameter holder instance");

// ->replacePath()
$t->diag('->replacePath()');
sfConfig::set('sf_app_dir', 'ROOTDIR');
$t->is($config->replacePath('test'), 'ROOTDIR/test', '->replacePath() prefix a relative path with "sf_app_dir"');
$t->is($config->replacePath('/test'), '/test', '->replacePath() prefix a relative path with "sf_app_dir"');

$t->diag('->parseCondition()');

sfConfig::add(array(
  'sf_web_debug' => true
));

$t->is_deeply(sfConfigHandler::parseCondition('%SF_WEB_DEBUG%'), true, '::parseCondition() works ok');
$t->is_deeply(sfConfigHandler::parseCondition('!%SF_WEB_DEBUG%'), false, '::parseCondition() works ok');
$t->is_deeply(sfConfigHandler::parseCondition('!!%SF_WEB_DEBUG%'), true, '::parseCondition() works ok');
$t->is_deeply(sfConfigHandler::parseCondition('!!!%SF_WEB_DEBUG%'), false, '::parseCondition() works ok');
