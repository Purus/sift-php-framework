<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/sfCoreMock.class.php');

$t = new lime_test(3);

function lessphp_double($arg) {
    list($type, $value, $unit) = $arg;
    return array($type, $value*2, $unit);
}

function lessphp_image_path($arg) {
    list($type, $delim, $image) = $arg;
    $value = sprintf('url("/images/%s")', $image[0]);
    return array($type, '', array($value));
}

$c = new sfLessCompiler(new sfEventDispatcher(), array(
    'cache_dir' => sys_get_temp_dir(),
    'web_cache_dir' => sys_get_temp_dir() . '/less_compiled'
));

$c->registerFunction("double", "lessphp_double");
$c->registerFunction("imagePath", "lessphp_image_path");

$less = '

  div { width: double(400px); }

  div {
    background-image: imagePath(\'foo.gif\');
  }
';

$expected = 'div{width:800px;}div{background-image:url("/images/foo.gif");}';
$result = $c->compile($less);
$t->is($result, $expected, 'Less compiler registers functions ok');

$t->diag('importDir');

$c->setImportDir('');
$c->addImportDir(dirname(__FILE__) . DIRECTORY_SEPARATOR. 'fixtures');

$result = $c->compileFile(dirname(__FILE__) . '/fixtures/compile.less');
$t->is($result, 'body{background:red;}', 'Less compiler compiles file with imports ok.');

$result = $c->compileFile(dirname(__FILE__) . '/fixtures/nested/compile.less');

$t->is($result, 'body{background:pink;}', 'Less compiler compiles file with imports ok.');
