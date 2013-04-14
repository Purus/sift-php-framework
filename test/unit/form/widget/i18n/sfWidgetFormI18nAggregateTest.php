<?php

require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(4);

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

$widget = new sfWidgetFormInputText();
$widget->setLabel('Title');

$w = new sfWidgetFormI18nAggregate(array('cultures' => array('cs_CZ'),
                                     'widget' => $widget));

$t->is($w->render('foo'), '<label for="foo_cs_CZ"><span class="flag flag-cz"></span> Title</label> <input type="text" name="foo[cs_CZ]" id="foo_cs_CZ" />', 'render() renders one culture as standalone');

$w = new sfWidgetFormI18nAggregate(array('cultures' => array('cs_CZ', 'en_GB', 'sk'),
                                     'widget' => $widget));

$dom->loadHTML($w->render('foo', array('cs_CZ' => 'default value is foo')));
$css = new sfDomCssSelector($dom);

$inputs = $css->matchAll('input[type="text"]');
$t->is(count($inputs), 3, '->render() renders inputs for all cultures');
$t->is($inputs->getNode()->getAttribute('value'), 'default value is foo', 'default value is set');

// sfCallable test

function get_cultures()
{
  return array(
    'de_DE'
  );
}

$w = new sfWidgetFormI18nAggregate(array('cultures' => new sfCallable('get_cultures'),
                                         'widget' => $widget));

$t->is($w->render('foo'), '<label for="foo_de_DE"><span class="flag flag-de"></span> Title</label> <input type="text" name="foo[de_DE]" id="foo_de_DE" />', 'render() renders works if cultures is an instance of sfCallable');