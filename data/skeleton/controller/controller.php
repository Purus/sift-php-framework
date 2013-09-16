<?php
/**
 * This is an entry point for application "##APP_NAME##" in "##ENVIRONMENT##" environment.
 *
 * Debugging features: ##IS_DEBUG_HUMAN##
 */
##IP_CHECK##
define('SF_ROOT_DIR', realpath(dirname(__FILE__).'/..'));

// load lib and dir settings
require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once $sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir);

sfContext::createInstance(
  sfCore::getApplication('##APP_NAME##', '##ENVIRONMENT##', ##IS_DEBUG##)
)->dispatch();
