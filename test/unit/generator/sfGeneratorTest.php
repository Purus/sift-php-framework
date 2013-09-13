<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(0);

class myGenerator extends sfGenerator
{
}

$manager = new sfGeneratorManager($savePath = sys_get_temp_dir());

$generator = new myGenerator($manager);
