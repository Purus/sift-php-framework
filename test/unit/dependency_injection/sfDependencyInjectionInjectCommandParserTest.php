<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(23, new lime_output_color());

$parser = new sfDependencyInjectionInjectCommandParser();
$parser->setString('Abcbxesdgs!@d');

$t->is_deeply($parser->parse(), false, 'parse returns false for invalid string');

$parser->setString("* @inject");

try
{
  $commands = $parser->parse();
  $t->fail('parse() throws an exception if the string cannot be parsed');
}
catch(sfParseException $e)
{
  $t->pass('parse() throws an exception if the string cannot be parsed');
}

$parser->setString("* @inject new:stdClass");

$commands = $parser->parse();

$t->is($commands[0]['new_class'], 'stdClass', 'newClass is correct');

$parser->setString("* @inject Apples");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], 'Apples', 'dependencyName is correctly parsed');

$parser->setString("* @inject Apples method:setApples");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], 'Apples', 'dependencyName is correctly parsed');
$t->is($commands[0]['inject_with'], 'method', 'injectWith is correctly parsed');
$t->is($commands[0]['inject_as'], 'setApples', 'injectAs is correctly parsed');

$parser->setString("* @inject Apples method:setApples force:true");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], 'Apples', 'dependencyName is correctly parsed');
$t->is($commands[0]['inject_with'], 'method', 'injectWith is correctly parsed');
$t->is($commands[0]['inject_as'], 'setApples', 'injectAs is correctly parsed');
$t->is($commands[0]['force'], true, 'force is correctly parsed');

try {
  $parser->setString("* @inject Apples method setApples force:true");
  $parser->parse();
  $t->fail('sfParseException is thrown when the parser finds invalid option');
}
catch(sfParseException $e)
{
  $t->pass('sfParseException is thrown when the parser finds invalid option');
}

$parser->setString(
        "/**
            * @inject Apple1
            * @inject Apple2 force:true
            * @inject Apple3
            */
        ");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], 'Apple1', 'dependencyName is correctly parsed when multiple commands specified');
$t->is($commands[1]['force'], true, 'force is correctly parsed when multiple commands specified');
$t->is($commands[2]['dependency_name'], 'Apple3', 'dependencyName is correctly parsed when multiple commands specified');

$parser->setString(
        "/**
            * @inject Apple1 required:true
            * @inject Apple3 required:false
            */
        ");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], 'Apple1', 'dependencyName is correctly parsed when multiple commands specified');
$t->is($commands[0]['required'], true, 'required is correctly parsed when multiple commands specified');
$t->is($commands[1]['dependency_name'], 'Apple3', 'dependencyName is correctly parsed when multiple commands specified');
$t->is($commands[1]['required'], false, 'required is correctly parsed when multiple commands specified');

$parser->setString(
"/**
 * @inject new:Something method:setForce force:true
 * @inject apple method:setApple
 */
");

$commands = $parser->parse();

$t->is($commands[0]['dependency_name'], null, 'dependencyName is correctly parsed');
$t->is($commands[0]['inject_with'], 'method', 'inject with is correctly parsed');
$t->is($commands[0]['inject_as'], 'setForce', 'inject as is correctly parsed');
$t->is($commands[0]['force'], true, 'force is correctly parsed');
