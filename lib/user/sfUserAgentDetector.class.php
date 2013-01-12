<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfUserAgentDetector is a simple user agent detector, which can guess
 * what browser (user agent) the user uses. 
 * 
 * Suports most common browsers and bots.
 *
 * @package    Sift
 * @subpackage user
 * @author     Thibault Duplessis <thibault.duplessis at gmail dot com>
 * @license    MIT License 
 */
class sfUserAgentDetector {

  protected $userAgent = '';
  protected $name = null;
  protected $version = null;
  protected $mobileName = null;
  protected $isBot = false;
  protected $isMobile = false;

  /**
   * The list of known browsers
   * 
   * @var array
   */
  protected $knownBrowsers = array(
      'msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror',
      'gecko', 'chrome'
  );

  /**
   * The list of browser aliases
   * 
   * @var array
   */
  protected $browserAliases = array(
      'shiretoko' => 'firefox',
      'namoroka' => 'firefox',
      'shredder' => 'firefox',
      'minefield' => 'firefox',
      'granparadiso' => 'firefox'
  );

  /**
   * The list of known bots
   * 
   * @var array
   */
  protected $knownBots = array(
      //            seznam.cz   centrum.cz
      'googlebot', 'seznambot', 'holmes', 'yahoo', 'slurp', 'msn', 'msnbot', 'yandex',
      'altavista', 'seznam', // seznam screenshot-generator
      'ia_archiver', 'harvest', 'crawl', 'bot', 'jeeves', 'spider', 'robot',
      'krawl', 'curl', 'wget', 'libwww-perl', 'metager', 'grub', 'netcraft',
      'urllib', 'robozilla', 'java', 'NetSprint', 'PPhpDig', 'RRC', 'WebStripper',
      'analyzer', 'arachnofilia', 'ask jeeves', 'aspseek', 'baiduspider', 'check',
      'gigabaz', 'gulliver', 'infoNavirobot', 'infoseek', 'inktomi', 'job crawler',
      'netmechanic', 'netoskop', 'nomad', 'openfind', 'roamer', 'rover', 'scooter',
      'search', 'siphon', 'sweep', 'teomaagent', 'validator', 'walker', 'wisenutbot',
      'FuntKlakow'
  );

  /**
   * The list of user agents which are considered mobile agents
   *  
   * @see http://www.zytrax.com/tech/web/mobile_ids.html
   */
  protected $knownMobiles = array(
      'iphone', 'ipad', 'android', 'blackberry',
      'windows phone', 'window mobile', 'iemobile',
      'smartphone', 'symbian', 'opera mini', 'nokia',
      'opera mobi'
  );

  /**
   * Tries to guess the agent
   * 
   * @param string $userAgent
   * @return array 
   */
  public static function guess($userAgent)
  {
    $detector = new sfUserAgentDetector();
    return $detector->execute($userAgent);
  }

  /**
   * Guesses the agent name and version from $agent string.
   * 
   * @param string $userAgent
   * @return string 
   */
  public function execute($userAgent)
  {
    $this->userAgent = strtr(strtolower($userAgent), $this->getBrowserAliases());

    $this->name = $this->version = $this->mobileName = null;
    $this->isBot = $this->isMobile = false;

    $this->guessFast();
    $this->fixGoogleChrome();
    $this->fixSafariVersion();

    return array(
        'name' => $this->name,
        'version' => $this->version,
        'is_bot' => $this->isBot,
        'is_mobile' => $this->isMobile,
        'mobile_name' => $this->mobileName
    );
  }

  /**
   * This method does the work of detecting the name, version and other stuff from
   * the user agent string
   * 
   */
  protected function guessFast()
  {
    // Clean up agent and build regex that matches phrases for known browsers
    // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
    // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
    $pattern = '#(' . join('|', $this->getKnownBrowsers()) . ')[/ ]+([0-9]+(?:\.[0-9]+)?)#';

    // Find all phrases (or return empty array if none found)
    if(preg_match_all($pattern, $this->userAgent, $matches))
    {
      // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
      // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
      // in the UA).  That's usually the most correct.
      $i = count($matches[1]) - 1;

      if(isset($matches[1][$i]))
      {
        $this->name = $matches[1][$i];
      }
      if(isset($matches[2][$i]))
      {
        $this->version = $matches[2][$i];
      }
    }

    // search bot
    if(preg_match('#(' . implode($this->getKnownBots(), ')|(') . ')#i', $this->userAgent, $matches))
    {
      $this->isBot = true;
      $this->name = $matches[0];
      // FIXME: try to detect bot version?      
    }
    // mobile device
    elseif(preg_match('#(' . implode($this->getKnownMobiles(), ')|(') . ')#i', $this->userAgent, $matches))
    {
      $this->isMobile = true;
      $this->mobileName = $matches[0];
    }
  }

  /**
   * Fixes google chrome name
   * 
   * @return void
   */
  protected function fixGoogleChrome()
  {
    // Google chrome has a safari like signature
    if('safari' === $this->name && strpos($this->userAgent, 'chrome/'))
    {
      $this->name = 'chrome';
      $this->version = preg_replace('|.+chrome/([0-9]+(?:\.[0-9]+)?).+|', '$1', $this->userAgent);
    }
  }

  /**
   * Fixes safari name 
   * 
   * @return void
   */
  protected function fixSafariVersion()
  {
    // Safari version is not encoded "normally"
    if('safari' === $this->name && strpos($this->userAgent, ' version/'))
    {
      $this->version = preg_replace('|.+\sversion/([0-9]+(?:\.[0-9]+)?).+|', '$1', $this->userAgent);
    }
  }

  /**
   * Returns browser aliases
   * 
   * @return array
   */
  protected function getBrowserAliases()
  {
    return $this->browserAliases;
  }

  /**
   * Returns the list of known browsers
   * 
   * @return array
   */
  protected function getKnownBrowsers()
  {
    return $this->knownBrowsers;
  }

  /**
   * Returns the list of known bots
   * 
   * @return array
   */
  protected function getKnownBots()
  {
    return $this->knownBots;
  }

  /**
   * Returns the list of known mobile devices
   * 
   * @return array
   */
  protected function getKnownMobiles()
  {
    return $this->knownMobiles;
  }

}
