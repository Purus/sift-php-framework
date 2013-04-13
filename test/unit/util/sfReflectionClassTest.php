<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfReflectionClass.class.php');

$t = new lime_test(11, new lime_output_color());

class Foo {}
class Bar {}

// reflection 2
class FooBar extends Bar {}
// reflection 3
class FoobarExtended extends Foobar implements Countable {
  public function count()
  {
    return 0;
  }
}

$reflection = new sfReflectionClass('Foo');
$reflection2 = new sfReflectionClass('Foobar');
$reflection3 = new sfReflectionClass('FoobarExtended');

$t->diag('isSubclassOf()');
$t->is($reflection->isSubclassOf('Foo'), false, '->isSubclassOf() returns false');

$t->is($reflection3->isSubclassOf('foobar'), true, '->isSubclassOf() is case insensitive');

$t->is($reflection->isSubclassOf(array('Foo', 'FooBar')), false, '->isSubclassOf() accepts array as argument');

$t->diag('isSubclassOfOrIsEqual()');

$t->is($reflection->isSubclassOfOrIsEqual('Foo'), true, '->isSubclassOfOrIsEqual() returns false');
$t->is($reflection->isSubclassOfOrIsEqual('foo'), true, '->isSubclassOfOrIsEqual() is case insensitive');

$t->is($reflection->isSubclassOfOrIsEqual($reflection2), false, '->isSubclassOfOrIsEqual() accepts reflection object');
$t->is($reflection2->isSubclassOfOrIsEqual(array($reflection, 'Bar')), true, '->isSubclassOfOrIsEqual() accepts reflection object');

$t->diag('->getParentClassNames');

$t->isa_ok($reflection->getParentClassNames(), 'array', '->getParentClassNames returns an array');
$t->is_deeply($reflection->getParentClassNames(), array(), '->getParentClassNames returns correct result');

$t->is_deeply($reflection2->getParentClassNames(), array('Bar'), '->getParentClassNames returns correct result');
$t->is_deeply($reflection3->getParentClassNames(), array('FooBar', 'Bar'), '->getParentClassNames returns correct result');
