<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$siftLibDir  = realpath(dirname(__FILE__) . '/../../lib');
require_once $siftLibDir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

/**
 * Command line usage:
 *
 * php poedit_js_extractor.php -o OUTPUT_FILE -k FUNCTION_KEYWORDS -f SPACE_SEPARATED_LIST_OF_JS_FILES
 *
 * Poedit configuration:
 *
 * Parser command:
 *
 * <code>
 * php YOUR_PATH/poedit_js_extractor.php -o %o -k "%K" -f "%F" -c %c
 * </code>
 *
 * Item is keyword list: %k
 * Item in input file list: %f
 * Source code charset (not implemented): %c
 */

// command line options
$shortopts  = '';
$shortopts .= 'o:';  // Output file
$shortopts .= 'k:';  // Keywords
$shortopts .= 'f:';  // Array of input files
$shortopts .= 'c:';  // Encoding of sources
// long options
$longopts   = array();
// catched errors
$errors     = array();

$options = getopt($shortopts, $longopts);

if(!isset($options['o']) || !isset($options['f']))
{
  echo "Invalid usage.\n";
  echo "See the script source for command line options.\n";
  exit(1);
}

$output   = $options['o'];
$messages = $meta = array();

$files = explode(' ', trim($options['f']));

$extractor = new sfI18nJavascriptExtractor(array(
  'functions' => explode(' ', isset($options['k']) ? trim($options['k']) : '__')
));

$save = false;

foreach($files as $file)
{
  $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
  // try to detect if its relative path
  if(preg_match('~^../~', $file))
  {
    $file = (getcwd() . '/' . $file);
  }

  if(!is_readable($file))
  {
    $errors[] = sprintf('File "%s" is not readable or does not exist', $file);
    continue;
  }

  $foundMessages = $extractor->extract(file_get_contents($file));
  if(count($foundMessages))
  {
    $save = true;
    foreach($foundMessages as $message)
    {
      $messages[$message] = '';
    }
  }
}

if(!is_readable($output))
{
  $dir = dirname($output);
  if(!is_dir($dir))
  {
    mkdir($dir);
  }
}

$po = sfI18nGettext::factory('PO');
$po->fromArray(array(
  'meta'    => $meta,
  'strings' => $messages
));

$result = $po->save($output);

if(count($errors))
{
  echo "Error(s) occured while extraction\n";
  foreach($errors as $error)
  {
    printf("  %s\n", $error);
  }
}
else
{
  echo "Successfully done.\n";
}

exit($result ? 0 : 1);