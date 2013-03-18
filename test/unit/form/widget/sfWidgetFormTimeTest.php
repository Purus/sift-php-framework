<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(3);

$w = new sfWidgetFormTime(array('culture' => 'cs_CZ'));
$t->is($w->render('date', '15:00'), '<input class="date" type="text" name="date" value="15:00" id="date" />', 'Rich widget is renderer correctly');

$w = new sfWidgetFormTime(array('culture' => 'cs_CZ', 'format_pattern' => 'HH'));
$t->is($w->render('date', '15:00'), '<input class="date" type="text" name="date" value="15" id="date" />', 'Rich widget is renderer correctly');

$w = new sfWidgetFormTime(array('culture' => 'cs_CZ', 'format_pattern' => 'full'));
$t->is($w->render('date', '15:00'), '<input class="date" type="text" name="date" value="15:00:00" id="date" />', 'Rich widget is renderer correctly');
