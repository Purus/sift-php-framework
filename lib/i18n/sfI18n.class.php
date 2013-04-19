<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18n class provides methods for translating user interface.
 *
 * @package    Sift
 * @subpackage i18n
 */
class sfI18n {

  /**
   * Instance holder
   *
   * @var sfI18n
   */
  protected static $instance;

  /**
   * Array of sfII18nMessageSources
   *
   * @var array
   */
  protected $sources = array();

  /**
   * Culture
   *
   * @var string
   */
  protected $culture;

  /**
   * sfContext holder
   *
   * @var sfContext
   */
  protected $context;

  /**
   * Array of requested translations
   *
   * @var array
   */
  protected $requestedTranslations = array();

  public function initialize(sfContext $context, $options = array())
  {
    $this->context = $context;
    $this->culture = $context->getUser()->getCulture();

    // FIXME: load fallback sources as last instance translators
    // for: core sift, forms, this should be passed as options

    $this->appendMessageSource($this->createMessageSource(sfConfig::get('sf_i18n_source'),
                               sfConfig::get('sf_app_i18n_dir')));
  }

  /**
   * Returns single instance of this class
   *
   * @return sfI18N instance
   */
  public static function getInstance()
  {
    if(!isset(self::$instance))
    {
      $class = __CLASS__;
      self::$instance = new $class();
    }
    return self::$instance;
  }

  /**
   * Translates given string with arguments using i18n catalogue(s).
   *
   * Translation catalogue can be specified as:
   *  - "myModule/catalogue" pair,
   *  - "catalogueName" - current module will be searched
   *  - "/var/www/website/data/catalogue" absolute path
   *
   * @param string $string
   * @param array $args
   * @param string $catalogue
   * @param string $culture Force to given culture
   * @return string
   */
  public function __($string, $args = array(), $catalogue = 'messages', $culture = null)
  {
    if(sfConfig::get('sf_debug'))
    {
      $timer = sfTimerManager::getTimer('Translation');
    }

    list($source, $catalogue) = $this->prepareSources($catalogue);

    $translated = false;

    if($source)
    {
      $source['source']->setCulture($culture ? $culture : $this->getCulture());
      $translated = $source['formatter']->formatExists($string, $args, $catalogue);
    }

    // we are in learning mode, catch what we translated
    if(sfConfig::get('sf_i18n_learning_mode'))
    {
      $this->requestedTranslations[$catalogue][$string] = array(
        'source'        => $source['source'] ? $source['source']->getOriginalSource() : false,
        'target'        => $translated ? $translated : '',
        'params'        => is_array($args) ? implode(', ', array_keys($args)) : '',
        'is_translated' => (boolean)$translated
      );
    }

    if(!$translated)
    {
      // loop all sources and find the translation
      foreach($this->sources as $_source)
      {
        if($_source == $source)
        {
          continue;
        }

        // we force to given culture
        $_source['source']->setCulture($culture ? $culture : $this->getCulture());

        try
        {
          $translated = $_source['formatter']->formatExists($string, $args, $catalogue);
        }
        catch(sfException $e)
        {
          if(sfConfig::get('sf_debug'))
          {
            throw $e;
          }
        }

        if($translated)
        {
          break;
        }

      }
    }

    if(sfConfig::get('sf_debug'))
    {
      $timer->addTime();
    }

    return $translated ? $translated : strtr($string, (array)$args);
  }

  /**
   * Prepare source for given $catalogue
   *
   * @param string $catalogue
   * @return boolean
   */
  protected function prepareSources($catalogue)
  {
    $catalogue = sfToolkit::replaceConstants($catalogue);

    $directory = false;
    // we have an absolute path for the catalogue
    if(sfToolkit::isPathAbsolute($catalogue))
    {
      $directory = dirname($catalogue);
      $catalogue = basename($catalogue);
      $hash      = sprintf('%s_%s_%s', $directory, $catalogue, $this->getCulture());
    }
    // we have module/catalogue pair
    elseif(strpos($catalogue, '/') !== false
        && preg_match('/^([a-zA-Z]+)\/([a-zA-Z]+)$/i', $catalogue, $matches))
    {
      $moduleName = $matches[1];
      $catalogue  = $matches[2];
      $directory  = sfLoader::getI18NDir($moduleName);
      $hash       = sprintf('%s_%s_%s', $moduleName, $catalogue, $this->getCulture());
    }
    else
    {
      $moduleName = $this->context->getModuleName();
      $directory = sfLoader::getI18NDir($moduleName);
      // current module is taken
      $hash = sprintf('%s_%s_%s', $moduleName, $catalogue, $this->getCulture());
    }

    // nothing found, unknown directory
    if(!$directory)
    {
      return false;
    }

    if(!isset($this->sources[$hash]))
    {
      $type = sfConfig::get('sf_i18n_source');
      $source = $this->createMessageSource($type, $directory);

      $cache = new sfFileCache(array(
        'cache_dir' => sfConfig::get('sf_cache_dir') . '/i18n/' . dechex(crc32($hash))
      ));

      $source->setCache($cache);

      $this->sources[$hash] = array(
        'source'    => &$source,
        'formatter' => $this->createMessageFormatter($source)
      );
    }

    return array(&$this->sources[$hash], $catalogue);
  }

  /**
   * Returns an array of requested translations
   *
   * @return array
   */
  public function getRequestedTranslations()
  {
    return $this->requestedTranslations;
  }

  /**
   * Converts strings to UTF-8 via iconv. NB, the result may not by UTF-8 if the conversion failed.
   *
   * @param  string $string string to convert to UTF-8
   * @param  string $from   current encoding
   *
   * @return string UTF-8 encoded string, original string if iconv failed.
   */
  public static function i18n2Utf8($string, $from)
  {
    $from = strtoupper($from);
    if($from != 'UTF-8')
    {
      $s = iconv($from, 'UTF-8', $string);  // to UTF-8

      return $s !== false ? $s : $string; // it could return false
    }

    return $string;
  }

  /**
   * Converts UTF-8 strings to a different encoding. NB.
   * The result may not have been encoded if iconv fails.
   *
   * @param  string $string  the UTF-8 string for conversion
   * @param  string $to      new encoding
   *
   * @return string encoded string.
   */
  public static function i18n2Encoding($string, $to)
  {
    $to = strtoupper($to);
    if($to != 'UTF-8')
    {
      $s = iconv('UTF-8', $to, $string);
      return $s !== false ? $s : $string;
    }
    return $string;
  }

  /**
   * Sets culture to all message sources
   *
   * @param string $culture current user culture
   */
  public function setCulture($culture)
  {
    $this->culture = $culture;

    // change user locale for formatting, collation, and internal error messages
    setlocale(LC_ALL, 'en_US.utf8', 'en_US.UTF8', 'en_US.utf-8', 'en_US.UTF-8');
    setlocale(LC_COLLATE, $culture . '.utf8', $culture . '.UTF8', $culture . '.utf-8', $culture . '.UTF-8');
    setlocale(LC_CTYPE, $culture . '.utf8', $culture . '.UTF8', $culture . '.utf-8', $culture . '.UTF-8');
    setlocale(LC_MONETARY, $culture . '.utf8', $culture . '.UTF8', $culture . '.utf-8', $culture . '.UTF-8');
    setlocale(LC_TIME, $culture . '.utf8', $culture . '.UTF8', $culture . '.utf-8', $culture . '.UTF-8');

    // loop all catalogues and switch culture
    foreach($this->sources as $id => &$source)
    {
      $source['source']->setCulture($culture);
    }

    return $this;
  }

  /**
   * Returns a culture
   *
   * @return string
   */
  public function getCulture()
  {
    return $this->culture;
  }

  /**
   * Translates the string. This is just an alias for __()
   * @param string $str
   * @param array $args
   * @param string $catalogue
   * @params string $culture Force culture?
   * @return string
   * @see __
   */
  public function translate($str, $args = array(), $catalogue = 'messages', $culture = null)
  {
    return $this->__($str, $args, $catalogue, $culture);
  }

  /**
   * Appends a $source to the translation chain
   *
   * @param sfII18nMessageSource $source
   * @return sfI18N
   */
  public function appendMessageSource(sfII18nMessageSource $source)
  {
    $this->sources[] = array(
      'source' => $source,
      'formatter' => $this->createMessageFormatter($source)
    );

    return $this;
  }

  /**
   * Creates a message source object. This is an alias for
   * sfI18nMessageSource::factory() method.
   *
   * @param string $type
   * @param string $source
   * @return sfII18nMessageSource
   */
  public function createMessageSource($type, $source)
  {
    return sfI18nMessageSource::factory($type, $source);
  }

  /**
   * Creates message formatter
   *
   * @param sfII18NMessageSource $source
   * @return sfI18nMessageFormatter
   */
  public function createMessageFormatter(sfII18NMessageSource $source)
  {
    $messageFormat = new sfI18nMessageFormatter($source, sfConfig::get('sf_charset'));
    if(sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug'))
    {
      $messageFormat->setUntranslatedPS(array(
          sfConfig::get('sf_i18n_untranslated_prefix'),
          sfConfig::get('sf_i18n_untranslated_suffix')));
    }
    return $messageFormat;
  }

  /**
   * Prepends a $source to the translation chain
   *
   * @param sfIMessageSource $source
   * @return sfI18N
   */
  public function prependMessageSource(sfII18nMessageSource $source)
  {
    $this->sources = array_reverse($this->sources, true);
    $this->sources[] = array(
      'source' => $source,
      'formatter' => $this->createMessageFormatter($source)
    );
    $this->sources = array_reverse($this->sources, true);
    return $this;
  }

  /**
   * Adds a source to the translation chain
   *
   * @param sfII18nMessageSource $source
   * @param boolean $prepend Prepend to the translation chain?
   * @return sfI18N
   */
  public function addMessageSource(sfII18nMessageSource $source, $prepend = false)
  {
    return $prepend ? $this->prependMessageSource($source) : $this->appendMessageSource($source);
  }

  /**
   * Is the given $source registered for translations?
   *
   * @param sfII18nMessageSource $source
   * @return boolean
   */
  public function hasMessageSource(sfII18nMessageSource $source)
  {
    foreach($this->sources as $_source)
    {
      if($_source['source'] == $source)
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Returns country name for given ISO code
   *
   * @param string $iso
   * @param string $culture
   * @return string
   */
  public function getCountry($iso, $culture = null)
  {
    $countries = $this->getCultureInstance($culture)->getCountries();
    return (array_key_exists($iso, $countries)) ? $countries[$iso] : '';
  }

  /**
   * Returns sfCulture instance for given culture. Or current culture if no $culture parameter passed.
   *
   * @param string $culture Culture
   * @return sfCulture
   */
  public function getCultureInstance($culture = null)
  {
    return sfCulture::getInstance($culture ? $culture : $this->getCulture());
  }

  /**
   * Returns culture native name
   *
   * @param string $culture
   * @return string
   */
  public function getNativeName($culture = null)
  {
    return $this->getCultureInstance($culture)->getNativeName();
  }

  /**
   * Return timestamp from a date formatted with a given culture
   *
   * @param mixed $date
   * @param string $culture
   * @return integer
   */
  public function getTimestamp($date, $culture = null)
  {
    list($d, $m, $y) = $this->getDate($date, $culture);
    list($hour, $minute) = $this->getTime($date, $culture);
    return mktime($hour, $minute, 0, $m, $d, $y);
  }

  /**
   * Return a d, m and y from a date formatted with a given culture
   *
   * @param integer $date
   * @param string $culture
   * @return int|null
   */
  public function getDate($date, $culture = null)
  {
    if(!$date)
    {
      return 0;
    }

    $dateFormatInfo = sfI18nDateTimeFormat::getInstance($culture ? $culture : $this->getCulture());
    $dateFormat = $dateFormatInfo->getShortDatePattern();

    // We construct the regexp based on date format
    $dateRegexp = preg_replace('/[dmy]+/i', '(\d+)', $dateFormat);

    // We parse date format to see where things are (m, d, y)
    $a = array(
      'd' => strpos($dateFormat, 'd'),
      'm' => strpos($dateFormat, 'M'),
      'y' => strpos($dateFormat, 'y'),
    );

    $tmp = array_flip($a);
    ksort($tmp);
    $i = 0;
    $c = array();
    foreach($tmp as $value)
    {
      $c[++$i] = $value;
    }

    $datePositions = array_flip($c);
    // We find all elements
    if(preg_match("~$dateRegexp~", $date, $matches))
    {
      // We get matching timestamp
      return array($matches[$datePositions['d']], $matches[$datePositions['m']], $matches[$datePositions['y']]);
    }
    else
    {
      return null;
    }
  }

  /**
   * Returns the hour, minute from a date formatted with a given culture.
   *
   * @param  string  $date    The formatted date as string
   * @param  string  $culture The culture
   *
   * @return array   An array with the hour and minute
   */
  public function getTime($time, $culture = null)
  {
    if(!$time)
    {
      return 0;
    }

    $timeFormatInfo = sfI18nDateTimeFormat::getInstance($culture ? $culture : $this->getCulture());
    $timeFormat = $timeFormatInfo->getShortTimePattern();

    // We construct the regexp based on time format
    $timeRegexp = preg_replace(array('/[^hm:]+/i', '/[hm]+/i'), array('', '(\d+)'), $timeFormat);

    // We parse time format to see where things are (h, m)
    $a = array(
      'h' => strpos($timeFormat, 'H') !== false ? strpos($timeFormat, 'H') : strpos($timeFormat, 'h'),
      'm' => strpos($timeFormat, 'm')
    );
    $tmp = array_flip($a);
    ksort($tmp);
    $i = 0;
    $c = array();

    foreach($tmp as $value)
    {
      $c[++$i] = $value;
    }

    $timePositions = array_flip($c);

    // We find all elements
    if(preg_match("~$timeRegexp~", $time, $matches))
    {
      // We get matching timestamp
      return array($matches[$timePositions['h']], $matches[$timePositions['m']]);
    }
    else
    {
      return null;
    }

  }

}
