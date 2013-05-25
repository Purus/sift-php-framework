<?php
require_once(dirname(__FILE__) . '/../../../../bootstrap/unit.php');

$t = new lime_test(3);

$w = new sfWidgetFormPrice();

// ->render()
$t->diag('->render()');
$t->is($w->render('foo', ''), '<input class="price" type="text" name="foo" value="" id="foo" />', '->render() renders the widget as HTML');

$t->is($w->render('foo', '121234.121110110'), '<input class="price" type="text" name="foo" value="121,234.12111011" id="foo" />', '->render() renders the widget as HTML');

$t->diag('culture cs_CZ');

$w->setOption('culture', 'cs_CZ');

$t->is($w->render('foo', '121234.121110110'), '<input class="price" type="text" name="foo" value="121 234,12111011" id="foo" />', '->render() renders the widget as HTML');
