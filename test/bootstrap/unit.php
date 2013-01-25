<?php
 
define('DS', DIRECTORY_SEPARATOR);

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/yaml/sfYaml.class.php');
sfConfig::set('sf_sift_lib_dir', realpath($_test_dir.'/../lib'));
sfConfig::set('sf_sift_data_dir', realpath($_test_dir.'/../data'));

require_once(dirname(__FILE__).'/testAutoloader.class.php');

testAutoloader::initialize();

function __autoload($class)
{
  return testAutoloader::__autoload($class);
}

class sfException extends Exception
{
  private $name = null;

  protected function setName ($name)
  {
    $this->name = $name;
  }

  public function getName ()
  {
    return $this->name;
  }
}

// Helper for cross platform testcases that validate output
function fix_linebreaks($content)
{
  return str_replace(array("\r\n", "\n", "\r"), "\n", $content);
}
