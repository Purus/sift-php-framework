<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

// init new test with color output that expects 5 tests to be run
$t = new lime_test(5, new lime_output_color());

$dimensions = new sfDimensions(array(
  'culture' => array(
    'en', 'sk'
  ),
  'brand' => array(
    'classic', 'corporate'
  ),
));

// check to make sure dimensions start empty
$t->diag('defaults');
$t->is($dimensions->getCurrentDimension(), array(
   'culture' => 'en', 'brand' => 'classic'
), 'sfDimension::getCurrentDimension return current dimension');

// check to make sure setting dimensions functions properly
$t->diag('setting');

$dimensions->setCurrentDimension(array(
    'culture' => 'sk', 'brand' => 'corporate')
);

$t->is($dimensions->getCurrentDimension(), array(
    'culture' => 'sk', 'brand' => 'corporate'
), 'sfDimension::setDimension sets the dimension');

// check to make sure getting dimensions functions properly
$t->diag('getting');
$t->is_deeply($dimensions->getCurrentDimension(), array('culture' => 'sk', 'brand' => 'corporate'),
        'sfDimension::getDimension returns the correct dimension');

$t->is($dimensions->getDimensionString(), 'sk_corporate', 'sfDimension::getDimensionString returns the correct dimension string');

$t->is_deeply($dimensions->getDimensionDirs(), array('sk_corporate', 'corporate', 'sk'), 'sfDimension::getDimensionDirs returns the correct dimension directories');

