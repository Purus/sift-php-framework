<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$closureJarPath = 'C:\windows\system32\compiler.jar';

$t = new lime_test(11, new lime_output_color());

$t->diag('Dummy');
$min = sfMinifier::factory('dummy', array());
$t->isa_ok($min, 'sfMinifierDriverDummy', 'factory() works ok');
$min->processFile(dirname(__FILE__).'/fixtures/foo.js');
$result = $min->getResults();
$t->ok(!empty($result['optimizedContent']), 'getResults() returns optimized result');
$t->ok($result['optimizedContent'] == file_get_contents(dirname(__FILE__).'/fixtures/foo.js'), 'getResults() returns the result untouched');

$t->diag('GoogleClosure');

try {

    $min = sfMinifier::factory('GoogleClosure', array(
        // required options
        'compiler_path' => $closureJarPath
    ));

    $t->isa_ok($min, 'sfMinifierDriverGoogleClosure', 'factory() works ok');
    $min->processFile(dirname(__FILE__).'/fixtures/foo.js');
    $result = $min->getResults();
    $t->ok(!empty($result['optimizedContent']), 'getResults() returns optimized result');

} catch (sfConfigurationException $e)
{
    $t->skip('Closure compiler in not installed', 2);
}

$t->diag('GoogleClosureApi');
$min = sfMinifier::factory('GoogleClosureApi');
$t->isa_ok($min, 'sfMinifierDriverGoogleClosureApi', 'factory() works ok');
$min->processFile(dirname(__FILE__).'/fixtures/foo.js');
$result = $min->getResults();
$t->ok(!empty($result['optimizedContent']), 'getResults() returns optimized result');

$t->diag('UglifyApi');
$min = sfMinifier::factory('UglifyApi');
$t->isa_ok($min, 'sfMinifierDriverUglifyApi', 'factory() works ok');
$min->processFile(dirname(__FILE__).'/fixtures/foo.js');
$result = $min->getResults();
$t->ok(!empty($result['optimizedContent']), 'getResults() returns optimized result');

$t->diag('Simple');
$min = sfMinifier::factory('JsMin');
$t->isa_ok($min, 'sfMinifierDriverJsMin', 'factory() works ok');
$min->processFile(dirname(__FILE__).'/fixtures/foo.js');
$result = $min->getResults();
$t->ok(!empty($result['optimizedContent']), 'getResults() returns optimized result');
