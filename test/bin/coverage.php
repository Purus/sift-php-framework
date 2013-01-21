<?php

require_once(dirname(__FILE__).'/../../lib/vendor/lime/lime.php');
require_once($root_dir.'/lib/util/sfFinder.class.php');
require_once($root_dir.'/lib/util/sfGlobToRegex.class.php');
require_once($root_dir.'/lib/util/sfNumberCompare.class.php');

$h = new lime_harness(new lime_output_color());
$h->base_dir = realpath(dirname(__FILE__).'/..');

// unit tests
$h->register_glob($h->base_dir.'/unit/*/*Test.php');

// functional tests
$h->register_glob($h->base_dir.'/functional/*Test.php');
$h->register_glob($h->base_dir.'/functional/*/*Test.php');

$c = new lime_coverage($h);
$c->extension = '.class.php';
$c->verbose = false;
$c->base_dir = realpath(dirname(__FILE__).'/../../lib');

$finder = pakeFinder::type('file')->ignore_version_control()->name('*.php')->prune('vendor');
$c->register($finder->in($c->base_dir));
$c->run();
