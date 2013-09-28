<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
$t = new lime_test(10, new lime_output_color());

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

sfConfig::set('sf_lib_dir', '/foobar');
sfConfig::set('sf_cache_dir', '/cache/application');
sfConfig::set('sf_array_config', array('empty'));

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
