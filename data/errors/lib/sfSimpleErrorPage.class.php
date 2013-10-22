<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSimpleErrorPage represents an production style error page
 *
 * @package Sift
 * @subpackage error
 *
 */
class sfSimpleErrorPage {

  /**
   * The user language
   *
   * @var string
   */
  protected $language = 'en';

  /**
   * Array of supported languages
   *
   * @var array
   */
  protected $supportedLanguages = array(
    'cs', 'en'
  );

  /**
   * The page
   *
   * @var string
   */
  protected $page;

  /**
   * Type of the response. Html or Json
   * @var string
   */
  protected $type;

  /**
   * Constructor
   *
   * @param string $page The error page
   */
  public function __construct($page, $type = 'html')
  {
    $this->page = $page;
    $this->type = $type;
    $this->dataDir = realpath(dirname(__FILE__) . '/..');
    $this->language = $this->getPreferedLanguage($this->supportedLanguages);
  }

  /**
   * Renders the template
   *
   * @return string
   */
  public function render()
  {
    // extract for the template
    $i18n = $this->getTranslations();
    $header = array_shift($i18n);

    switch($this->type)
    {
      default:
      case 'html':
        $template = '_template.php';
      break;

      case 'json':
        $template = '_json.php';
      break;
    }

    self::renderTemplate(
      $this->dataDir . '/' . $template, array(
        'header' => $header,
        'i18n' => $i18n,
        'class' => $this->page
      )
    );
  }

  /**
   * Renders the template
   */
  public static function renderTemplate(/*$template, $variables = array()*/)
  {
    if(is_array(func_get_arg(1)))
    {
      extract(func_get_arg(1));
    }
    include func_get_arg(0);
  }

  /**
   * Return array of translations for the page
   *
   * @return array|false
   */
  public function getTranslations()
  {
    $file = ($this->dataDir . '/i18n/' . $this->page . '/' . $this->language . '.php');
    $translations = is_readable($file) ? include $file : array();

    $variables = $this->getVariables();
    foreach($translations as &$translation)
    {
      $translation = strtr($translation, $variables);
    }
    return $translations;
  }

  /**
   * Get preferred language by the user
   *
   * @param array $available_languages
   * @return string
   */
  public function getPreferedLanguage($available_languages)
  {
    // if $http_accept_language was left out, read it from the HTTP-Header
    $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';

    // standard  for HTTP_ACCEPT_LANGUAGE is defined under
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
    // pattern to find is therefore something like this:
    //    1#( language-range [ ";" "q" "=" qvalue ] )
    // where:
    //    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
    //    qvalue         = ( "0" [ "." 0*3DIGIT ] )
    //            | ( "1" [ "." 0*3("0") ] )
    preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
        "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i", $http_accept_language, $hits, PREG_SET_ORDER);

    // default language (in case of no hits) is the first in the array
    $bestlang = $available_languages[0];
    $bestqval = 0;

    foreach($hits as $arr)
    {
      // read data from the array of this hit
      $langprefix = strtolower($arr[1]);
      if(!empty($arr[3]))
      {
        $langrange = strtolower($arr[3]);
        $language = $langprefix . "-" . $langrange;
      }
      else
      {
        $language = $langprefix;
      }

      $qvalue = 1.0;
      if(!empty($arr[5]))
      {
        $qvalue = floatval($arr[5]);
      }

      // find q-maximal language
      if(in_array($language, $available_languages) && ($qvalue > $bestqval))
      {
        $bestlang = $language;
        $bestqval = $qvalue;
      }
      // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
      else if(in_array($langprefix, $available_languages) && (($qvalue * 0.9) > $bestqval))
      {
        $bestlang = $langprefix;
        $bestqval = $qvalue * 0.9;
      }
    }
    return $bestlang;
  }

  /**
   * Return array of variables which will be replaced in the messages
   *
   * @return array
   */
  public function getVariables()
  {
    return array(
      '%email%' => $this->getWebmasterEmail()
    );
  }

  /**
   * Returns webmaster email
   *
   * @return string
   */
  public function getWebmasterEmail()
  {
    if(!class_exists('sfConfig', false))
    {
      return;
    }

    $email = sfConfig::get('app_webmaster_email', sfConfig::get('sf_webmaster_email', ''));
    if(is_array($email))
    {
      return $email[0];
    }
    return $email;
  }

}