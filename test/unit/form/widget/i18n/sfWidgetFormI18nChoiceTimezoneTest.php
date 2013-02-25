<?php

require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');


$t = new lime_test(4);

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');
$w = new sfWidgetFormI18nChoiceTimezone();
$dom->loadHTML($w->render('timezone', 'Europe/Paris'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#timezone option[value="Europe/Paris"]')->getValue(), 'Europe/Paris', '->render() renders all timezones as option tags');
$t->is(count($css->matchAll('#timezone option[value="Europe/Paris"][selected="selected"]')->getNodes()), 1, '->render() renders all timezones as option tags');

// add_empty
$t->diag('add_empty');
$w = new sfWidgetFormI18nChoiceTimezone(array('culture' => 'fr', 'add_empty' => true));
$dom->loadHTML($w->render('language', 'FR'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#language option[value=""]')->getValue(), '', '->render() renders an empty option if add_empty is true');

$w = new sfWidgetFormI18nChoiceTimezone(array('culture' => 'fr', 'add_empty' => 'foo'));
$dom->loadHTML($w->render('language', 'FR'));
$css = new sfDomCssSelector($dom);
$t->is($css->matchSingle('#language option[value=""]')->getValue(), 'foo', '->render() renders an empty option if add_empty is true');
