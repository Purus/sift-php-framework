<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/lib/mySearchSourceTest.class.php');

$t = new lime_test(9, new lime_output_color());

$t->diag('mySearch');
$t->diag('------------------');

// search 
$source = new mySearchSourceTest();
$results = $source->find('test')->getResults();

$t->isa_ok($results, 'sfSearchResultCollection', 'search returns collection object');

$t->isa_ok($results->count(), 'integer', 'count() returns number of results');

$t->is($results->count(), 3, 'count() returns correct number of results');

// result tests

$t->isa_ok($results[0], 'mySearchResult', 'Result is valid searchResult object');
$t->isa_ok($results[0]->title, 'string', 'Result getTitle() returns string');
$t->is($results[2]->title, 'TEstík 3', 'Result getTitle() returns correct string');

$t->is($results[2]->getTitle(), 'TEstík 3', 'Method is callable and returns correct result');

// merging collections!
$source2 = new mySearchSourceTest();
$source2->find2('test');

$source2->getResults()->merge($source->getResults());

$t->is($source2->getResults()->count(), 5, 'count() returns correct number of results after collection merge');

// serialize
$t->isa_ok(serialize($source), 'string', 'serialize interface works correctly');


