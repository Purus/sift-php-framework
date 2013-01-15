<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/user/sfUserAgentDetector.class.php');

$tests = array();
// chrome
$tests['Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6'] =     
    array(
      'name' => 'chrome',
      'version' => '20.0',
      'is_bot' => false,        
      'is_mobile' => false,
      'mobile_name' => null        
);

// firefox
$tests['Mozilla/5.0 (Windows NT 5.1; rv:11.0) Gecko/20100101 Firefox/11.0'] =     
    array(
      'name' => 'firefox',
      'version' => '11.0', 
      'is_bot' => false,        
      'is_mobile' => false,  
      'mobile_name' => null        
);

// safari
$tests['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10'] =     
    array(
      'name' => 'safari',
      'version' => '5.1',
      'is_bot' => false,        
      'is_mobile' => false,
      'mobile_name' => null        
);
  

// bots 

$tests['SeznamBot/2.0 (+http://fulltext.sblog.cz/robot/)'] =     
    array(
      'name' => 'seznambot',
      'version' => null,
      'is_bot' => true,      
      'is_mobile' => false,
      'mobile_name' => null        
);


$tests['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'] =     
    array(
      'name' => 'googlebot',
      'version' => null,
      'is_bot' => true,     
      'is_mobile' => false,  
      'mobile_name' => null                
);


$tests['Googlebot-Image/1.0'] =     
    array(
      'name' => 'googlebot',
      'version' => null,
      'is_bot' => true,     
      'is_mobile' => false,  
      'mobile_name' => null
);

$tests['Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3'] =     
    array(
      'name' => 'safari',
      'version' => '3.0',
      'is_bot' => false,     
      'is_mobile' => true,  
      'mobile_name' => 'iphone'  
);

$tests['Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3'] =     
    array(
      'name' => 'safari',
      'version' => '419.3',
      'is_bot' => false,     
      'is_mobile' => true,  
      'mobile_name' => 'android'  
);

$tests['Mozilla/5.0 (Linux; U; Android 2.0.1; en-us; Droid Build/ESD56) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17'] =     
    array(
      'name' => 'safari',
      'version' => '4.0',
      'is_bot' => false,     
      'is_mobile' => true,  
      'mobile_name' => 'android'  
);


$tests['Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00'] =     
    array(
      'name' => 'opera',
      'version' => '9.80',
      'is_bot' => false,     
      'is_mobile' => false,  
      'mobile_name' => null  
);

// YAHOO

$tests['Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)'] =     
    array(
      'name' => 'yahoo',
      'version' => null,
      'is_bot' => true,     
      'is_mobile' => false,  
      'mobile_name' => null  
);
// IE

$tests['Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'] =     
    array(
      'name' => 'msie',
      'version' => '6.0',
      'is_bot' => false,     
      'is_mobile' => false,  
      'mobile_name' => null  
);

$tests['Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'] =     
    array(
      'name' => 'msie',
      'version' => '8.0',
      'is_bot' => false,     
      'is_mobile' => false,  
      'mobile_name' => null  
);

$tests['Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0'] =     
    array(
      'name' => 'msie',
      'version' => '10.6',
      'is_bot' => false,     
      'is_mobile' => false,  
      'mobile_name' => null  
);

$t = new lime_test(count($tests), new lime_output_color());
foreach($tests as $agent => $result)
{
  $agent = sfUserAgentDetector::guess($agent);
  $t->is($agent, $result, '->guess() guesses correctly');
}


