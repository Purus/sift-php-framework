<?php

// guess current application
if (!isset($app)) {
    $traces = debug_backtrace();
    $caller = $traces[0];

    $dirPieces = explode(DIRECTORY_SEPARATOR, dirname($caller['file']));
    $app = array_pop($dirPieces);
}

require_once dirname(__FILE__) . '/../../config/config.php';

require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

define('SF_ROOT_DIR', dirname(__FILE__) . '/../..');

sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir, true);

// create context instance
sfContext::createInstance(
    sfCore::getApplication($app, 'test', isset($debug) ? $debug : true)
);

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));
