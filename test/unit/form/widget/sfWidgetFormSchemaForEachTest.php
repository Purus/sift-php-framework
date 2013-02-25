<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(4);

$w1 = new sfWidgetFormInputText();
$w = new sfWidgetFormSchema(array('w1' => $w1));

// __construct()
$t->diag('__construct()');
$wf = new sfWidgetFormSchemaForEach($w, 2);
// avoid recursive loop in comparison
$wf[0]['w1']->setParent(null); $wf[1]['w1']->setParent(null);
$t->ok($wf[0]['w1'] !== $w1, '__construct() takes a sfWidgetFormSchema as its first argument');
$t->ok($wf[0]['w1'] == $w1, '__construct() takes a sfWidgetFormSchema as its first argument');
$t->ok($wf[1]['w1'] !== $w1, '__construct() takes a sfWidgetFormSchema as its first argument');
$t->ok($wf[1]['w1'] == $w1, '__construct() takes a sfWidgetFormSchema as its first argument');
