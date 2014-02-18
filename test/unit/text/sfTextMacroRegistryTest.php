<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../sfServiceContainerMock.php');

$t = new lime_test(15, new lime_output_color());

class myTextMacroRegistry extends sfTextMacroRegistry {

  protected function loadMacros()
  {
  }

}

function test_macro($attributes)
{
  return sprintf('MY_WIDGET(%s)', strtoupper(http_build_query($attributes)));
}

class Foo {
  public static function macro($attributes = array(), $value = null)
  {
   return sprintf('FOO_MACRO(%s) VALUE=%s', strtoupper(http_build_query($attributes)), trim($value));
  }
}

$registry = new myTextMacroRegistry($serviceContainer, new sfConsoleLogger());
$registry->register('test', 'test_macro');

$t->is(count($registry), 1, 'register() registers the macro');
$t->is($registry->getTags(), array(
    'test'
), 'getTags() returns the registered tags');

$registry->unregister('test', 'test_macro');
$t->is(count($registry), 0, 'unregister() unregisters the macro');

$registry->register('test', 'test_macro');
$registry->clear();

$t->is(count($registry), 0, 'clear() cleared the registry');

try
{
  $registry->register('test', 'invalid_callable');
  $t->fail('register() throws an exception if the callable is not valid');
}
catch(InvalidArgumentException $e)
{
  $t->pass('register() throws an exception if the callable is not valid');
}

$content = '
This is a text.

[test id=15 display=compact]

';

$expected = '
This is a text.

MY_WIDGET(ID=15&DISPLAY=COMPACT)

';


// register again
$registry->register('test', 'test_macro');
$t->is($registry->parse($content), $expected, 'parse() parsed the content');

// register next
$registry->register('foo', array('Foo', 'macro'));

$content = '
This is a text.

[test id=15 display=compact]

[foo id=17]
Hey dude
[/foo]

';

$expected = '
This is a text.

MY_WIDGET(ID=15&DISPLAY=COMPACT)

FOO_MACRO(ID=17) VALUE=Hey dude

';

$t->diag('->parse()');

$t->is($registry->parse($content), $expected, 'parse() parsed the content with two marcos');

$expected2 = '
This is a text.

[test id=15 display=compact]

FOO_MACRO(ID=17) VALUE=Hey dude

';

$t->is($registry->parse($content, array('foo')), $expected2, 'parse() parsed the content with only one macro tag allowed');

// the original content is returned
$t->is($registry->parse($content, array('notregisteredtag')), $content, 'parse() parsed the content with only one macro tag allowed but not registered');

$t->diag('->strip()');

$stripped = '
This is a text.





';

$t->is($registry->strip($content), $stripped, 'strip() striped all tags');

$stripped = '
This is a text.



[foo id=17]
Hey dude
[/foo]

';

$t->is($registry->strip($content, array('test')), $stripped, 'strip() striped only given tags');

$t->diag('->hasTag()');
$t->is($registry->hasTag('test', $content), true, 'hasTag() works ok');
$t->is($registry->hasTag('foobar', $content), false, 'hasTag() works ok');

$t->diag('real objects');

class myMacroParser implements sfITextMacroFilter {

  protected $constructed = 0;

  public function __construct()
  {
    $this->constructed = 1;
  }

  public function filter($attributes, $value = null)
  {
    return 'myMACROPARSER-'.  http_build_query($attributes) . '&CONSTRUCTED='. $this->constructed;
  }

}

class myMacroWidget extends sfTextMacroWidget {

  protected $constructed = 0;

  public function __construct()
  {
    $this->constructed = 1;
  }

  public function getHtml($attributes, $value = null)
  {
    return 'myMACROWIDGET-'.  http_build_query($attributes) . '&CONSTRUCTED='. $this->constructed;
  }

}

$expected = '
This is a text.

myMACROPARSER-id=15&display=compact&CONSTRUCTED=1

FOO_MACRO(ID=17) VALUE=Hey dude

';

$registry->register('test', array(
  'class' => 'myMacroParser'
));

$t->is($registry->parse($content), $expected, 'parse() parsed the content with real object implements sfITextMacroFilter');

$registry->register('test', array(
  'class' => 'myMacroWidget'
));

$expected = '
This is a text.

myMACROWIDGET-id=15&display=compact&CONSTRUCTED=1

FOO_MACRO(ID=17) VALUE=Hey dude

';

$t->is($registry->parse($content), $expected, 'parse() parsed the content with real object which implements sfITextMacroWidget');