<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

sfConfig::set('sf_sift_lib_dir', realpath(dirname(__FILE__).'/../../../lib'));

$t = new lime_test(8, new lime_output_color());

$handler = new sfGeneratorConfigHandler();
$handler->initialize();

$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'sfGeneratorConfigHandler'.DIRECTORY_SEPARATOR;

$t->diag('parse errors');
$files = array(
  $dir.'empty.yml',
  $dir.'no_generator_class.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('generator.yml must have a "class" section');
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/must specify a generator class section under the generator section/', 'generator.yml must have a "class" section');
}

$files = array(
  $dir.'empty.yml',
  $dir.'no_generator_section.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('generator.yml must have a "generator" section');
  $t->diag(var_export($data, true));
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/must specify a generator section/', 'generator.yml must have a "generator" section');
}

$files = array(
  $dir.'empty.yml',
  $dir.'root_fields_section.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('generator.yml can have a "fields" section but only under "param"');
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/can specify a "fields" section but only under the param section/', 'generator.yml can have a "fields" section but only under "param"');
}

$files = array(
  $dir.'empty.yml',
  $dir.'root_list_section.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('generator.yml can have a "list" section but only under "param"');
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/can specify a "list" section but only under the param section/', 'generator.yml can have a "list" section but only under "param"');
}

$files = array(
  $dir.'empty.yml',
  $dir.'root_edit_section.yml',
);

try
{
  $data = $handler->execute($files);
  $t->fail('generator.yml can have a "edit" section but only under "param"');
}
catch (sfParseException $e)
{
  $t->like($e->getMessage(), '/can specify a "edit" section but only under the param section/', 'generator.yml can have a "edit" section but only under "param"');
}


$t->diag('->replaceConstantsForExpressions');

$value = 'Edit user "%%username%%"';
$t->is(sfGeneratorConfigHandler::replaceConstantsForExpressions($value), 'Edit user "%%username%%"', '->replaceConstantsForExpressions() does not touch the value if its not configuration directive');

$value = 'Edit user "%app_user.name%"';
$result = sfGeneratorConfigHandler::replaceConstantsForExpressions($value);
$t->isa_ok($result, 'sfPhpExpression', '->replaceConstantsForExpressions() works ok for expression');

$t->is_deeply((string)$result, 'sfConfig::get(\'app_user.name\')', '->replaceConstantsForExpressions() works ok for expression');
