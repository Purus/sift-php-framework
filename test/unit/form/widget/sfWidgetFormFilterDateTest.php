<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

class FormFormatterStub extends sfWidgetFormSchemaFormatter
{
  public function __construct() {}

  public function translate($subject, $parameters = array())
  {
    return sprintf('translation[%s]', $subject);
  }
}

class WidgetFormStub extends sfWidget
{
  public function __construct() {}

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return sprintf('##%s##', __CLASS__);
  }
}

$t = new lime_test(1);

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');

$ws = new sfWidgetFormSchema();
$ws->addFormFormatter('stub', new FormFormatterStub());
$ws->setFormFormatterName('stub');
$w = new sfWidgetFormFilterDate(array('from' => new WidgetFormStub(), 'to' => new WidgetFormStub()));
$w->setParent($ws);
$dom->loadHTML($w->render('foo'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('label[for="foo_is_empty"]')->getValue(), 'translation[is empty]', '->render() translates the empty_label option');

