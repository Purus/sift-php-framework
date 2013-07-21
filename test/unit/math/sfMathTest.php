<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(196, new lime_output_color());

$calculation = sfMath::add('0.1', '0.2', 1);
$t->is_deeply($calculation, '0.3', 'add() works ok with precision is specified');

sfMath::setScale(4);

$t->diag('add');

$calculation = sfMath::add('0.10001', '0.2');
$t->is_deeply($calculation, '0.3000', 'add() works ok without scale parameter');

$calculation = sfMath::add('0.10001', '0.2', 5);
$t->is_deeply($calculation, '0.30001', 'add() works ok');

$t->diag('compare');

$calculation = sfMath::compare('0.33333', '0.33334');
// default is 4 scale, so the numbers are equal
$t->is_deeply($calculation, 0, 'compare() of numbers works ok without scale parameter');

$calculation = sfMath::compare('0.33333', '0.33334', 5);
$t->is_deeply($calculation, -1, 'compare() of numbers works ok');

$calculation = sfMath::compare('0.33334', '0.33333', 5);
$t->is_deeply($calculation, 1, 'compare() of numbers works ok');

$t->is_deeply(sfMath::compare('0.6', '0.5', 3), 1, 'compare() of numbers works ok');
$t->is_deeply(sfMath::compare('0.5', '0.6', 3), -1, 'compare() of numbers works ok');
$t->is_deeply(sfMath::compare('0.5', '0.5', 3), 0, 'compare() of numbers works ok');

$t->diag('divide');

$calculation = sfMath::divide('0.3', '0.35');
$t->is_deeply($calculation, '0.8571', 'divide() of numbers works ok without scale parameter');

$calculation = sfMath::divide('0.3', '0.35', 5);
$t->is_deeply($calculation, '0.85714', 'divide() of numbers works ok');

$t->diag('modulus');

$calculation = sfMath::modulus('2', '4');
$t->is_deeply($calculation, '2', 'modulus() works ok without scale parameter');

$calculation = sfMath::modulus('2', '4');
$t->is_deeply($calculation, '2', 'modulus() works ok', 4);

$t->diag('multiply');

$calculation = sfMath::multiply('2', '8.123');
$t->is_deeply($calculation, '16.246', 'multiply() works ok without scale parameter');

$calculation = sfMath::multiply('2', '8.123', 4);
$t->is_deeply($calculation, '16.246', 'multiply() works ok');

$t->diag('power');

$calculation = sfMath::power('2.1', '8');
$t->is_deeply($calculation, '378.2285', 'power() works ok without scale parameter');

$calculation = sfMath::power('2.1', '8', 10);
$t->is_deeply($calculation, '378.22859361', 'power() works ok');

$t->diag('powerModulus');

$calculation = sfMath::powerModulus('2', '4', '4');
$t->is_deeply($calculation, '0.0000', 'powerModulus() works ok without scale parameter');

$calculation = sfMath::powerModulus('2', '4', '4', 10);
$t->is_deeply($calculation, '0.0000000000', 'powerModulus() works ok');

$t->diag('substract');

$calculation = sfMath::substract('2', '4.0001');
$t->is_deeply($calculation, '-2.0001', 'substract() works ok without scale parameter');

$calculation = sfMath::substract('2', '4.0001', 5);
$t->is_deeply($calculation, '-2.00010', 'substract() works ok');

$t->diag('sqrt');

$calculation = sfMath::sqrt('2');
$t->is_deeply($calculation, '1.4142', 'sqrt() works ok without scale parameter');

$calculation = sfMath::sqrt('2', 4);
$t->is_deeply($calculation, '1.4142', 'sqrt() works ok');

$t->diag('factorial');

$calculation = sfMath::factorial(0);
$t->is_deeply($calculation, '1', 'factorial() works ok');

$calculation = sfMath::factorial(1);
$t->is_deeply($calculation, '1', 'factorial() works ok');

$calculation = sfMath::factorial(5);
$t->is_deeply($calculation, '120', 'factorial() works ok');

$calculation = sfMath::factorial(8);
$t->is_deeply($calculation, '40320', 'factorial() works ok');

$calculation = sfMath::factorial(16);
$t->is_deeply($calculation, '20922789888000', 'factorial() works ok');

$t->diag('->isOdd() ->isEven');

$t->is_deeply(sfMath::isOdd('2.00000'), false, 'isOdd() works ok for "integer" values');
$t->is_deeply(sfMath::isEven('2.00000'), true, 'isEven() works ok for "integer" values');
////
$t->is_deeply(sfMath::isOdd('3.00000'), true, 'isOdd() works ok for "integer" values');
$t->is_deeply(sfMath::isOdd('-3'), true, 'isOdd() works ok for negative values');
$t->is_deeply(sfMath::isEven('-3'), false, 'isOdd() works ok for negative values');
$t->is_deeply(sfMath::isEven('3.00000'), false, 'isEven() works ok for "integer" values');
//
$t->is_deeply(sfMath::isEven('2.1'), true, 'isEven() works ok for "float" values.');
$t->is_deeply(sfMath::isOdd('3.1'), true, 'isEven() works ok for "float" values.');
//
$t->diag('abs()');

$t->is_deeply(sfMath::abs('3.1'), '3.1', 'abs() works ok.');
$t->is_deeply(sfMath::abs('-3.1'), '3.1', 'abs() works ok.');
$t->is_deeply(sfMath::abs('-0.1'), '0.1', 'abs() works ok.');
//
$t->diag('sign()');

$t->is_deeply(sfMath::sign('3.1'), '1', 'sign() works ok.');
$t->is_deeply(sfMath::sign('-3.1'), '-1', 'sign() works ok.');
$t->is_deeply(sfMath::sign('-0.1'), '-1', 'sign() works ok.');
$t->is_deeply(sfMath::sign('-0.000000001'), '-1', 'sign() works ok.');
$t->is_deeply(sfMath::sign('0'), '1', 'sign() works ok.');
//
sfMath::setScale(0);
//
$t->diag('clean');
$t->is_deeply(sfMath::clean('1.00000'), '1', 'clean() works ok');
$t->is_deeply(sfMath::clean('1.000001'), '1.000001', 'clean() works ok');
$t->is_deeply(sfMath::clean('0001.001000'), '1.001', 'clean() works ok');
$t->is_deeply(sfMath::clean('1000'), '1000', 'clean() works ok');
$t->is_deeply(sfMath::clean('0.5000'), '0.5', 'clean() works ok');
$t->is_deeply(sfMath::clean('0.00'), '0.0', 'clean() works ok for already cleaned value');
//
$t->diag('round');
$t->is_deeply(sfMath::round('3'), '3', 'round() works ok');
$t->is_deeply(sfMath::round('3.8'), '4', 'round() works ok');
$t->is_deeply(sfMath::round('3.86', 1), '3.9', 'round() works ok');
//
$t->is_deeply(sfMath::round('1.95583', 2), '1.96', 'round() works ok');
$t->is_deeply(sfMath::round('5.045', 2), '5.05', 'round() works ok');
$t->is_deeply(sfMath::round('5.055', 2), '5.06', 'round() works ok');
$t->is_deeply(sfMath::round('9.999', 2), '10.00', 'round() works ok');

$t->diag('ceil');
//
$t->is_deeply(sfMath::ceil('4'), '4', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('4.3'), '5', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('9.999'), '10', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('-3.14'), '-3', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('1.0'), '1', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('1.6'), '2', 'ceil() works ok');
$t->is_deeply(sfMath::ceil('-1.6'), '-1', 'ceil() works ok');

$t->diag('ceil with precision');
$t->is_deeply(sfMath::ceil('-5.5534', 3), '-5.553', 'ceil() works ok with precision');
$t->is_deeply(sfMath::ceil('5.5534', 3), '5.554', 'ceil() works ok with precision');
$t->is_deeply(sfMath::ceil('-10.549', 2), '-10.54', 'ceil() works ok with precision');
$t->is_deeply(sfMath::ceil('10.541', 2), '10.55', 'ceil() works ok with precision');
$t->is_deeply(sfMath::ceil('10.5481', 3), '10.549', 'ceil() works ok with precision');

$t->is_deeply(sfMath::ceil('3.14555', 4), '3.1456', 'ceil() works ok with precision');
$t->is_deeply(sfMath::ceil('0.00455', 4), '0.0046', 'ceil() works ok with precision');
//
$t->diag('floor');
$t->is_deeply(sfMath::floor('4'), '4', 'floor() works ok');
$t->is_deeply(sfMath::floor('-1.6'), '-2', 'floor() works ok for negative number');
$t->is_deeply(sfMath::floor('-10.548', 2), '-10.55', 'floor() works ok for negative number with precision');
$t->is_deeply(sfMath::floor('-3.14555', 4), '-3.1456', 'floor() works ok for negative number with precision');

$t->is_deeply(sfMath::floor('4.3'), '4', 'floor() works ok');
$t->is_deeply(sfMath::floor('9.999'), '9', 'floor() works ok');
$t->is_deeply(sfMath::floor('9.999', 2), '9.99', 'floor() works ok');
$t->is_deeply(sfMath::floor('-3.14'), '-4', 'floor() works ok');
//
$t->diag('isNegative');

$t->isa_ok(sfMath::isNegative('4'), 'boolean', 'isNegative() returns boolean');
$t->is_deeply(sfMath::isNegative('4'), false, 'isNegative() returns false for positive number');
$t->is_deeply(sfMath::isNegative('-4'), true, 'isNegative() returns true for positive number');
$t->is_deeply(sfMath::isNegative('0'), false, 'isNegative() returns false for zero');

// real worlds examples
$t->diag('real world examples');
$calculation = sfMath::round(sfMath::multiply(sfMath::divide('10900', '1.21', 10), '1.21', 10));
$t->is_deeply($calculation, '10900', 'multiply() works ok');

class myValue {

  public $amount;

  public function __construct($amount)
  {
    $this->amount = $amount;
  }

  public function __toString()
  {
    return (string)$this->amount;
  }

}

$t->diag('working with objects');

$value = new myValue(100);
$calculation = sfMath::multiply($value, '2');

$t->is_deeply($calculation, '200', 'multiply() works ok for objects');

$t->diag('Rounding methods' . "\n");

$t->diag('Round up ->roundUp()');

$tests = array(
  '5.5' => '6',
  '2.5' => '3',
  '1.6' => '2',
  '1.1' => '2',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-2',
  '-1.6' => '-2',
  '-2.5' => '-3',
  '-5.5' => '-6',
  '-5.5555' => array(
      3, '-5.556'
  ),
  '-5.5534' => array(
      3, '-5.554'
  ),
  '5.5534' => array(
      3, '5.554'
  )
);

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundUp($n, $precision), $expected, sprintf('->roundUp() works for "%s" with %s precision', $n, $precision));
}

$tests = array(
  '5.55' => '5.6',
  '2.55' => '2.6',
);

foreach($tests as $n => $expected)
{
  $t->is_deeply(sfMath::roundUp($n, 1),
      $expected, sprintf('->roundUp() works for "%s" with 1 precision', $n));
}
//
$tests = array(
  '5.5' => '5',
  '2.5' => '2',
  '1.6' => '1',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-1',
  '-1.6' => '-1',
  '-2.5' => '-2',
  '-5.5' => '-5',
  '-5.5555' => array(
      3, '-5.555'
  ),
  '-5.5534' => array(
      3, '-5.553'
  ),
  '5.5534' => array(
      3, '5.553'
  )
);

$t->diag('->roundDown()');

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundDown($n, $precision), $expected, sprintf('->roundDown() works for "%s" with %s precision', $n, $precision));
}

$tests = array(
  '5.5' => '5',
  '2.5' => '2',
  '1.6' => '1',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-2',
  '-1.6' => '-2',
  '-2.5' => '-3',
  '-5.5' => '-6',
);

$t->diag('->floor()');

foreach($tests as $n => $expected)
{
  $t->is_deeply(sfMath::floor($n), $expected, sprintf('->floor() works for "%s"', $n));
}

$t->diag('Round half up ->roundHalfUp()');

$tests = array(
  '5.5' => '6',
  '2.5' => '3',
  '1.6' => '2',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-1',
  '-1.6' => '-2',
  '-2.5' => '-3',
  '-5.5' => '-6',
  '-5.5555' => array(
      3, '-5.556'
  ),
  '-5.5534' => array(
      3, '-5.553'
  ),
  '5.5534' => array(
      3, '5.553'
  )
);

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundHalfUp($n, $precision), $expected,
      sprintf('->roundHalfUp() works for "%s" with %s precision', $n, $precision));
}

 $t->is_deeply(sfMath::roundHalfUp('5.123456', 5), '5.12346', sprintf('->roundHalfUp() with 5 precision works for "%s"', $n));

$t->diag('Round half down ->roundHalfDown()');

$tests = array(
  '5.5' => '5',
  '2.5' => '2',
  '1.6' => '2',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-1',
  '-1.6' => '-2',
  '-2.5' => '-2',
  '-5.5' => '-5',
  '-5.5555' => array(
      3, '-5.555'
  ),
  '-5.5534' => array(
      3, '-5.553'
  ),
  '5.5534' => array(
      3, '5.553'
  ),
  // precision is greater than the decimal part
  '5.5534' => array(
      5, '5.5534'
  ),
  '23.125' => array(
      2, '23.12'
  ),
  '23.126' => array(
      2, '23.13'
  )
);

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundHalfDown($n, $precision), $expected . '', sprintf('->roundHalfDown() works for "%s" with %s precision', $n, $precision));
}

$tests = array(
  '5.55' => '5.5',
  '1.54' => '1.5',
  '-1.55' => '-1.5',
  '-1.54' => '-1.5'
);

foreach($tests as $n => $expected)
{
  $t->is_deeply(sfMath::roundHalfDown($n, 1), $expected, sprintf('->roundHalfDown() with 1 precision works for "%s"', $n));
}

$t->is_deeply(sfMath::roundHalfDown('5.123456', 5), '5.12346', sprintf('->roundHalfDown() with 5 precision works for "%s"', $n));

//
$t->diag('Round half up ->roundHalfEven()');

$tests = array(
  '5.5' => '6',
  '2.5' => '2',
  '1.6' => '2',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-1',
  '-1.6' => '-2',
  '-2.5' => '-2',
  '-5.5' => '-6',
  '23.5' => '24',
  '24.5' => '24',
  '-23.5' => '-24',
  '-24.5' => '-24',

  '3.1446' => array(
      3, '3.145'
  ),
  '3.1456' => array(
      3, '3.146'
  ),
  '4.13465' => array(
      3, '4.135'
  ),
  '3.11456773365' => array(
      10, '3.1145677336'
  ),
  '1.275' => array(
      2, '1.28'
  )
);

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundHalfEven($n, $precision), $expected, sprintf('->roundHalfEven() works for "%s" with precision "%s"', $n, $precision));
}


$t->diag('Round half up ->roundHalfOdd()');

$tests = array(
  '5.5' => '5',
  '2.5' => '3',
  '1.6' => '2',
  '1.1' => '1',
  '1.0' => '1',
  '-1.0' => '-1',
  '-1.1' => '-1',
  '-1.6' => '-2',
  '-2.5' => '-3',
  '-5.5' => '-5',

  '3.1446' => array(
      3, '3.145'
  ),
  '3.1456' => array(
      3, '3.146'
  ),
  '4.13465' => array(
      3, '4.135'
  ),
  '3.11456773365' => array(
      10, '3.1145677337'
  ),
  '1.275' => array(
      2, '1.27'
  ),
  '13.00' => array(
    10, '13'
  )
);

foreach($tests as $n => $expected)
{
  $precision = 0;
  if(is_array($expected))
  {
    $precision = $expected[0];
    $expected = $expected[1];
  }

  $t->is_deeply(sfMath::roundHalfOdd($n, $precision), $expected, sprintf('->roundHalfOdd() works for "%s"', $n));
}

$t->diag('Round to nearest ->roundToNearest()');

$tests = array(
  '3.1446' => array(
      2, '5', '3.15' // precision, nearest, expected value
  ),
  '10.15' => array(
      2, '10', '10.2' // precision, nearest, expected value
  ),
  '100.15' => array(
      2, '25', '100.25' // precision, nearest, expected value
  ),
);

foreach($tests as $n => $expected)
{
  $precision = $expected[0];
  $nearest = $expected[1];
  $expected = $expected[2];

  $t->is_deeply(sfMath::roundToNearest($n, $nearest, $precision), $expected, sprintf('->roundToNearest() works for "%s"', $n));
}

try {
  sfMath::roundToNearest('3.5', 0);
  $t->fail('Exception is thrown when nearest is zero or empty');
}
catch(Exception $e)
{
  $t->pass('Exception is thrown when nearest is zero or empty');
}
