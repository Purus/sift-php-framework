<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$result = new sfAjaxResult();

$t->is($result->success, true, 'Default is success');

$array = array(
    'success' => false
);

$result = sfAjaxResult::createFromArray($array);
$t->is($result->success, false, 'Array can specify the success flag');

$result = new sfAjaxResult(false, 'my html', array(
    'foobar' => 'yes'
));

$t->is($result->success, false, 'constructor accepts success flag');
$t->is($result->html, 'my html', 'constructor accepts html parameter');
$t->is($result->foobar, 'yes', 'constructor additional arguments');
