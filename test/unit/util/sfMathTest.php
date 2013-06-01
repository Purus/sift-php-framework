<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(45, new lime_output_color());

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

$t->diag('clean');
$t->is_deeply(sfMath::clean('1.00000'), '1', 'clean() works ok');
$t->is_deeply(sfMath::clean('1.000001'), '1.000001', 'clean() works ok');
$t->is_deeply(sfMath::clean('0001.001000'), '1.001', 'clean() works ok');
$t->is_deeply(sfMath::clean('1000'), '1000', 'clean() works ok');

$t->diag('round');

$t->is(sfMath::round('3'), round('3'), 'round() works ok');
$t->is(sfMath::round('3.8'), round('4'), 'round() works ok');
$t->is(sfMath::round('3.86', 1), round('3.9', 1), 'round() works ok');

$t->is(sfMath::round('1.95583', 2), round('1.96', 2), 'round() works ok');
$t->is(sfMath::round('5.045', 2), round('5.05', 2), 'round() works ok');
$t->is(sfMath::round('5.055', 2), round('5.06', 2), 'round() works ok');
$t->is(sfMath::round('9.999', 2), round('10.00', 2), 'round() works ok');

$t->diag('ceil');

$t->is(sfMath::ceil('4'), ceil('4'), 'ceil() works ok');
$t->is(sfMath::ceil('4.3'), ceil('4'), 'ceil() works ok');
$t->is(sfMath::ceil('9.999'), ceil('9.999'), 'ceil() works ok');
$t->is(sfMath::ceil('-3.14'), ceil('-3.14'), 'ceil() works ok');

$t->diag('floor');

$t->is(sfMath::floor('4'), floor('4'), 'floor() works ok');
$t->is(sfMath::floor('4.3'), floor('4.3'), 'floor() works ok');
$t->is(sfMath::floor('9.999'), floor('9.999'), 'floor() works ok');
$t->is(sfMath::floor('-3.14'), floor('-3.14'), 'floor() works ok');

// real worlds examples
$t->diag('real world examples');

$calculation = sfMath::round(sfMath::multiply(sfMath::divide('10900', '1.21'), '1.21'), 2);
$t->is_deeply($calculation, '10900.00', 'multiply() works ok');
