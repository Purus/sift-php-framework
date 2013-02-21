<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(23, new lime_output_color());

class myBibleReader extends sfConfigurable {

  protected $customProperty = 'I love Jesus Christ!';

  protected $defaultOptions = array(
      'book'    => 'Genesis',
      'chapter' => 1,
      'verse'   => 1
  );

}

class myFastBibleReader extends myBibleReader {

  protected $defaultOptions = array(
    'chapter' => 2
  );

}

class myPowerfullBibleReader extends myFastBibleReader {

  protected $defaultOptions = array(
    'power'  => PHP_INT_MAX
  );

}

class Foobar extends sfConfigurable {}

class myBrokenBibleReader extends Foobar {

  protected $customProperty = 'I love Jesus Christ!';

  protected $defaultOptions = array(
      'book'    => 'Genesis',
      'chapter' => 1,
      'verse'   => 1
  );

}



$reader = new myBibleReader();

$t->isa_ok($reader->getOptions(), 'array', 'getOptions() returns an array of options');

$t->is($reader->getOptions(), array(
      'book'    => 'Genesis',
      'chapter' => 1,
      'verse'   => 1
), 'getOptions() returns assigned options');


$t->is($reader->getOption('book'), 'Genesis', 'getOption() returns correct result');
$t->is($reader->hasOption('zoom'), false, 'hasOption() returns correct result');

$t->isa_ok($reader->setOption('zoom', 7), 'myBibleReader', 'setOption() returns object for chainability');

$t->is($reader->hasOption('zoom'), true, 'hasOption() returns correct result');
$t->is($reader->getOption('zoom'), 7, 'getOption() returns correct result');

$reader->setOption('prophet', array('Moses', 'Samuel'));

$t->is($reader->getOption('prophet'), array('Moses', 'Samuel'), 'getOption() returns correct result for array value');

$reader2 = new myFastBibleReader();

$t->is($reader2->getOptions(), array(
      'book'    => 'Genesis',
      'chapter' => 2,
      'verse'   => 1
), 'getOptions() returns assigned options');

$reader3 = new myPowerfullBibleReader();

$t->is($reader3->getOptions(), array(
      'book'    => 'Genesis',
      'chapter' => 2,
      'verse'   => 1,
      'power' => PHP_INT_MAX
), 'getOptions() returns assigned options');

$reader4 = new myBrokenBibleReader(array(
   'chapter' => 14
));

$t->is($reader4->getOptions(), array(
      'book'    => 'Genesis',
      'chapter' => 14,
      'verse'   => 1
), 'getOptions() returns assigned options');


$t->is($reader4->getOption('foobar', 'value'), 'value', 'getOption() returns default value');


class myBibleClass extends myBibleReader {

  protected $requiredOptions = array(
      'time'
  );

}

$t->diag('required options');

try
{
  $reader5 = new myBibleClass(array(
   'chapter' => 14
  ));
  $t->fail('RuntimeException is thrown when required option is missing');
}
catch (RuntimeException $e)
{
  $t->pass('RuntimeException is thrown when required option is missing');
}

$reader5 = new myBibleClass(array(
  'time' => 'now'
));

$t->is($reader5->getOptions(), array(
      'book'    => 'Genesis',
      'chapter' => 1,
      'verse'   => 1,
      'time'    => 'now'
), 'getOptions() returns assigned options');

//
try
{
  $reader5 = new myBibleClass();
  $t->fail('RuntimeException is thrown when required option is missing');
}
catch (RuntimeException $e)
{
  $t->pass('RuntimeException is thrown when required option is missing');
}


class myValidBibleReader extends myBibleReader {

  protected $validOptions = array('book', 'chapter', 'verse');

}

class myValidBibleReader2 extends myValidBibleReader {
  protected $validOptions = array('power');
}

try
{
  $reader6 = new myValidBibleReader(array(
      'book' => 1,
      'power' => 4
  ));
  $t->fail('RuntimeException is thrown when invalid option is given');
}
catch (RuntimeException $e)
{
  $t->pass('RuntimeException is thrown when invalid option is given');
}


try
{
  $reader7 = new myValidBibleReader2(array(
      'book' => 1,
      'power' => 4
  ));

  $t->pass('RuntimeException is not thrown (valid option is merged from its parent class)');
}
catch (RuntimeException $e)
{
  $t->fail('RuntimeException is not thrown (valid option is merged from its parent class)');

}

$t->diag('dot notation syntax');

class sfDotNotation extends sfConfigurable {

  protected $defaultOptions = array(
      'foo' => 'bar',
      'deeply' => array(
          'nested' => array(
              'hello' => '',
              'foo' => 'bar'
          )
      ),
      'nested' => array(
          'foo' => 'bar',
          'deeply' => array(
              'foo' => array(
                'bar' => 'baz'
              ),
              'bar',
              '2' => '',
          )
      ),
      'very.nested.deeply.foo.bar' => 'baz'
  );

}

$dotNotation = new sfDotNotation();

$t->is($dotNotation->getOption('foo'), 'bar', 'getOption() works ok for simple option name');
$t->is($dotNotation->getOption('nested.foo'), 'bar', 'getOption() works ok for dot notation name "nested.foo"');
$t->is($dotNotation->getOption('deeply.nested.foo'), 'bar', 'getOption() works ok for dot notation name');
$t->is($dotNotation->getOption('nested.deeply.foo.bar'), 'baz', 'getOption() works ok for dot notation name "nested.deeply.foo.bar"');

$t->is($dotNotation->getOption('very.nested.deeply.foo.bar'), 'baz', 'getOption() works ok for dot notation but is simple option');

$dotNotation = new sfDotNotation();
$dotNotation->addOptions(array('nested.foo' => 'test'));

$t->is($dotNotation->getOptions(), array(
      'foo' => 'bar',
      'deeply' => array(
          'nested' => array(
              'hello' => '',
              'foo' => 'bar'
          )
      ),
      'nested' => array(
          'foo' => 'test',
          'deeply' => array(
              'foo' => array(
                'bar' => 'baz'
              ),
              'bar',
              '2' => '',
          )
      ),
      'very.nested.deeply.foo.bar' => 'baz'
  ), 'setOptions() work for dot notation');
