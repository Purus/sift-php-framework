<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(2);

$tmpDir = sys_get_temp_dir().'/log';

sfToolkit::clearDirectory($tmpDir);
@unlink($tmpDir);

if(!is_dir($tmpDir))
{
  mkdir($tmpDir);
}

// create log file without date information
$file = $tmpDir.'/front_dev.log';
file_put_contents($file, 'Log entry' . PHP_EOL);
touch($file);

// prepare env
// create 13 files in temp directory and let manager do its job
for($i = 0; $i < 14; $i++)
{
  $time = strtotime(sprintf('-%s days', $i));
  $fileName = sprintf('front_dev_%s.log', date('Y_m_d', $time));
  $file = $tmpDir.'/'.$fileName;
  file_put_contents($file, 'Log entry'. PHP_EOL);
  touch($file, $time);
}

$m = new sfLogManager($tmpDir);
$m->rotate('front', 'dev');

// how many files are in the log dir?
// get all log history files for this application and environment
$logs = sfFinder::type('file')
    ->ignoreVersionControl()
    ->maxDepth(1)
    ->name('front_dev*.log')
    ->in($tmpDir.'/history');

$t->ok(count($logs) == 10, 'Files have copied to history folder');

$logs = sfFinder::type('file')
    ->ignoreVersionControl()
    ->maxDepth(0)
    ->name('front_dev*.log')
    ->in($tmpDir);

$t->ok(count($logs) == 0, 'Old logs were deleted');

sfToolkit::clearDirectory($tmpDir);
@unlink($tmpDir);