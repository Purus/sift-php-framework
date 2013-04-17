<?php

require_once(dirname(__FILE__) . '/../../bootstrap/unit.php');

$t = new lime_test(66);

$tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);

$content = 'This is an ASCII file.';
file_put_contents($tmpDir . DIRECTORY_SEPARATOR . 'test.txt', $content);
file_put_contents($tmpDir . DIRECTORY_SEPARATOR . 'foo.txt', $content);

class testValidatorFile extends sfValidatorFile {

  public function getMimeType($file, $fallback)
  {
    return parent::getMimeType($file, $fallback);
  }

  public function getMimeTypesFromCategory($category)
  {
    return parent::getMimeTypesFromCategory($category);
  }

}

// ->getMimeTypesFromCategory()
$t->diag('->getMimeTypesFromCategory()');
$v = new testValidatorFile();
try
{
  $t->is($v->getMimeTypesFromCategory('non_existant_category'), '');
  $t->fail('->getMimeTypesFromCategory() throws an InvalidArgumentException if the category does not exist');
}
catch(InvalidArgumentException $e)
{
  $t->pass('->getMimeTypesFromCategory() throws an InvalidArgumentException if the category does not exist');
}
$categories = $v->getOption('mime_categories');
$t->is($v->getMimeTypesFromCategory('web_images'), $categories['web_images'], '->getMimeTypesFromCategory() returns an array of mime types for a given category');
$v->setOption('mime_categories', array_merge($v->getOption('mime_categories'), array('text' => array('text/plain'))));
$t->is($v->getMimeTypesFromCategory('text'), array('text/plain'), '->getMimeTypesFromCategory() returns an array of mime types for a given category');

// ->getMimeType()
$t->diag('->getMimeType()');
$v = new testValidatorFile();
$t->is($v->getMimeType($tmpDir . '/test.txt', 'image/png'), 'text/plain', '->getMimeType() guesses the type of a given file');
$t->is($v->getMimeType($tmpDir . '/foo.txt', 'text/plain'), 'text/plain', '->getMimeType() returns the default type if the file type is not guessable');

// ->clean()
$t->diag('->clean()');
$v = new testValidatorFile();
try
{
  $v->clean(array('test' => true));
  $t->fail('->clean() throws an sfValidatorError if the given value is not well formatted');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the given value is not well formatted');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
}
$f = $v->clean(array('tmp_name' => $tmpDir . '/test.txt'));
$t->ok($f instanceof sfUploadedFile, '->clean() returns a sfUploadedFile instance');
$t->is($f->getOriginalName(), '', '->clean() returns a sfUploadedFile with an empty original name if the name is not passed in the initial value');
$t->is($f->getSize(), strlen($content), '->clean() returns a sfUploadedFile with a computed file size if the size is not passed in the initial value');
$t->is($f->getType(), 'text/plain', '->clean() returns a sfUploadedFile with a guessed content type');

class myValidatedFile extends sfUploadedFile {

}

$v->setOption('uploaded_file_class', 'myValidatedFile');
$f = $v->clean(array('tmp_name' => $tmpDir . '/test.txt'));
$t->ok($f instanceof myValidatedFile, '->clean() can take a "uploaded_file_class" option');

foreach(array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_PARTIAL, UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION) as $error)
{
  try
  {
    $v->clean(array('tmp_name' => $tmpDir . '/test.txt', 'error' => $error));
    $t->fail('->clean() throws an sfValidatorError if the error code is not UPLOAD_ERR_OK (0)');
    $t->skip('', 1);
  }
  catch(sfValidatorError $e)
  {
    $t->pass('->clean() throws an sfValidatorError if the error code is not UPLOAD_ERR_OK (0)');
    $t->is($e->getCode(), $code = strtolower(str_replace('UPLOAD_ERR_', '', $e->getCode())), '->clean() throws an error code of ' . $code);
  }
}

// max file size
$t->diag('max file size');
$v->setOption('max_size', 4);
try
{
  $v->clean(array('tmp_name' => $tmpDir . '/test.txt'));
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the file size is too large');
  $t->is($e->getCode(), 'max_size', '->clean() throws an error code of max_size');
}
$v->setOption('max_size', null);

// mime types
$t->diag('mime types');
$v->setOption('mime_types', 'web_images');
try
{
  $v->clean(array('tmp_name' => $tmpDir . '/test.txt'));
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the file mime type is not in mime_types option');
  $t->is($e->getCode(), 'mime_types', '->clean() throws an error code of mime_types');
}
$v->setOption('mime_types', null);

// required
$v = new testValidatorFile();
try
{
  $v->clean(array('tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'name' => '', 'size' => 0, 'type' => ''));
  $t->fail('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
  $t->is($e->getCode(), 'required', '->clean() throws an error code of required');
}
try
{
  $v->clean(null);
  $t->fail('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
  $t->skip();
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the file is required and no file is uploaded');
  $t->is($e->getCode(), 'required', '->clean() throws an error code of required');
}
$v = new testValidatorFile(array('required' => false));
$t->is($v->clean(array('tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'name' => '', 'size' => 0, 'type' => '')), null, '->clean() handles the required option correctly');

// sfUploadedFile
// ->getOriginalName() ->getTempName() ->getSize() ->getType()
$t->diag('->getOriginalName() ->getTempName() ->getSize() ->getType()');
sfToolkit::clearDirectory($tmpDir . '/foo');
if(is_dir($tmpDir . '/foo'))
{
  rmdir($tmpDir . '/foo');
}
$f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getOriginalName(), 'test.txt', '->getOriginalName() returns the original name');
$t->is($f->getTempName(), $tmpDir . '/test.txt', '->getTempName() returns the temp name');
$t->is($f->getType(), 'text/plain', '->getType() returns the content type');
$t->is($f->getSize(), strlen($content), '->getSize() returns the size of the uploaded file');

// ->save() ->isSaved() ->getSavedName()
$t->diag('->save() ->isSaved() ->getSavedName()');
$f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->isSaved(), false, '->isSaved() returns false if the file has not been saved');
$t->is($f->getSavedName(), null, '->getSavedName() returns null if the file has not been saved');
$filename = $f->save($tmpDir . '/foo/test1.txt');
$t->is($filename, $tmpDir . '/foo/test1.txt', '->save() returns the saved filename');
$t->is(file_get_contents($tmpDir . '/foo/test1.txt'), file_get_contents($tmpDir . '/test.txt'), '->save() saves the file to the given path');
$t->is($f->isSaved(), true, '->isSaved() returns true if the file has been saved');
$t->is($f->getSavedName(), $tmpDir . '/foo/test1.txt', '->getSavedName() returns the saved file name');

$f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content), $tmpDir);
$filename = $f->save($tmpDir . DIRECTORY_SEPARATOR . 'foo'.DIRECTORY_SEPARATOR.'test1.txt');
$t->is($filename, 'foo'.DIRECTORY_SEPARATOR.'test1.txt', '->save() returns the saved filename relative to the path given');
$t->is(file_get_contents($tmpDir . '/foo/test1.txt'), file_get_contents($tmpDir . '/test.txt'), '->save() saves the file to the given path');
$t->is($f->getSavedName(), $tmpDir . DIRECTORY_SEPARATOR . 'foo'.DIRECTORY_SEPARATOR.'test1.txt', '->getSavedName() returns the saved file name');

$filename = $f->save('foo'.DIRECTORY_SEPARATOR.'test1.txt');
$t->is($filename, 'foo'.DIRECTORY_SEPARATOR.'test1.txt', '->save() returns the saved filename relative to the path given');
$t->is(file_get_contents($tmpDir . '/foo/test1.txt'), file_get_contents($tmpDir . '/test.txt'), '->save() saves the file to the given path and uses the path if the file is not absolute');
$t->is($f->getSavedName(), $tmpDir . DIRECTORY_SEPARATOR . 'foo'.DIRECTORY_SEPARATOR.'test1.txt', '->getSavedName() returns the saved file name');

$filename = $f->save();
$t->is(file_get_contents($tmpDir . DIRECTORY_SEPARATOR . $filename), file_get_contents($tmpDir . DIRECTORY_SEPARATOR . 'test.txt'), '->save() returns the generated file name is none was given');
$t->is($f->getSavedName(), $tmpDir . DIRECTORY_SEPARATOR . $filename, '->getSavedName() returns the saved file name');

try
{
  $f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content));
  $f->save();
  $t->fail('->save() throws an Exception if you don\'t give a filename and the path is empty');
}
catch(Exception $e)
{
  $t->pass('->save() throws an Exception if you don\'t give a filename and the path is empty');
}

try
{
  $f->save($tmpDir . '/test.txt/test1.txt');
  $t->fail('->save() throws an Exception if the directory already exists and is not a directory');
}
catch(Exception $e)
{
  $t->pass('->save() throws an Exception if the directory already exists and is not a directory');
}

// ->getExtension()
$t->diag('->getExtension()');
$f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getExtension(), '.txt', '->getExtension() returns file extension based on the content type');
$f = new sfUploadedFile('test.txt', 'image/x-png', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getExtension(), '.txt', '->getExtension() returns file extension based on the real content type (ignores what was given by browser)');

$f = new sfUploadedFile('test.txt', 'very/specific', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getExtension(), '.txt', '->getExtension() returns correct type if nonsence type is given');
$f = new sfUploadedFile('test.txt', '', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getExtension(), '.txt', '->getExtension() returns correct type if the content type is empty');

// ->getOriginalExtension()
$t->diag('->getOriginalExtension()');
$f = new sfUploadedFile('test.txt', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getOriginalExtension(), '.txt', '->getOriginalExtension() returns the extension based on the uploaded file name');
$f = new sfUploadedFile('test', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getOriginalExtension(), '', '->getOriginalExtension() returns an original extension if the uploaded file name has no extension');

$f = new sfUploadedFile('test', 'text/plain', $tmpDir . '/test.txt', strlen($content));
$t->is($f->getOriginalExtension('bin'), 'bin', '->getOriginalExtension() takes a default extension as its first argument');

$t->diag('multiple');

$v = new testValidatorFile(array('multiple' => true));

try
{
  $v->clean(array(array('test' => true)));
  $t->fail('->clean() throws an sfValidatorError if the given value is not well formatted');
  $t->skip('', 1);
}
catch(sfValidatorError $e)
{
  $t->pass('->clean() throws an sfValidatorError if the given value is not well formatted');
  $t->is($e->getCode(), 'invalid', '->clean() throws a sfValidatorError');
}

$f = $v->clean(array(array('tmp_name' => $tmpDir . '/test.txt')));
foreach($f as $file)
{
  $t->ok($file instanceof sfUploadedFile, '->clean() returns a sfUploadedFile instance');
  $t->is($file->getOriginalName(), '', '->clean() returns a sfUploadedFile with an empty original name if the name is not passed in the initial value');
  $t->is($file->getSize(), strlen($content), '->clean() returns a sfUploadedFile with a computed file size if the size is not passed in the initial value');
  $t->is($file->getType(), 'text/plain', '->clean() returns a sfUploadedFile with a guessed content type');
}

unlink($tmpDir . '/test.txt');
sfToolkit::clearDirectory($tmpDir . '/foo');
rmdir($tmpDir . '/foo');
