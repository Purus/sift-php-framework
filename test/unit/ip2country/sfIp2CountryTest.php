<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(5);

// invalid database
try
{
  $i = new sfIp2CountryDriverGeoIp(array(
    'database' => dirname(__FILE__).'/ip2country.db',
  ));

  $t->fail('exception is thrown for invalid database');
}
catch(Exception $e)
{
  $t->pass('exception is thrown for invalid database');
}

$i = sfIp2Country::factory('GeoIp', array(
  'database' => $sf_sift_data_dir . '/data/ip2country.db',
));

$t->is($i->getCountryCode('77.75.76.3'), 'CZ', '->getCountryForIp() works ok for known ip');
$t->is($i->getCountryCode('23.22.24.199'), 'US', '->getCountryForIp() works ok for known ip for overseas');

// invalid ips
$t->is_deeply($i->getCountryCode('127.0.0.1'), null, '->getCountryForIp() works ok for invalid ip');
$t->is($i->getCountryCode('358.1.1.0'), null, '->getCountryForIp() works ok for invalid ip');
