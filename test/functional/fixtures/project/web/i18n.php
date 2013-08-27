<?php

define('SF_ROOT_DIR', dirname(__FILE__) . '/..');

require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once $sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php';    
sfCoreAutoload::register();

sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir);

// sfToolkit::clearDirectory(SF_ROOT_DIR . '/cache');
// sfToolkit::clearDirectory(SF_ROOT_DIR . '/log');

sfContext::createInstance(
  sfCore::getApplication('i18n', 'dev', true)
)->dispatch();
