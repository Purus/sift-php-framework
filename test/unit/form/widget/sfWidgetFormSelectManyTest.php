<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(1);

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');
$w = new sfWidgetFormSelectMany(array('choices' => array('foo' => 'bar', 'foobar' => 'foo')));
$t->is($w->getOption('multiple'), true, '__construct() creates a multiple select tag');
