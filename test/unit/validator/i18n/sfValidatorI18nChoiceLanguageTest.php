<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(3);

// ->configure()
$t->diag('->configure()');

try
{
  new sfValidatorI18nChoiceLanguage(array('languages' => array('xx')));
  $t->fail('->configure() throws an InvalidArgumentException if a language does not exist');
}
catch(InvalidArgumentException $e)
{
  $t->pass('->configure() throws an InvalidArgumentException if a language does not exist');
}

$v = new sfValidatorI18nChoiceLanguage(array('languages' => array('fr', 'en')));
$t->is($v->getOption('choices'), array('en', 'fr'), '->configure() can restrict the number of languages with the languages option');

// ->clean()
$t->diag('->clean()');
$v = new sfValidatorI18nChoiceLanguage(array('languages' => array('fr', 'en')));
$t->is($v->clean('fr'), 'fr', '->clean() cleans the input value');
