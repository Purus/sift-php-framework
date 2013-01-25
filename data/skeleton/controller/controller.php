<?php

define('SF_ROOT_DIR',    realpath(dirname(__file__).'/..'));
define('SF_APP',         '##APP_NAME##');
define('SF_ENVIRONMENT', '##ENV_NAME##');
define('SF_DEBUG',       ##DEBUG##);
// define to match hosting directory layout
// define('SF_WEB_DIR_NAME', 'web');
// define('SF_UPLOAD_DIR_NAME', 'files');
        
require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.
        DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

sfContext::getInstance()->getController()->dispatch();
