<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(2);

$loader = new sfClassLoader();
$loader->add('Bible',  dirname(__FILE__).'/lib');
$loader->register();
 
$t->is(class_exists('Bible'), true, 'Autoloading works ok.');
$t->is(class_exists('Bible_Chapter'), true, 'Autoloading works ok.');
