<?php

if (!isset($app)) {
    $app = '##APP_NAME##';
}

// load settings from project config.php
include dirname(__FILE__) . '/../../../../config/config.php';

define('SF_ROOT_DIR', dirname(__FILE__) . '/../fixtures/project/');

require_once $sf_sift_lib_dir . '/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir, true);

function ##PLUGIN_NAME##_cleanup()
{
    sfToolkit::clearDirectory(SF_ROOT_DIR . '/cache');
    sfToolkit::clearDirectory(SF_ROOT_DIR . '/log');
}

// cleanup first
##PLUGIN_NAME##_cleanup();

register_shutdown_function('##PLUGIN_NAME##_cleanup');

// create context instance
sfContext::createInstance(
    sfCore::getApplication($app, 'test', isset($debug) ? $debug : true)
);
