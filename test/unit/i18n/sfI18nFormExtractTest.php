<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(3, new lime_output_color());

class mySuperForm extends myForm {

  public function configure()
  {
    $this->setWidget('a', new sfWidgetFormInput());
    $this->setLabel('a', 'my label');
  }

  public function getTranslationCatalogue()
  {
    return dirname(__FILE__) . '/fixtures/my_super_form';
  }

}

class Foo {
  public $label = 'my foo label';
}

class myFormWithArguments extends mySuperForm  {

  protected $foo;

  public function __construct(Foo $foo, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->foo = $foo;
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  public function configure()
  {
    $this->setWidget('foo', new sfWidgetFormInput());
    $this->setLabel('foo', $this->foo->label);
  }

}

class myFormWithArgumentsI18nImplemented extends myFormWithArguments implements sfII18nExtractableForm {

  public static function __construct_I18n()
  {
    return new myFormWithArgumentsI18nImplemented(new Foo());
  }
}

$extractor = new sfI18nFormExtract(array(
  'culture' => 'en_GB',
  'form' => 'mySuperForm'
));

$messages = $extractor->extract();

$expected = array(
  dirname(__FILE__) . '/fixtures/my_super_form' => array(
    'my label'
));

$t->is($messages, $expected, '->extract() returns messages from standard form');

try {

  $extractor = new sfI18nFormExtract(array(
    'culture' => 'en_GB',
    'form' => 'myFormWithArguments'
  ));
  $t->fail('constructor throws an exception if the form cannot be extracted by standard way');
}
catch(Exception $e)
{
  $t->pass('constructor throws an exception if the form cannot be extracted by standard way');
}

$extractor = new sfI18nFormExtract(array(
  'culture' => 'en_GB',
  'form' => 'myFormWithArgumentsI18nImplemented'
));

$messages = $extractor->extract();

$expected = array(
  dirname(__FILE__) . '/fixtures/my_super_form' => array(
    'my foo label'
));

$t->is($messages, $expected, '->extract() returns messages from standard form');