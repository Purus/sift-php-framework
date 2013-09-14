<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(14);

class TestForm1 extends sfForm
{
  public function configure()
  {
    $this->disableCSRFProtection();
    $this->setWidgets(array(
      'a' => new sfWidgetFormInputText(),
      'b' => new sfWidgetFormInputText(),
      'c' => new sfWidgetFormInputText(),
    ));
    $this->setValidators(array(
      'a' => new sfValidatorString(array('min_length' => 2)),
      'b' => new sfValidatorString(array('max_length' => 3)),
      'c' => new sfValidatorString(array('max_length' => 1000)),
    ));
    $this->getWidgetSchema()->setLabels(array(
      'a' => '1_a',
      'b' => '1_b',
      'c' => '1_c',
    ));
    $this->getWidgetSchema()->setHelps(array(
      'a' => '1_a',
      'b' => '1_b',
      'c' => '1_c',
    ));
  }
}

class TestForm2 extends TestForm1 {

  public function getJavascriptFinalValidation(sfFormJavascriptValidationRulesCollection &$rules,
      sfFormJavascriptValidationMessagesCollection &$messages)
  {
    // modify the rule
    $rules['a']['required'] = false;
    $messages['a']['required'] = 'Enter the A!';
  }

}


$form = new TestForm1();

$t->diag('->getValidationRulesAndMessagesForForm()');

$result = sfFormJavascriptValidation::getValidationRulesAndMessagesForForm($form);

$t->isa_ok($result, 'array', 'getValidationRulesAndMessagesForForm() returns an array');
$t->is(count($result), 2, 'getValidationRulesAndMessagesForForm() returns an array with two elements');

list($rules, $messages) = $result;

$t->isa_ok($rules, 'sfFormJavascriptValidationRulesCollection', 'rules is a collection object');
$t->isa_ok($messages, 'sfFormJavascriptValidationMessagesCollection', 'rules is a collection object');

$t->is(count($rules), 3, 'each field has a rule object');
$t->is(count($messages), 3, 'each field has a message object');

$rulesEncoded = sfJson::encode($rules);

$t->ok(!empty($rulesEncoded), 'rules are successfully encoded to json');
$t->is(json_decode($rulesEncoded, true), array(
    'a' => array(
      'required' => true,
      'minlength' => 2,
    ),
    'b' => array(
      'required' => true,
      'maxlength' => 3,
    ),
    'c' => array(
      'required' => true,
      'maxlength' => 1000,
    ),

), 'rules are valid.');

$t->isa_ok(sfJson::encode($messages), 'string', 'Messages can be encoded to json');

list($rules, $messages) = sfFormJavascriptValidation::getValidationRulesAndMessagesForForm(new TestForm2());

$t->is_deeply(json_decode(sfJson::encode($rules), true), array(
    'a' => array(
      'required' => false,
      'minlength' => 2,
    ),
    'b' => array(
      'required' => true,
      'maxlength' => 3,
    ),
    'c' => array(
      'required' => true,
      'maxlength' => 1000,
    ),

), 'rules can be modified in getJavascriptFinalValidation method of the form.');

$t->is((string)$messages['a']['required'], 'Enter the A!', 'messages can be modified in getJavascriptFinalValidation method of the form.');

$t->diag('->getFormForm()');
$t->isa_ok(sfFormJavascriptValidation::getForForm($form), 'string', 'getFormForm() returns string');

class TranslatedForm extends sfForm {

  public function configure()
  {
    $this->setWidget('a', new sfWidgetFormInput());
    $this->setValidator('a', new sfValidatorString(array(
      'required' => true,
    ), array(
      'required' => 'This is a text to be translated',
    )));
  }

  public function translate($str, $params = array())
  {
    return $str . '-TRANSLATED';
  }
}

list($rules, $messages) = sfFormJavascriptValidation::getValidationRulesAndMessagesForForm(new TranslatedForm());

$t->is_deeply(json_decode(sfJson::encode($messages), true), array(
    'a' => array(
      'required' => 'This is a text to be translated-TRANSLATED'
    )
), 'messages are translated');

$t->is_deeply(json_decode(sfJson::encode($rules), true), array(
    'a' => array(
      'required' => true
    )
), 'rules are as expected');
