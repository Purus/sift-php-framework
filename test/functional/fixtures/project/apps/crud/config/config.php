<?php

// include project configuration
include(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

// symfony bootstraping
require_once($sf_sift_lib_dir.'/core/sfCore.class.php');
sfCore::bootstrap($sf_sift_lib_dir, $sf_sift_data_dir);
