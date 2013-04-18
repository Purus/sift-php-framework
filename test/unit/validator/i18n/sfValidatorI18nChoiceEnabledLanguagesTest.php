<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(3);

sfConfig::set('sf_i18n_enabled_cultures', array('en', 'cs'));

$v = new sfValidatorI18nChoiceEnabledLanguages();

$t->is($v->getOption('choices'), array('en', 'cs'), '->configure() can restrict the number of languages with the languages option');
// ->clean()
$t->diag('->clean()');
$t->is($v->clean('cs'), 'cs', '->clean() cleans the input value');

try {

  $v->clean('foo');
  $t->fail('error is thrown for invalid value');

}
catch(sfValidatorError $e)
{
  $t->pass('error is thrown for invalid value');
}
