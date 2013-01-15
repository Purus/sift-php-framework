<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10, new lime_output_color());

$query = '"zkouška rozhlasu" OR test AND (test OR dva) "moje váha"';

$parser = new sfSearchQueryParser(new sfSearchQueryLexer());
$result = $parser->parse($query);

$t->isa_ok($result, 'sfSearchQueryExpression', 'parse() returns sfSearchQueryExpression');

// tests for expressions

$t->isa_ok($result->getPhrases(), 'array', 'getPhrases() returns an array');

$phrase1 = new sfSearchQueryPhrase("zkouška rozhlasu", sfSearchQueryPhrase::MODE_DEFAULT);
$phrase2 = new sfSearchQueryPhrase("test", sfSearchQueryPhrase::MODE_OR);
$phrase3 = new sfSearchQueryPhrase("moje váha", sfSearchQueryPhrase::MODE_AND);

$t->is($result->getPhrases(), array($phrase1, $phrase2, $phrase3), 'getPhrases() returns an array');

$t->isa_ok($result->getSubExpressions(), 'array', 'getSubExpressions() returns an array');

$phrase1 = new sfSearchQueryPhrase("test", sfSearchQueryPhrase::MODE_DEFAULT);
$phrase2 = new sfSearchQueryPhrase("dva", sfSearchQueryPhrase::MODE_OR);

foreach($result->getSubExpressions() as $sub)
{
  $t->isa_ok($sub, 'sfSearchQueryExpression', 'getSubExpressions() returns an array of sfSearchQueryExpression objects');
  
  $t->is($sub->getPhrases(), array($phrase1, $phrase2), 'getPhrases() returns correct result for subexpression.');  
}

$t->diag('MySQL');
// mysql
$builder = new sfSearchQueryBuilderMysqlFulltext($result);
$t->isa_ok($builder->getResult(), 'string', 'getResult() returns string');
$t->is($builder->getResult(), '"zkouška rozhlasu" test +"moje váha" +(test dva)', 'getResult() returns correct query string');

$t->diag('Pgsql');
// mysql
$builder = new sfSearchQueryBuilderPgsqlFulltext($result);
$t->isa_ok($builder->getResult(), 'string', 'getResult() returns string');
$t->is($builder->getResult(), '\'zkouška rozhlasu\' | test & \'moje váha\' & (test | dva)', 'getResult() returns correct query string');

