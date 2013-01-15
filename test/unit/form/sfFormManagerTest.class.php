<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6);

// disable aria support
sfWidgetForm::setAria(false);

class TestForm extends sfForm {}

$form = sfFormManager::getForm('Test');

$t->isa_ok($form, 'TestForm', 'getForm() returns form instance');

try {
  
  $form = sfFormManager::getForm('Nonsense');
  $t->fail('getForm() throws an exception if form not found.');  
}
catch(sfException $e)
{
  $t->pass('getForm() throws an exception if form not found.');
}

$form = sfFormManager::getForm('Test', array('f' => '1'));

$t->is($form->getDefaults(), array('f'=>'1'), 'getForm() passes defaults to the form class');

$form = sfFormManager::getForm('Test', array(), array('my_option' => 'yes'));

$t->is($form->getOptions(), array('my_option'=>'yes'), 'getForm() passes options to the form class');

$form = sfFormManager::getForm('Test', array(), array(), null);
$t->is($form->isCSRFProtected(), false, 'getForm() disables CSRF protection of the form');

$form = sfFormManager::getForm('Test', array(), array(), 'secretKey$');
$t->is($form->isCSRFProtected(), true, 'getForm() enabled CSRF protection of the form');
  
