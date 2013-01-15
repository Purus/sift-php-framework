<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(66, new lime_output_color());

$variables = array(
  '1', true, array('žížala<strong>', 2)
);

$results = array(
  '1', 'true', 'Array'
);

$t->diag('->toRawString()');

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toRawString($variable), 'string', '->toRawString() converts all type of variables to raw string');
  $t->is(sfInputFilters::toRawString($variable), $results[$k], '->toRawString() converts all type of variables to raw string');
}

$t->diag('->toInt()');

$results = array(
  1, 1, 1
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toInt($variable), 'integer', '->toInt() converts all type of variables to integer');
  $t->is(sfInputFilters::toInt($variable), $results[$k], '->toInt() converts all type of variables to integer');
}

$t->diag('->toBool()');

$results = array(
  true, true, true
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toBool($variable), 'boolean', '->toBool() returns boolean');
  $t->is_deeply(sfInputFilters::toBool($variable), $results[$k], '->toBool() converts all type of variables to boolean');
}

$t->diag('->toFloat()');

$results = array(
  1.0, 1.0, 1.0
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toFloat($variable), 'double', '->toFloat() returns float');
  $t->is_deeply(sfInputFilters::toFloat($variable), $results[$k], '->toFloat() converts all type of variables to float');
}

$t->diag('->toArray()');

$results = array(
  array('1'), array(true), array('žížala<strong>', 2)
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toArray($variable), 'array', '->toArray() returns array');
  $t->is_deeply(sfInputFilters::toArray($variable), $results[$k], '->toArray() converts all type of variables to array');
}

$t->diag('->toIntArray()');

$results = array(
  array(1), array(1), array(0, 2)
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toIntArray($variable), 'array', '->toIntArray() returns array');
  $t->is_deeply(sfInputFilters::toIntArray($variable), $results[$k], '->toIntArray() converts all elements in array to integer');
}

$t->diag('->toRawStringArray()');

$results = array(
  array('1'), array('true'), array('žížala<strong>', '2')
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toRawStringArray($variable), 'array', '->toRawStringArray() returns array');
  $t->is_deeply(sfInputFilters::toRawStringArray($variable), $results[$k], '->toRawStringArray() converts all elements in array to string');
}

$t->diag('->toStringArray()');

$results = array(
  array('1'), array('true'), array('žížala&lt;strong&gt;', '2')
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toStringArray($variable), 'array', '->toStringArray() returns array');
  $t->is_deeply(sfInputFilters::toStringArray($variable), $results[$k], '->toStringArray() converts all elements in array to string');
}

$t->diag('->toFloatArray()');

$results = array(
  array(1.0), array(1.0), array(0.0, 2.0)
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toFloatArray($variable), 'array', '->toFloatArray() returns array');
  $t->is_deeply(sfInputFilters::toFloatArray($variable), $results[$k], '->toFloatArray() converts all elements in array to float');
}

$t->diag('->toBoolArray()');

$results = array(
  array(true), array(true), array(true, true)
);

foreach($variables as $k => $variable)
{
  $t->isa_ok(sfInputFilters::toBoolArray($variable), 'array', '->toBoolArray() returns array');
  $t->is_deeply(sfInputFilters::toBoolArray($variable), $results[$k], '->toBoolArray() converts all elements in array to boolean');
}

$t->diag('->filterVar()');

// clean
$variable = array(
  'Tvoje hodnota & moje hodnota = 10'
);

$t->isa_ok(sfInputFilters::filterVar($variable, array(sfInputFilters::TO_STRING)), 'array', '->filter() method works ok');
$t->is_deeply(sfInputFilters::filterVar($variable, array(sfInputFilters::TO_STRING)), array(
  'Tvoje hodnota &amp; moje hodnota = 10'), '->filter() method works ok');


$t->diag('->stripWhitespace()');

$t->is_deeply(sfInputFilters::stripWhitespace('This is   můj      text' . "\n   \n"), 'This is můj text ', '->stripWhitespace() method works ok');

$t->diag('->stripImages()');
$t->is(sfInputFilters::stripImages('<a href="#"><img src=images/foo.jpg alt="fo"/></a> Image?'), '<a href="#">fo</a><br /> Image?', '->stripImages() method works ok');

$t->diag('->stripScripts()');
$t->is(sfInputFilters::stripScripts('<script type="text/javascript"><!-- alert(document.cookie); //--></script>hacked!'), 'hacked!', '->stripScripts() method works ok');

$t->diag('->stripTags()');
$t->is(sfInputFilters::stripTags('<script type="text/javascript"><!-- alert(document.cookie); //--></script><strong>hacked!</strong>'), '<!-- alert(document.cookie); //-->hacked!', '->stripTags() method works ok');
