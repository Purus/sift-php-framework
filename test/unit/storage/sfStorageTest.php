<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(0, new lime_output_color());

class myStorage extends sfStorage
{
  function read($key) {}
  function remove($key) {}
  function shutdown() {}
  function write($key, $data) {}
  function regenerate($destroy = false) {}
  function isStarted() {return true;}
  function start(){}
}

$storage = new myStorage();