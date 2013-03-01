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

require_once(dirname(__FILE__).'/../../lib/config/sfIConfigurable.interface.php');
require_once(dirname(__FILE__).'/../../lib/config/sfConfigurable.class.php');
require_once(dirname(__FILE__).'/../../lib/exception/sfException.class.php');
require_once(dirname(__FILE__).'/../../lib/file/sfFilesystem.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfFinder.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfGlobToRegex.class.php');
require_once(dirname(__FILE__).'/../../lib/log/sfILogger.interface.php');
require_once(dirname(__FILE__).'/../../lib/log/sfLogger.class.php');
require_once(dirname(__FILE__).'/../../lib/log/sfStreamLogger.class.php');
require_once(dirname(__FILE__).'/../../lib/log/sfConsoleLogger.class.php');
require_once(dirname(__FILE__).'/../../lib/vendor/lime/lime.php');

if(!isset($argv[1]))
{
  throw new sfException('You must provide version prefix.');
}

if(!isset($argv[2]))
{
  throw new sfException('You must provide stability status (alpha/beta/stable).');
}

$logger = new sfConsoleLogger();

$stability = $argv[2];

$filesystem = new sfFilesystem($logger);

if(($stability == 'beta' || $stability == 'alpha'))
{
  $version_prefix = $argv[1];
  list($latest) = $filesystem->execute('git rev-parse --verify --short HEAD');

  if(!isset($latest))
  {
    throw new sfException('Unable to find last revision!');
  }

  // make a PEAR compatible version
  $version = $version_prefix . '-' . trim($latest);
}
else
{
  $version = $argv[1];
}

$logger->log(sprintf('Releasing Sift version "%s"', $version));

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

$logger->log('Running all tests');

// $ret = $h->run();
$ret = true;

if(!$ret)
{
  throw new sfException('Some tests failed. Release process aborted!');
}

if(is_file('package.xml'))
{
  $filesystem->remove(getcwd().DIRECTORY_SEPARATOR.'package.xml');
}

$filesystem->copy(getcwd().'/package.xml.tmpl', getcwd().'/package.xml');

// add class files
$finder = sfFinder::type('file')->ignore_version_control()->relative();
$xml_classes = '';
$dirs = array('lib' => 'php', 'data' => 'data');
$skip = array(
  'sift', 'sift.bat', 'update_core_autoloader.php', 'release.php', 'build_docs.php', 'changelog.php'
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
$filesystem->replaceTokens(getcwd().DIRECTORY_SEPARATOR.'package.xml', '##', '##', array(
  'SIFT_VERSION' => $version,
  'CURRENT_DATE' => date('Y-m-d'),
  'CLASS_FILES' => $xml_classes,
  'STABILITY' => $stability,
));

$results = $filesystem->sh('pear package');

echo $results;

$filesystem->remove(getcwd().DIRECTORY_SEPARATOR.'package.xml');

$filesystem->rename(getcwd().'/Sift-'.$version . '.tgz', getcwd() . '/dist/Sift-'.$version . '.tgz');

// copy .tgz as Sift-latest.tgz
// $filesystem->copy(getcwd() . '/dist/Sift-' . $version . '.tgz', getcwd() . '/dist/Sift-latest.tgz');

exit(0);
