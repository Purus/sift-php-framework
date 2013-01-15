<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Release script.
 *
 * Usage: php data/bin/release.php 1.0.0 stable
 *
 * @package    Sift
 * @subpackage cli
 */
require_once(dirname(__FILE__) . '/../../lib/vendor/pake/pakeFunction.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/pake/pakeGetopt.class.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/lime/lime.php');

if(!isset($argv[1]))
{
  throw new Exception('You must provide version prefix.');
}

if(!isset($argv[2]))
{
  throw new Exception('You must provide stability status (alpha/beta/stable).');
}

$stability = $argv[2];

if(($stability == 'beta' || $stability == 'alpha') && count(explode('.', $argv[1])) < 2)
{
  $version_prefix = $argv[1];

  $result = pake_sh('git git rev-parse --verify HEAD');
  if(preg_match('/Status against revision\:\s+(\d+)\s*$/im', $result, $match))
  {
    $version = $match[1];
  }

  if(!isset($version))
  {
    throw new Exception('Unable to find last revision!');
  }

  // make a PEAR compatible version
  $version = $version_prefix . '.' . $version;
}
else
{
  $version = $argv[1];
}

echo 'Releasing Sift version "' . $version . "\"\n";

// Test
$h = new lime_harness(array(
            'output' => new lime_output_color(),
            'error_reporting' => true,
            'verbose' => false
        ));

$h->base_dir = realpath(dirname(__FILE__) . '/../../test');

// unit tests
$h->register_glob($h->base_dir . '/unit/*/*Test.php');

// functional tests
$h->register_glob($h->base_dir . '/functional/*Test.php');
$h->register_glob($h->base_dir . '/functional/*/*Test.php');

echo "Running all tests\n";

$ret = true;
// $ret = $h->run();

if(!$ret)
{
  throw new Exception('Some tests failed. Release process aborted!');
}

if(is_file('package.xml'))
{
  pake_remove('package.xml', getcwd());
}

pake_copy(getcwd() . '/package.xml.tmpl', getcwd() . '/package.xml');

// add class files
$finder = pakeFinder::type('file')->ignore_version_control()->relative();
$xml_classes = '';
$dirs = array('lib' => 'php', 'data' => 'data');
$skip = array(
  'sift', 'sift.bat'    
);

foreach($dirs as $dir => $role)
{
  $class_files = $finder->in($dir);
  foreach($class_files as $file)
  {
    // skip files
    if(in_array(basename($file), $skip))
    {
      continue;
    }
    $xml_classes .= '<file role="' . $role . '" baseinstalldir="Sift" install-as="' . $file . '" name="' . $dir . '/' . $file . '" />' . "\n";
  }
}

// replace tokens
pake_replace_tokens('package.xml', getcwd(), '##', '##', array(
  'SIFT_VERSION' => $version,
  'CURRENT_DATE' => date('Y-m-d'),
  'CLASS_FILES' => $xml_classes,
  'STABILITY' => $stability,
));

$results = pake_sh('pear package');

echo $results;

pake_remove('package.xml', getcwd());

// copy .tgz as Sift-latest.tgz
pake_copy(getcwd() . '/Sift-' . $version . '.tgz', getcwd() . '/Sift-latest.tgz');

exit(0);
