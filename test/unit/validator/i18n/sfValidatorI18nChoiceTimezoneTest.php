<?php

require_once(dirname(__FILE__) . '/../../../bootstrap/unit.php');

$t = new lime_test(1);

// ->configure()
$t->diag('->configure()');

// ->clean()
$t->diag('->clean()');
$v = new sfValidatorI18nChoiceTimezone();
$t->is($v->clean('Europe/Paris'), 'Europe/Paris', '->clean() cleans the input value');
