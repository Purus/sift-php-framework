<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
$t = new lime_test(17, new lime_output_color());

class sfContext {}

class testMailer {

  public $context;

  public function __construct($context)
  {
    $this->context = $context;
  }

}

class Newsletter {

  public $mailer;

  public function __construct(testMailer $mailer)
  {
    $this->mailer = $mailer;
  }

}

class Foo {

  public $options;

  public function __construct($options = array())
  {
    $this->options = $options;
  }

}

class ConfiguredObject {

  public function __toString()
  {
    return '_configured_';
  }

}

sfConfig::set('sf_lib_dir', '/foobar');
sfConfig::set('sf_cache_dir', '/cache/application');
sfConfig::set('sf_array_config', array('empty'));
sfConfig::set('sf_app', 'front');
sfConfig::set('sf_configured_object', new stdClass());
sfConfig::set('sf_configured_object_to_string', new ConfiguredObject());
sfConfig::set('what', 'this_is_what_i_dont_know');

$services = new sfServiceContainer();

$services->getDependencies()->set('context', new sfContext());

$services->register('mailer', sfServiceDefinition::createFromArray(array(
    'class' => 'testMailer',
    'arguments' => array(
      '$context', '%SF_LIB_DIR%'
    )
)));

$services->register('newsletter', sfServiceDefinition::createFromArray(array(
    'class' => 'Newsletter',
    'arguments' => array(
      '@mailer'
    )
)));

$mailer = $services->get('mailer');

$t->isa_ok($mailer, 'testMailer', 'Service returned the object');

$t->isa_ok($mailer->context, 'sfContext', 'The context is passed to the constructor');

$newsletter = $services->get('newsletter');

$t->isa_ok($newsletter, 'Newsletter', 'Service returned the object');

$t->is_deeply($newsletter->mailer, $mailer, 'Service returned the object');

$services->register('foo', sfServiceDefinition::createFromArray(array(
    'class' => 'Foo',
    'arguments' => array(
      array('option' => '%SF_CACHE_DIR%/foo', 'another' => '%SF_ARRAY_CONFIG%')
    )
)));

$foo = $services->get('foo');

$t->isa_ok($foo, 'Foo', 'Service returned the object');

$t->is_deeply($foo->options, array(
    'option' => '/cache/application/foo',
    'another' => array('empty')
), 'Service returned the object');

$t->diag('->resolveValue()');

$t->is($services->resolveValue('%SF_CACHE_DIR%'), '/cache/application', 'resolveValue() works ok');
$t->is($services->resolveValue('%SF_CACHE_DIR%/foobar'), '/cache/application/foobar', 'resolveValue() works ok');

$t->diag('->replaceConstants()');

$t->is($services->replaceConstants('%SF_CACHE_DIR%'), '/cache/application', 'replaceConstants() works ok');
$t->is($services->replaceConstants('%SF_CACHE_DIR%/foobar'), '/cache/application/foobar', 'replaceConstants() works ok');
$t->is($services->replaceConstants('%SF_CACHE_DIR%/%SF_APP%'), '/cache/application/front', 'replaceConstants() works ok');
$t->is($services->replaceConstants('/myfoo%SF_CACHE_DIR%/%SF_APP%/directory'), '/myfoo/cache/application/front/directory', 'replaceConstants() works ok');

$t->is($services->replaceConstants('/myfoo%SF_CACHE_DIR%/what/%SF_APP%/directory'), '/myfoo/cache/application/what/front/directory', 'replaceConstants() works ok');

try
{
  $services->replaceConstants('/myfoo%SF_CACHE_DIR%/%SF_ARRAY_CONFIG%/directory');
  $t->fail('Exception is thrown when using array in the value (when there is present more than one constant');
}
catch(LogicException $e)
{
  $t->pass('Exception is thrown when using array in the value (when there is present more than one constant');
}

try
{
  $services->replaceConstants('/myfoo%SF_CACHE_DIR%/%SF_CONFIGURED_OBJECT%/directory');
  $t->fail('Exception is thrown when using object without __toString() in the value (when there is present more than one constant');
}
catch(LogicException $e)
{
  $t->pass('Exception is thrown when using object without __toString() in the value (when there is present more than one constant');
}

$t->is($services->replaceConstants('/myfoo%SF_CACHE_DIR%/what/%SF_CONFIGURED_OBJECT_TO_STRING%/directory'), '/myfoo/cache/application/what/_configured_/directory', 'replaceConstants() works ok');

$t->is($services->replaceConstants('%SF_CACHE_DIR%/%WHAT%/%SF_CONFIGURED_OBJECT_TO_STRING%/directory/slash'), '/cache/application/this_is_what_i_dont_know/_configured_/directory/slash', 'replaceConstants() works ok');