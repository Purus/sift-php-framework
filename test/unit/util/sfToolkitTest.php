<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(113, new lime_output_color());

// ::stringToArray()
$t->diag('::stringToArray()');
$tests = array(
  'foo=bar' => array('foo' => 'bar'),
  'foo1=bar1 foo=bar   ' => array('foo1' => 'bar1', 'foo' => 'bar'),
  'foo1="bar1 foo1"' => array('foo1' => 'bar1 foo1'),
  'foo1="bar1 foo1" foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1 = "bar1=foo1" foo=bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1= \'bar1 foo1\'    foo  =     bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1=\'bar1=foo1\' foo = bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1=  bar1 foo1 foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1="l\'autre" foo=bar' => array('foo1' => 'l\'autre', 'foo' => 'bar'),
  'foo1="l"autre" foo=bar' => array('foo1' => 'l"autre', 'foo' => 'bar'),
  'foo_1=bar_1' => array('foo_1' => 'bar_1'),
);

foreach ($tests as $string => $attributes)
{
  $t->is(sfToolkit::stringToArray($string), $attributes, '->stringToArray()');
}

// ::isUTF8()
$t->diag('::isUTF8()');
$t->is('été', true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'), false, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'.chr(254)), false, '::isUTF8() returns false if the parameter is not an UTF-8 encoded string');
// check a very long string
$string = str_repeat('Here is an UTF8 string avec du français.', 1000);
$t->is(sfToolkit::isUTF8($string), true, '::isUTF8() can operate on very large strings');

// ::literalize()
$t->diag('::literalize()');
foreach (array('true', 'on', '+', 'yes') as $param)
{
  $t->is(sfToolkit::literalize($param), true, sprintf('::literalize() returns true with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), true, sprintf('::literalize() returns true with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), true, sprintf('::literalize() returns true with "%s"', ' '.$param.' '));
}

foreach (array('false', 'off', '-', 'no') as $param)
{
  $t->is(sfToolkit::literalize($param), false, sprintf('::literalize() returns false with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), false, sprintf('::literalize() returns false with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), false, sprintf('::literalize() returns false with "%s"', ' '.$param.' '));
}

foreach (array('null', '~', '') as $param)
{
  $t->is(sfToolkit::literalize($param), null, sprintf('::literalize() returns null with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), null, sprintf('::literalize() returns null with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), null, sprintf('::literalize() returns null with "%s"', ' '.$param.' '));
}

// ::replaceConstants()
$t->diag('::replaceConstants()');
sfConfig::set('foo', 'bar');
$t->is(sfToolkit::replaceConstants('my value with a %foo% constant'), 'my value with a bar constant', '::replaceConstantsCallback() replaces constants enclosed in %');
$t->is(sfToolkit::replaceConstants('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstantsCallback() does not replace unknown constants');
sfConfig::set('bar', null);
$t->is(sfToolkit::replaceConstants('my value with a %bar% constant'), 'my value with a  constant', '::replaceConstantsCallback() replaces constants enclosed in % even if value is null');
$t->is(sfToolkit::replaceConstants('my value with a %foobar% constant'), 'my value with a %foobar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');
$t->is(sfToolkit::replaceConstants('my value with a %foo\'bar% constant'), 'my value with a %foo\'bar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');
$t->is(sfToolkit::replaceConstants('my value with a %foo"bar% constant'), 'my value with a %foo"bar% constant', '::replaceConstantsCallback() returns the original string if the constant is not defined');

// ::isPathAbsolute()
$t->diag('::isPathAbsolute()');
$t->is(sfToolkit::isPathAbsolute('/test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('\\test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('C:\\test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('d:/test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('test'), false, '::isPathAbsolute() returns false if path is relative');
$t->is(sfToolkit::isPathAbsolute('../test'), false, '::isPathAbsolute() returns false if path is relative');
$t->is(sfToolkit::isPathAbsolute('..\\test'), false, '::isPathAbsolute() returns false if path is relative');

// ::stripComments()
$t->diag('::stripComments()');

$php = <<<EOF
<?php

# A perl like comment
// Another comment
/* A very long
comment
on several lines
*/

\$i = 1; // A comment on a PHP line
EOF;

$stripped_php = '<?php $i=1;';

$t->is(preg_replace('/\s*(\r?\n)+/', ' ', sfToolkit::stripComments($php)), $stripped_php, '::stripComments() strip all comments from a php string');

// ::stripslashesDeep()
$t->diag('::stripslashesDeep()');
$t->is(sfToolkit::stripslashesDeep('foo'), 'foo', '::stripslashesDeep() strip slashes on string');
$t->is(sfToolkit::stripslashesDeep(addslashes("foo's bar")), "foo's bar", '::stripslashesDeep() strip slashes on array');
$t->is(sfToolkit::stripslashesDeep(array(addslashes("foo's bar"), addslashes("foo's bar"))), array("foo's bar", "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');
$t->is(sfToolkit::stripslashesDeep(array(array('foo' => addslashes("foo's bar")), addslashes("foo's bar"))), array(array('foo' => "foo's bar"), "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');

// ::clearDirectory()
$t->diag('::clearDirectory()');
$tmp_dir = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
mkdir($tmp_dir);
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'test', 'ok');
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
sfToolkit::clearDirectory($tmp_dir);
$t->ok(!is_dir($tmp_dir.DIRECTORY_SEPARATOR.'foo'), '::clearDirectory() removes all directories from the directory parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearDirectory() removes all directories from the directory parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'test'), '::clearDirectory() removes all directories from the directory parameter');
rmdir($tmp_dir);

// ::clearGlob()
$t->diag('::clearGlob()');
$tmp_dir = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
mkdir($tmp_dir);
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'bar');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo', 'ok');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'bar'.DIRECTORY_SEPARATOR.'bar', 'ok');
sfToolkit::clearGlob($tmp_dir.'/*/bar');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
$t->ok(is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo'), '::clearGlob() removes all files and directories matching the pattern parameter');
sfToolkit::clearDirectory($tmp_dir);
rmdir($tmp_dir);

// ::arrayDeepMerge()
$t->diag('::arrayDeepMerge()');
$t->is(
  sfToolkit::arrayDeepMerge(array('d' => 'due', 't' => 'tre'), array('d' => 'bis', 'q' => 'quattro')),
  array('d' => 'bis', 't' => 'tre', 'q' => 'quattro'),
  '::arrayDeepMerge() merges linear arrays preserving literal keys'
);
$t->is(
  sfToolkit::arrayDeepMerge(array('d' => 'due', 't' => 'tre', 'c' => array('c' => 'cinco')), array('d' => array('due', 'bis'), 'q' => 'quattro', 'c' => array('c' => 'cinque', 'c2' => 'cinco'))),
  array('d' => array('due', 'bis'), 't' => 'tre', 'q' => 'quattro', 'c' => array('c' => 'cinque', 'c2' => 'cinco')),
  '::arrayDeepMerge() recursively merges arrays preserving literal keys'
);
$t->is(
  sfToolkit::arrayDeepMerge(array(2 => 'due', 3 => 'tre'), array(2 => 'bis', 4 => 'quattro')),
  array(2 => 'bis', 3 => 'tre', 4 => 'quattro'),
  '::arrayDeepMerge() merges linear arrays preserving numerical keys'
);
$t->is(
  sfToolkit::arrayDeepMerge(array(2 => array('due'), 3 => 'tre'), array(2 => array('bis', 'bes'), 4 => 'quattro')),
  array(2 => array('bis', 'bes'), 3 => 'tre', 4 => 'quattro'),
  '::arrayDeepMerge() recursively merges arrays preserving numerical keys'
);


$arr = array(
  'foobar' => 'foo',
  'foo' => array(
    'bar' => array(
      'baz' => 'foo bar',
    ),
  ),
  'bar' => array(
    'foo',
    'bar',
  ),
  'simple' => 'string',
);

// ::getArrayValueForPath()
$t->diag('::getArrayValueForPath()');

$t->is(sfToolkit::getArrayValueForPath($arr, 'foo[bar][baz][booze]'), null, '::getArrayValueForPath() is not fooled by php mistaking strings and array');
$t->is(sfToolkit::getArrayValueForPathByRef($arr, 'foo[bar][baz][booze]'), null, '::getArrayValueForPathByRef() is not fooled by php mistaking strings and array');


$t->diag('::varExport()');

$var = array(
  'min_length' => 12
);

$t->isa_ok(sfToolkit::varExport($var), 'string', 'varExport() exports string');
$t->is(sfToolkit::varExport($var), 'array(\'min_length\' => 12)', 'varExport() exports string');

$var = array(
  'widget' => new sfPhpExpression('new stdClass()'),
);

$t->is(sfToolkit::varExport($var), 'array(\'widget\' => new stdClass())', 'varExport() exports string containing php expresion');

$var = array(
  "widget" => new sfPhpExpression('new stdClass(array("foo" => \'bar\'), null)'),
);

$t->is(sfToolkit::varExport($var), 'array(\'widget\' => new stdClass(array("foo" => \'bar\'), null))', 'varExport() exports string containing php expresion with double quotes');

$var = array(
  "widget" => new sfPhpExpression('function(){}'),
);

$t->is(sfToolkit::varExport($var), 'array(\'widget\' => function(){})', 'varExport() exports string containing php expresion with double quotes');


$var = array(
  "widget" => new sfPhpExpression('function(){}'),
  'params' => array()
);

$t->is(sfToolkit::varExport($var), 'array(\'widget\' => function(){}, \'params\' => array())', 'varExport() exports string containing php expresion with closure');


$var = array(
  "validator" => new sfPhpExpression('new sfValidatorString(array("separator" => "\n"))'),
);

$t->is(sfToolkit::varExport($var), 'array(\'validator\' => new sfValidatorString(array("separator" => "\n")))', 'varExport() exports string containing php expresion with double quotes');

$t->diag('->sfToolkit::extractClassName()');

$t->is(sfToolkit::extractClassName('myFooBar.class.php'), 'myFooBar', 'extractClassName() returns class name from the filename');

$t->is(sfToolkit::extractClassName('myFooBar.php'), 'myFooBar', 'extractClassName() returns class name from the filename');

$t->diag('->sfToolkit::extractClasses()');

try
{
  sfToolkit::extractClasses('invalid.php');
  $t->fail('extractClasses() throws an sfFileException if the file does not exist');
}
catch(sfFileException $e)
{
  $t->pass('extractClasses() throws an sfFileException if the file does not exist');
}

$t->is(sfToolkit::extractClasses(dirname(__FILE__).'/fixtures/lib/file.php'), array(
  'customClass',
  'myCustomIterface',
  'myExtendedClass',
  'myTrulyExtendedClass'
), 'extractClasses() returns all classes and interfaces defined in the file');

$t->is(sfToolkit::extractClasses(dirname(__FILE__).'/fixtures/lib/file2.php'), array(), 'extractClasses() returns all classes and interfaces defined in the file');

$t->diag('::isCallable()');

$t->isa_ok(sfToolkit::isCallable('phpinfo'), 'boolean', '::isCallable() return boolean');
$t->is(sfToolkit::isCallable('phpinfo'), true, '::isCallable() return false when callback is invalid');
$t->is(sfToolkit::isCallable('anonsnse'), false, '::isCallable() return false when callback is invalid');

class sfFoo {
  public static function bar()
  {
  }

  public function get()
  {
  }
}

$t->is(sfToolkit::isCallable(array('sfFoo', 'bar'), false, $callableName), true, '::isCallable() return true when callback is valid');
$t->isa_ok($callableName, 'string', '::isCallable() returns callable name as string');
$t->is($callableName, 'sfFoo::bar', '::isCallable() returns callable name');

$foo = new sfFoo();
$t->is(sfToolkit::isCallable(array($foo, 'get'), false, $callableName), true, '::isCallable() return true when callback is valid');
$t->isa_ok($callableName, 'string', '::isCallable() returns callable name as string');
$t->is($callableName, 'sfFoo::get', '::isCallable() returns callable name');

$t->diag('::isFunctionDisabled()');

$t->isa_ok(sfToolkit::isFunctionDisabled('phpinfo'), 'boolean', '::isCallable() return boolean');
$t->is(sfToolkit::isFunctionDisabled('phpinfo'), false, '::isCallable() return false when callback is invalid');

$t->diag('replaceConstantsWithModifiers');

sfConfig::set('foo', 'barfoo');

$t->is(sfToolkit::replaceConstantsWithModifiers('my value with a %foo{0,1}% constant'), 'my value with a b constant', '::replaceConstantsWithModifiers() replaces constants enclosed in %');
$t->is(sfToolkit::replaceConstantsWithModifiers('my value with a %foo{0,4}% constant'), 'my value with a barf constant', '::replaceConstantsWithModifiers() replaces constants enclosed in %');

sfConfig::set('foo', 'bar foo');

$t->is(sfToolkit::replaceConstantsWithModifiers('my value with a %foo{slugify}% constant'), 'my value with a bar-foo constant', '::replaceConstantsWithModifiers() replaces constants enclosed in %');
$t->is(sfToolkit::replaceConstantsWithModifiers('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstantsWithModifiers() does not replace unknown constants');
$t->is(sfToolkit::replaceConstantsWithModifiers('my value with a %foo{slugify|2,3}% constant'), 'my value with a r-f constant', '::replaceConstantsWithModifiers() replaces constants enclosed in %');

try
{
  sfToolkit::replaceConstantsWithModifiers('my value with a %foo{abc}% constant');
  $t->fail('::replaceConstantsWithModifiers() throws logic exception if modifier is not understood.');
}
catch(LogicException $e)
{
  $t->pass('::replaceConstantsWithModifiers() throws logic exception if modifier is not understood.');
}

$t->diag('isBlank()');

$blankTests = array(
  '' => true,
  'ahoj' => false,
  0 => false,
  '-0' => false
);

foreach($blankTests as $test => $expected)
{
  $t->is(sfToolkit::isBlank($test), $expected, 'isBlank() works as expected');
}

$t->is(sfToolkit::isBlank(array()), true, 'isBlank() works as expected for array');
$t->is(sfToolkit::isBlank(array('0')), false, 'isBlank() works as expected for array');
$t->is(sfToolkit::isBlank(array('0', '')), false, 'isBlank() works as expected for array');
$t->is(sfToolkit::isBlank(array(array())), false, 'isBlank() works as expected for array');

$t->diag('arrayExtend()');

$test = array(
  'widget' => array(
    'class' => 'sfFoobarWidget',
    'options' => array(
      'culture' => 'en'
    )
  )
);

$test2 = array(
  'widget' =>  array(
    'class' => 'sfFoobarWidgetAnotherOne',
    'options' => array(
      'add_empty' => true
    )
  )
);

$expected = array(
  'widget' =>  array(
    'class' => 'sfFoobarWidgetAnotherOne',
     'options' => array(
      'culture' => 'en',
      'add_empty' => true
  ))
);

$expected2 = array(
  'widget' =>  array(
    'class' => 'sfFoobarWidget',
     'options' => array(
      'add_empty' => true,
      'culture' => 'en'
  ))
);

$t->is(sfToolkit::arrayExtend($test, $test2), $expected, '->arrayExtend() works ok');
$t->is(sfToolkit::arrayExtend($test2, $test), $expected2, '->arrayExtend() works ok');
