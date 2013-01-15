<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(1);

$autoload = sfSimpleAutoload::getInstance();
$autoload->addFile(dirname(__FILE__).'/../sfEventDispatcherTest.class.php');
$autoload->register();

$t->is(class_exists('myeventdispatchertest'), true, '"sfSimpleAutoload" is case insensitive');
