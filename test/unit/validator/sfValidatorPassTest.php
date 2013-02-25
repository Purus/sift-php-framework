<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(2);

$v = new sfValidatorPass();

// ->clean()
$t->diag('->clean()');
$t->is($v->clean(''), '', '->clean() always returns the value unmodified');
$t->is($v->clean(null), null, '->clean() always returns the value unmodified');
