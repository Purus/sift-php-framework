<?php

$shortopts  = '';
$longopts  = array(
  "groups::",    // Optional value
);

$options = getopt($shortopts, $longopts);

require_once(dirname(__FILE__).'/../../lib/vendor/lime/lime.php');

$h = new lime_harness(new lime_output_color());

$h->base_dir = realpath(dirname(__FILE__).'/..');

// cache autoload files
require_once($h->base_dir.'/bootstrap/unit.php');
testAutoloader::initialize(true);

if(!isset($options['groups']))
{
  echo usage();
  exit;
}

$groups = explode(',', isset($options['groups']) ? $options['groups'] : '');

if($groups)
{
  foreach($groups as $group)
  {
    // unit tests
    $h->register_glob(sprintf('%s/unit/%s/*Test.php', $h->base_dir, $group));
  }

  $ret = $h->run();
  exit($ret);
}

function usage()
{
  echo <<<USAGE
Usage:

Run takes following arguments:

--groups=groupName

USAGE;

}


// functional tests
// $h->register_glob($h->base_dir.'/functional/*Test.php');
// $h->register_glob($h->base_dir.'/functional/*/*Test.php');
// other tests
// $h->register_glob($h->base_dir.'/other/*Test.php');

