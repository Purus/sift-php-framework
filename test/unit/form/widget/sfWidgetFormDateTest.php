<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(5);

$w = new sfWidgetFormDate(array('culture' => 'cs_CZ'));
$t->is($w->render('date', '2013-12-03'), '<input class="date" type="text" name="date" value="3.12.2013" id="date" />', 'Rich widget is renderer correctly');

$w = new sfWidgetFormDate(array('culture' => 'sk_SK'));
$t->is($w->render('date', '2013-12-03'), '<input class="date" type="text" name="date" value="3.12.2013" id="date" />', 'Rich widget is renderer correctly');

$w = new sfWidgetFormDate(array('culture' => 'en'));
$t->is($w->render('date', 'ahoj'), '<input class="date" type="text" name="date" value="ahoj" id="date" />', 'Rich widget is renderer correctly');

$w = new sfWidgetFormDate(array('culture' => 'cs_CZ', 'format_pattern' => 'd.M.Y'));
$t->is($w->render('date', '2013-01-01'), '<input class="date" type="text" name="date" value="1.1.2013" id="date" />', 'Rich widget is renderer correctly with custom pattern');

$w = new sfWidgetFormDate(array('culture' => 'cs_CZ', 'format_pattern' => 'd.M.Y'));
$t->is($w->render('date', '1901-01-01'), '<input class="date" type="text" name="date" value="1.1.1901" id="date" />', 'Rich widget is renderer correctly with custom pattern and pre epoch date.');
