<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$formatter = new sfI18nDateFormatter('en_US');
$t->is($formatter->format('September 18, 2013 11:29:10', 'f'), 'September 18, 2013 11:29 AM', 'format() works ok for "en" and format "f"');

$formatter = new sfI18nDateFormatter('en');
$t->is($formatter->format('September 18, 2013 11:29:10', 'd'), '9/18/13', 'format() works ok for "en" and format "d"');

$formatter = new sfI18nDateFormatter('cs');

$t->is($formatter->format('September 18, 2013 11:29:10', 'f'), '18. září 2013 11:29', 'format() works ok for "cs" and format "f"');

$t->is($formatter->format('September 18, 2013 11:29:10', 'd'), '18.9.2013', 'format() works ok for "cs" and format "d"');

// FIXME: Make test for other cultures