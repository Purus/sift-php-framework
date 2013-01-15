<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(1, new lime_output_color());

$download = new sfHttpDownload();

$t->is($download->getOptions(), array(), 'download returns an array of default options');

