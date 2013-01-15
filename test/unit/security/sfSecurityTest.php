<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/security/sfSecurity.class.php');

$t = new lime_test(10, new lime_output_color());

$host = 'localhost';
$protocol = 'http';

class sfRequest {
  
  public function getHost()
  {
    global $host;
    return $host;
  }
  
  public function getProtocol()
  {
    global $protocol;
    return $protocol;
  }
  
}

class sfContext {
  
  public static function getInstance()
  {
    return new sfContext();
  }
  
  public function getRequest()
  {
    return new sfRequest();
  }  
}

$t->diag('->isRedirectUrlValid()');

$urls = array(
  'http://localhost/foobar.php/hello?ahoj' => true,
  // ssl version of the site
  'https://localhost/foobar.php/hello?ahoj' => true,
  'https://hack.eu/send-money/' => false,
  '192.168.37.122' => false,
);

foreach($urls as $url => $expected)
{
  $t->is(sfSecurity::isRedirectUrlValid($url), $expected, '->isRedirectUrlValid() validates redirect url correctly.');
}

$t->diag('->isIpInWhitelist()');

$ip        = '10.0.0.1';
$whitelist = array();

$t->isa_ok(sfSecurity::isIpInWhitelist($ip, $whitelist), 'boolean', '->isInWhitelist() returns boolean value');

$whitelist = array(
  '10.0.0.1'
);

$t->is(sfSecurity::isIpInWhitelist($ip, $whitelist), true, '->isInWhitelist() returns true if whole IP matches');

$whitelist = array(
  '10.0.0.*'
);

$t->is(sfSecurity::isIpInWhitelist($ip, $whitelist), true, '->isInWhitelist() returns true if last segment of IP has wildcard');

$whitelist = array(
  '10.0.*'
);

$t->is(sfSecurity::isIpInWhitelist($ip, $whitelist), true, '->isInWhitelist() returns true if has wildcard');

// check if it is CIDR format
$whitelist = array(
  '10.0.0.0/8',
);

$t->is(sfSecurity::isIpInWhitelist($ip, $whitelist), true, '->isInWhitelist() returns true if has wildcard');

$t->diag('->isIpValid()');

$ip = '88.86.106.74';

$t->is(sfSecurity::isIpValid($ip), true, '->isIpValid() works ok');
