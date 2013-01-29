<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

$b->
  get('/validation')
  ->with('request')->begin()
    ->isParameter('module', 'validation')
    ->isParameter('action', 'index')
  ->end()      
  ->with('response')->begin()->
    isStatusCode(200)->
    checkElement('body h1', 'Form validation tests')->
    checkElement('body form input[name="fake"][value=""]')->
    checkElement('body form input[name="id"][value="1"]')->
    checkElement('body form input[name="article[title]"][value="title"]')->
    checkElement('body form textarea[name="article[body]"]', 'body')->
    checkElement('body ul[class="errors"] li', 0)
  ->end();


// test fill in filter
$b->
  click('submit')
  ->with('request')->begin()
    ->isParameter('module', 'validation')
    ->isParameter('action', 'index')
  ->end()        
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('body form input[name="fake"][value=""]')
    ->checkElement('body form input[name="id"][value="1"]')
    ->checkElement('body form input[name="password"][value=""]')
    ->checkElement('body form input[name="article[title]"][value="title"]')
    ->checkElement('body form textarea[name="article[body]"]', 'body')
    ->checkElement('body ul[class="errors"] li[class="fake"]')        
  ->end(); 

$b->
  click('submit', array('article' => array('title' => 'my title', 'body' => 'my body', 'password' => 'test', 'id' => 4)))
  ->with('response')->begin()             
    ->isStatusCode(200)
    ->checkElement('body form input[name="fake"][value=""]')
    ->checkElement('body form input[name="id"][value="1"]')
    ->checkElement('body form input[name="password"][value=""]')
    ->checkElement('body form input[name="article[title]"][value="my title"]')
    ->checkElement('body form textarea[name="article[body]"]', 'my body')
    ->checkElement('body ul[class="errors"] li[class="fake"]')        
  ->end()
  ->with('request')->begin()        
    ->isRequestParameter('module', 'validation')
    ->isRequestParameter('action', 'index')
  ->end();

// test group feature (with validator)
$b->test()->diag('test group feature (with validator)');
$b->
  get('/validation/group')
  ->with('request')->begin()
    ->isParameter('module', 'validation')
    ->isParameter('action', 'group')
  ->end()
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end();

$b->test()->diag('when none of the two inputs are filled, the validation passes (ok)');
$b->
  click('submit')
  ->with('response')->begin()        
  ->checkElement('body ul[class="errors"] li', false)
  ->end();      


$b->test()->diag('when both fields are filled, the validation passes (ok)');
$b->
  click('submit', array('input1' => 'foo', 'input2' => '1234567890'))        
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li', false)
  ->end();

$b->test()->diag('when both fields are filled, and input2 has incorrect data, the validation fails because of the nameValidator on input2');
$b->
  click('submit', array('input1' => 'foo', 'input2' => 'bar'))
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li[class="input1"]', false)
    ->checkElement('body ul[class="errors"] li[class="input2"]', 'nameValidator')
  ->end();

$b->test()->diag('when only the second input is filled, and with incorrect data, the validation fails because of the nameValidator on input2 and input1 is required');
$b->
  click('submit', array('input2' => 'foo'))
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li[class="input1"]', 'Required')
    ->checkElement('body ul[class="errors"] li[class="input2"]', 'nameValidator')
  ->end();

$b->test()->diag('when only the first input is filled, the validation fails because of a required on input2');
$b->
  click('submit', array('input1' => 'foo'))
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li[class="input1"]', false)
    ->checkElement('body ul[class="errors"] li[class="input2"]', 'Required')
  ->end();


// test group feature (without validator)
$b->test()->diag('test group feature (without validator)');
$b->
  get('/validation/group')
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end()
  ->with('request')->begin()        
    ->isParameter('module', 'validation')
    ->isParameter('action', 'group')
  ->end();

$b->test()->diag('when none of the two inputs are filled, the validation passes (ok)');
$b->
  click('submit')
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li', false)
  ->end();


$b->test()->diag('when both fields are filled, the validation passes (ok)');
$b->
  click('submit', array('input3' => 'foo', 'input4' => 'bar'))
    ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li', false)
  ->end();


$b->test()->diag('when only input4 is filled, the validation fails because input3 is required');
$b->
  click('submit', array('input4' => 'foo'))
  ->with('response')->begin()
    ->checkElement('body ul[class="errors"] li[class="input3"]', 'Required')
    ->checkElement('body ul[class="errors"] li[class="input4"]', false)
  ->end();


$b->test()->diag('when only input3 is filled, the validation fails because input4 is required');
$b->
  click('submit', array('input3' => 'foo'))
  ->with('response')->begin()        
    ->checkElement('body ul[class="errors"] li[class="input3"]', false)
    ->checkElement('body ul[class="errors"] li[class="input4"]', 'Required')
  ->end();


// check that /validation/index and /validation/Index both uses the index.yml validation file (see #1617)
// those tests are only relevant on machines where filesystems are case sensitive.
$b->
  post('/validation/index')
  ->with('response')->begin()         
    ->isStatusCode(200)
  ->end()
  ->with('request')->begin()         
    ->isParameter('module', 'validation')
    ->isParameter('action', 'index')
    ->isHeader('X-Validated', 'ko')
  ->end();

$b->
  post('/validation/Index')
  ->with('response')->begin()         
    ->isStatusCode(200)
  ->end()
  ->with('request')->begin()         
    ->isParameter('module', 'validation')
    ->isParameter('action', 'Index')        
    ->isHeader('X-Validated', 'ko')
  ->end();

$b->
  post('/validation/INdex')
  ->with('response')->begin() 
    ->isStatusCode(404)
  ->end();

$b->
  post('/validation/index2')
  ->with('response')->begin()  
    ->isStatusCode(200)
    ->isHeader('X-Validated', 'ko')
  ->end()      
  ->with('request')->begin()         
    ->isParameter('module', 'validation')
    ->isParameter('action', 'index2')
  ->end();

$b->
  post('/validation/Index2')
  ->with('response')->begin()
    ->isStatusCode(200)
    ->isHeader('X-Validated', 'ko')
  ->end()
  ->with('request')->begin()
    ->isParameter('module', 'validation')
    ->isParameter('action', 'Index2')
  ->end();
