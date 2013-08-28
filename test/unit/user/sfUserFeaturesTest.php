<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(16, new lime_output_color());

class sfTestUser extends sfUser {

  protected $agent;

  public function setUserAgent($a)
  {
    $this->getAttributeHolder()->remove('browser_guessed', self::ATTRIBUTE_NAMESPACE);
    $this->agent = $a;
  }

  public function getUserAgent()
  {
    return $this->agent;
  }
}

$sessionPath = sys_get_temp_dir() . '/sessions_' . rand(11111, 99999);
$storage = new sfSessionTestStorage(array('session_path' => $sessionPath));

$user = new sfTestUser(new sfEventDispatcher(), $storage, new sfWebRequest());

$t->diag('->isBot()');

$t->isa_ok($user->isBot(), 'boolean', 'isBot() returns boolean value');

$non_bots = array(
  'Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)'
);

foreach($non_bots as $k => $non_bot)
{
  $user->setUserAgent($non_bot);
  $t->is($user->isBot(), false, 'isBot() returns correct result web bot');
}

$t->is($user->isBot(), false, 'isBot() returns correct result for standard browser');

$bots = array(
  'SeznamBot/3.0-beta (+http://fulltext.sblog.cz/)',
  'SeznamBot/2.0 (+http://fulltext.sblog.cz/robot/)',
  'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
  'Mozilla/5.0 (compatible; DotBot/1.1; http://www.dotnetdotcom.org/, crawler@dotnetdotcom.org)',
  'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
  'Mozilla/4.0 (CMS Crawler: http://www.cmscrawler.com)'
);

foreach($bots as $k => $bot)
{
  $user->setUserAgent($bot);
  $t->is($user->isBot(), true, 'isBot() returns correct result for web bot');
}

$mobiles = array(
  'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; es-es) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405'  ,
  'Mozilla/5.0 (iPhone; U; XXXXX like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/241 Safari/419.3/)',
  'Mozilla/5.0 (Linux; U; Android 1.1; en-gb; dream) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2 – G1 Phone',
  'Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, Like Gecko) Version/6.0.0.141 Mobile Safari/534.1+',
  'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; HTC_Touch_Diamond2_T5353; Windows Phone 6.5)',
  'Modzilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11) 480x640; XV6850; Window Mobile 6.1 Professional;',
  'HTC-P4600/1.2 Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11) UP.Link/6.3.1.17.0'
);

$t->diag('->isMobile()');

foreach($mobiles as $k => $mobile)
{
  $user->setUserAgent($mobile);
  $t->is($user->isMobile(), true, 'isMobile() returns correct result for mobile device');
}
