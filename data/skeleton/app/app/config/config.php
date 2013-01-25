<?php

// include project configuration
include(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
// load core
require_once($sf_sift_lib_dir.'/core/sfCore.class.php');
// bootstrap the framework
sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir);
