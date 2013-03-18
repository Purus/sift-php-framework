<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(4);

$w = new sfWidgetFormDateTime(array('culture' => 'cs_CZ'));
$t->is($w->render('date', '2013-05-05 15:00:00'), '<input class="date" type="text" name="date" value="5.5.2013 15:00" id="date" />', 'Widget is renderer correctly');

$w = new sfWidgetFormDateTime(array('culture' => 'en'));
$t->is($w->render('date', '2013-05-05 15:00:00'), '<input class="date" type="text" name="date" value="5/5/2013 15:00" id="date" />', 'Widget is renderer correctly');

$w = new sfWidgetFormDateTime(array('culture' => 'en', 'format_pattern' => 'long'));
$t->is($w->render('date', '2013-05-05 15:00:00'), '<input class="date" type="text" name="date" value="5/5/2013 15:00:00" id="date" />', 'Widget is renderer correctly');

$w = new sfWidgetFormDateTime(array('culture' => 'en', 'format_pattern' => 'dd MM hh ss'));
$t->is($w->render('date', '2013-05-05 15:00:00'), '<input class="date" type="text" name="date" value="05 05 15 00" id="date" />', 'Widget is renderer correctly with custom pattern');
