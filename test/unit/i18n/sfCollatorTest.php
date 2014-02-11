<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10, new lime_output_color());

$collator = new sfCollator('cs_CZ');

$t->isa_ok($collator->compare('a', 'b'), 'integer', 'compare returns integer value');

$tests = array(
  'hola',
  'česká republika',
  'čína',
  'žížala',
  'buráky',
  'chomáč',
  'chroust',
  'hák',
);

$t->isa_ok($collator->asort($tests), 'boolean', 'asort returns boolean value');
$t->is($collator->getCulture(), 'cs_CZ', '->getCulture() returns current culture');

if(class_exists('Collator'))
{
  try
  {
    $collator = new sfCollator('fooobar');
    $t->fail('sfCollator throws exception if culture is invalid');
  }
  catch(RuntimeException $e)
  {
    $t->pass('sfCollator throws exception if culture is invalid');
  }

  // czech specific sorting, CH is places after H
  $t->is_deeply(array_values($tests), array(
    'buráky',
    'česká republika',
    'čína',
    'hák',
    'hola',
    'chomáč',
    'chroust',
    'žížala',
  ), 'asort returns array sorted for czech locale');

  $t->is($collator->getStrength(), 2, '->getStrength() return strength');

}
else
{
  $t->skip('Intl module is not installed', 3);
}

class myCollator extends sfCollator
{

  public function __construct($culture)
  {
    parent::__construct($culture);
    $this->collator = null;
  }
}

//
$t->diag('without collator');

$tests = array(
  'hola',
  'česká republika',
  'čína',
  'žížala',
  'buráky',
  'chomáč',
  'chroust',
  'hák',
);

$collator = new myCollator('cs_CZ');

$collator->asort($tests);

$t->is($collator->compare('chroust', 'čína'), 1, 'compare works ok for czech collation');

// czech specific sorting, CH is places after H
$t->is_deeply(array_values($tests), array(
  'buráky',
  'česká republika',
  'čína',
  'hák',
  'hola',
  'chomáč',
  'chroust',
  'žížala',
), 'asort returns array sorted for czech locale');

$collator = new myCollator('cs_CZ');

$tests = array(
  'hola',
  'česká republika',
  'čína',
  'žížala',
  'buráky',
  'chomáč',
  'chroust',
  'hák',
);

$collator->asort($tests);

$t->is($collator->compare('chroust', 'čína'), 1, 'compare works ok for czech collation');

// czech specific sorting, CH is places after H
$t->is_deeply(array_values($tests), array(
  'buráky',
  'česká republika',
  'čína',
  'hák',
  'hola',
  'chomáč',
  'chroust',
  'žížala',
), 'asort returns array sorted for czech locale');

