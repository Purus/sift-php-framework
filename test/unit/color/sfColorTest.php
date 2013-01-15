<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(11, new lime_output_color());

$color = new sfColor('#ffffff');

$t->is($color->toRgbInt(), array('red' => 255, 'green' => 255, 'blue' => 255), 'toRgbInt() works ok.');

$t->is($color->toRgbHex(), array('red' => 'ff', 'green' => 'ff', 'blue' => 'ff'), 'toRgbHex() works ok.');

// TODO!
// $t->is($color->toHsvFloat(), array(), 'toHsvFloat() works ok.');
// $t->is($color->toHsvInt(), array(), 'toHsvInt() works ok.');
// $t->is($color->getBrightness(), 255, 'getBrightness() works ok.');
// $t->is($color->getLuminance(), 255, 'getLuminance() works ok.');

// invert the color, it should be black
$color->invert();

$t->is($color->toRgbInt(), array('red' => 0, 'green' => 0, 'blue' => 0), 'toRgbInt() works ok.');

$mixed = $color->mix(new sfColor('#ffffff'));

$t->is($mixed->toString(), '#808080', 'mix() works ok.');

$t->is($mixed->makeWebSafe()->toString(), '#999999', 'makeWebSafe() works ok.');

// init via named color
$color = new sfColor('blue');
$t->is($color->toString(), '#0000ff', 'Creating of color with named color works ok.');

$t->is($color->makeWebSafe()->toString(), '#0000ff', 'makeWebSafe() works ok.');

// $t->is($mixed->lighten(0.5)->toString(), '#000000', 'lighten() works ok.');
// $t->is($mixed->darken(1)->toString(), '#000000', 'darken() works ok.');
$color = new sfColor('#cd3a3a');
$t->is($color->makeWebSafe()->toString(), '#cc3333', 'makeWebSafe() works ok.');

$color = new sfColor('#053e2e');
$t->is($color->makeWebSafe()->toString(), '#003333', 'makeWebSafe() works ok.');

$color = new sfColor('#ffffff');
$t->is($color->mix(new sfColor('#ff0000'), 100)->toString(), '#ff0000', 'makeWebSafe() works ok.');

$color = new sfColor('#000000');
$t->is($color->mix(new sfColor('#00ff00'), 50)->toString(), '#008000', 'makeWebSafe() works ok.');




