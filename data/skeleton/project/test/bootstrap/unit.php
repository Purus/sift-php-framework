<?php

// load settings from project config.php
include dirname(__FILE__) . '/../../config/config.php';

require_once $sf_sift_lib_dir.'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once $sf_sift_lib_dir.'/vendor/lime/lime.php';
