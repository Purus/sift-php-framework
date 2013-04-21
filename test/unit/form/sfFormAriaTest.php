<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(9);

// disable protection
sfForm::disableCSRFProtection();
// enable aria support
sfWidgetForm::setAria(true);

sfWidgetFormSchema::setDefaultFormFormatterName('Div');

class FormTest extends sfForm
{
}

class TestForm1 extends FormTest
{
  public function configure()
  {
    $this->disableCSRFProtection();
    $this->setWidgets(array(
      'a' => new sfWidgetFormInputText(),
      'b' => new sfWidgetFormInputText(),
      'password' => new sfWidgetFormInputPassword(),
      'checkbox' => new sfWidgetFormSelectCheckbox(array(
          'choices' => array(1 => 1, 2 => 'second choice', 3 => 3)
        )),
      'choice' => new sfWidgetFormChoice(array(
        'choices' => array(1 => 1, 2 => 2, 3 => 3)          
      ))  
    ));
    $this->setValidators(array(
      'a' => new sfValidatorString(array('required' => false)),
      'b' => new sfValidatorString(array('required' => true)),
      'password' => new sfValidatorString(array('required' => true)),
      'checkbox' => new sfValidatorChoice(array('choices' => array(1,2,3))), // default is required = true
      'choice' => new sfValidatorChoice(array('choices' => array(1,2,3))), // default is required = true
    ));
  }
}

class TestForm2 extends TestForm1
{
  public function configure()
  {    
    parent::configure();
    
    $this->widgetSchema->setNameFormat('test[%s]');
    
  }
}

$f = new TestForm1();
$f2 = new TestForm2();

$t->diag('input');

$t->is($f['a']->render(), '<input type="text" name="a" aria-labelledby="a_label" id="a" />', 'render() includes aria attributes');
$t->is($f['b']->render(), '<input type="text" name="b" aria-labelledby="b_label" aria-required="true" id="b" />', 'render() includes aria attributes');

$t->diag('password');

$t->is($f['password']->render(), '<input type="password" name="password" aria-labelledby="password_label" aria-required="true" id="password" />', 'render() includes aria attributess');

$t->is($f2['password']->render(), '<input type="password" name="test[password]" aria-labelledby="test_password_label" aria-required="true" id="test_password" />', 'render() includes aria attributes');

$output = <<<EOF
<div class="form-row">
<div class="form-field-wrapper">
<label for="test_password" class="required" id="test_password_label">Password <span class="required"><span>*</span></span></label>
<input type="password" name="test[password]" aria-labelledby="test_password_label" aria-required="true" id="test_password" />
</div>
</div>

EOF;


$t->is($f2['password']->renderRow(), $output, 'renderRow() includes aria attributes');

$t->is($f2['password']->renderLabel(), '<label for="test_password" class="required" id="test_password_label">Password <span class="required"><span>*</span></span></label>', 'renderLabel() works ok');

// binds some data
$f2->bind(array()); 
$t->is($f2['password']->renderError(), '<label for="test_password" role="alert" id="test_password_label" class="form-error">This value is required.</label>' . "\n", 'renderError() renders all atttributes');


$t->diag('checkbox');
$t->is($f['checkbox']->render(), file_get_contents(dirname(__FILE__). '/fixtures/aria_select_checkbox.html'), 'render() includes aria attributes');

$f->bind(array()); 

// FIXME: checkboxes have ids like checkbox_1, so the label for attribute is wrong
// but how to handle the label make it as label for the first of the checkboxes? 
$t->is($f['checkbox']->renderError(), '<label for="checkbox" role="alert" id="checkbox_label" class="form-error">This value is required.</label>'."\n", 'renderError() includes aria-required attribute');
