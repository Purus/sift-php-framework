<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(20, new lime_output_color());

$parser = new sfDependencyInjectionMapBuilderParser();

$parser->setString(
  "@inject Apple force:true"
);

$parser->match();

$t->isa_ok($parser->hasCommand(), 'boolean', 'hasCommand returns boolean value');
$t->is_deeply($parser->hasCommand(), true, 'hasCommand returns true');

$parser->setString('Abcbxesdgs!@d');
$parser->match();

$t->is_deeply($parser->hasCommand(), false, 'hasCommand returns false for invalid string');

$parser->setString(
        "/**
            * @inject Apple1
            * @inject Apple2 force:true
            * @inject Apple3
            */
        ");
$parser->match();

$t->isa_ok($parser->getNumberOfCommands(), 'integer', 'getNumberOfCommands() return integer');
$t->is_deeply($parser->getNumberOfCommands(), 3, 'getNumberOfCommands() return correct number');

$parser->setString("@inject");
$parser->match();

$t->is_deeply($parser->hasCommand(), true, 'hasCommand() works for simple strings');

$parser->setString("* @inject");
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options,  array (
    array(
    'dependencyName' => NULL,
    'force' => false,
    'injectWith' => NULL,
    'injectAs' => NULL,
    'newClass' => false,
  )), 'getOptions() returns something');

$t->is_deeply($options[0]['dependencyName'], null, 'getOptions() returns something');

$parser->setString("* @inject new:stdClass");
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options[0]['newClass'], 'stdClass', 'newClass is correct');

$parser->setString("* @inject Apples");
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options[0]['dependencyName'], 'Apples', 'dependencyName is correctly parsed');

$parser->setString("* @inject Apples method:setApples");
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options[0]['dependencyName'], 'Apples', 'dependencyName is correctly parsed');
$t->is($options[0]['injectWith'], 'method', 'injectWith is correctly parsed');
$t->is($options[0]['injectAs'], 'setApples', 'injectAs is correctly parsed');

$parser->setString("* @inject Apples method:setApples force:true");
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options[0]['dependencyName'], 'Apples', 'dependencyName is correctly parsed');
$t->is($options[0]['injectWith'], 'method', 'injectWith is correctly parsed');
$t->is($options[0]['force'], 'true', 'force is correctly parsed');

try {
  $parser->setString("* @inject Apples method setApples force:true");
  $parser->match();
  $parser->buildOptions();
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
$parser->match();
$parser->buildOptions();

$options = $parser->getOptions();

$t->is($options[0]['dependencyName'], 'Apple1', 'dependencyName is correctly parsed when multiple commands specified');
$t->is($options[1]['force'], 'true', 'force is correctly parsed when multiple commands specified');
$t->is($options[2]['dependencyName'], 'Apple3', 'dependencyName is correctly parsed when multiple commands specified');