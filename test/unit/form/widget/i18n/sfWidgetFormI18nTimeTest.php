<?php

require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(4);

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->configure()
$t->diag('->configure()');

$w = new sfWidgetFormI18nTime(array('culture' => 'fr'));
$t->is($w->getOption('format'), '%hour%:%minute%:%second%', '->configure() automatically changes the date format for the given culture');
$t->is($w->getOption('format_without_seconds'), '%hour%:%minute%', '->configure() automatically changes the date format for the given culture');

$w = new sfWidgetFormI18nTime(array('culture' => 'sr'));
$t->is($w->getOption('format'), '%hour%.%minute%.%second%', '->configure() automatically changes the date format for the given culture');
$t->is($w->getOption('format_without_seconds'), '%hour%.%minute%', '->configure() automatically changes the date format for the given culture');
