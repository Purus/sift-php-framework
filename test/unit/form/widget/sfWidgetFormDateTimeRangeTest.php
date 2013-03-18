<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

class FormFormatterMock extends sfWidgetFormSchemaFormatter
{
  public $translateSubjects = array();

  public function __construct() {}

  public function translate($subject, $parameters = array())
  {
    $this->translateSubjects[] = $subject;
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


$t = new lime_test(2);

// ->render()
$t->diag('->render()');

$ws = new sfWidgetFormSchema();
$ws->addFormFormatter('stub', $formatter = new FormFormatterMock());
$ws->setFormFormatterName('stub');
$w = new sfWidgetFormDateTimeRange(array('from' => new WidgetFormStub(), 'to' => new WidgetFormStub()));
$w->setParent($ws);
$t->is($w->render('foo'), 'translation[from ##WidgetFormStub## to ##WidgetFormStub##]', '->render() replaces %from% and %to%');
$t->is($formatter->translateSubjects, array('from %from% to %to%'), '->render() translates the template option');
