<?php

require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(1);

$w = new sfWidgetFormTreeChoice(array('choices' => array(
    1 => array('title' => 'foobar'),
)));

$result = '<div class="tree multiple tree-checkbox"><ul role="list"><li role="listitem"><input name="id[]" type="checkbox" value="1" id="id_1" aria-labelledby="id_1_label" /> <label for="id_1" class="inline" id="id_1_label">foobar</label></li></ul></div>';

$t->is(fix_linebreaks($w->render('id')), fix_linebreaks($result), '->render() renders the tree');
