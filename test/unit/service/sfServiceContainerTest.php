<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
$t = new lime_test(4, new lime_output_color());

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


sfConfig::set('sf_lib_dir', '/foobar');

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
