<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfContextMock.class.php');
require_once(dirname(__FILE__).'/../sfCoreMock.class.php');
require_once(sfConfig::get('sf_sift_lib_dir').'/i18n/sfI18N.class.php');

$t = new lime_test(12, new lime_output_color());

$t->diag('i18n');
$i18n = new sfI18n(new sfContext());

$time =  mktime(10, 30, 0, 8, 1, 2008);

$t->is($i18n->getTimestamp('01/08/2008 10:30', 'fr'), $time, '->getTimestamp() returns the timestamp for a data formatted in the current culture');
$t->is($i18n->getTimestamp('08/01/2008 10:30', 'en_US'), $time, '->getTimestamp() returns the timestamp for a data formatted in the current culture');
$t->is($i18n->getTimestamp('08/01/2008', 'en_US'), mktime(0, 0, 0, 8, 1, 2008), '->getTimestamp() returns the timestamp for a data formatted in the current culture');
$t->is($i18n->getTimestamp('', 'en_US'), mktime(0, 0, 0, 0, 0, 0), '->getTimestamp() returns the timestamp for a data formatted in the current culture');
$t->is($i18n->getTimestamp('not a date', 'en_US'), mktime(0, 0, 0, 0, 0, 0), '->getTimestamp() returns the timestamp for a data formatted in the current culture');
$t->is($i18n->getTimestamp('10:30', 'en_US'), mktime(10, 30, 0, 0, 0, 0), '->getTimestamp() returns the timestamp for a data formatted in the current culture');

$t->is($i18n->getDate('01/08/2008 10:30', 'fr'), array(1, 8, 2008), '->getDate() returns the day, month and year for a data formatted in the current culture');
$t->is($i18n->getDate('08/01/2008 10:30', 'en_US'), array(1, 8, 2008), '->getDate() returns the day, month and year for a data formatted in the current culture');
$t->is($i18n->getDate('not a date', 'en_US'), null, '->getTime() returns null in case of conversion problem');


$t->is($i18n->getTime('01/08/2008 10:30', 'fr'), array(10, 30), '->getDate() returns the day, month and year for a data formatted in the current culture');
$t->is($i18n->getTime('08/01/2008 10:30', 'en_US'), array(10, 30), '->getDate() returns the day, month and year for a data formatted in the current culture');
$t->is($i18n->getTime('not a date', 'en_US'), null, '->getTime() returns null in case of conversion problem');

