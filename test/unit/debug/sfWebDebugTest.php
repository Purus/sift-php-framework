<?php

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(1);

class sfWebDebugTest extends sfWebDebug
{
  public function __construct()
  {
    $this->options['image_root_path'] = '';
    $this->options['request_parameters'] = array();
  }
}

$debug = new sfWebDebugTest();

$t->todo('add tests');