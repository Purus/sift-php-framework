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
class sfI18n extends sfConfigurable {

  /**
   * Regular expression to match module and catalogue from domain
   *
   * @var string
   */
  public static $moduleCatalogueRegexp = '/^([a-zA-Z_-]+)\/([a-zA-Z_-]+)$/';

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

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'charset' => 'UTF-8',
    'debug' => false,
    'cache' => false,
    'cache_dir' => '',
    'translator_mode' => false,
    'untranslated_prefix' => '[T]',
    'untranslated_suffix' => '[/T]',
    'source_type' => 'gettext',
    // sources which are registered upon object creation
    'sources' => array()
  );

  /**
   * Event dispatched object
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Current module in the application.
   *
   * @var string
   * @see listenToControllerChangeActionEvent()
   */
  protected $moduleName;

  /**
   * Call cache
   *
   * @var array
   */
  protected $callCache = array();

  /**
   * Constructs the I18n
   *
   * @param sfContext $context Context instance
   * @param array $options
   * @inject context
   */
  public function __construct(sfContext $context, $options = array())
  {
    $this->context = $context;
    $this->culture = $context->getUser()->getCulture();

    // This should be taken from context
    $this->context->getEventDispatcher()->connect('user.change_culture',
      array($this, 'listenToUserChangeCultureEvent')
    );

    // calls setup()
    parent::__construct($options);
  }

  /**
   * Setup the object
   *
   */
  public function setup()
  {
    if($this->getOption('cache') && !$this->getOption('cache_dir'))
    {
      throw new InvalidArgumentException('Cache directory option "cache_dir" is missing');
    }

    foreach($this->getOption('sources', array()) as $name => $source)
    {
      if(!$source instanceof sfII18nMessageSource)
      {
        // skip disabled sources
        if(isset($source['enabled']) && !$source['enabled'])
        {
          continue;
        }

        if(!isset($source['source']))
        {
          throw new InvalidArgumentException(sprintf('Given source "%s" is missing "source" key', $name));
        }

        $arguments = array();
        if(isset($source['arguments']))
        {
          $arguments = $source['arguments'];
        }

        $type = isset($source['type']) ? $source['type'] : $this->getOption('source_type');

        if(isset($source['class']))
        {
          $type = $source['class'];
        }

        $source = $this->createMessageSource($type, $source['source'], $arguments);
      }

      $this->appendMessageSource($source);
    }
  }

  /**
   * Is running in translator mode?
   *
   * @return boolean
   */
  public function isInTranslatorMode()
  {
    return $this->getOption('translator_mode');
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
    $culture = $culture ? $culture : $this->getCulture();

    if($this->getOption('debug'))
    {
      $timer = sfTimerManager::getTimer('Translation');
    }

    list($source, $catalogue) = $this->prepareSources($catalogue, $string);

    $translated = false;
    if($source)
    {
      $source['source']->setCulture($culture);
      $translated = $source['formatter']->formatExists($string, $args, $catalogue);
    }

    // we are in learning mode, catch what we translated
    if($this->getOption('translator_mode'))
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
        // don'ty try the same source again
        if($_source == $source)
        {
          continue;
        }

        // we force to given culture
        $_source['source']->setCulture($culture);

        try
        {
          $translated = $_source['formatter']->formatExists($string, $args, $catalogue);
        }
        catch(sfException $e)
        {
          if($this->getOption('debug'))
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

    if(isset($timer))
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
      $hash      = sprintf('%s_%s', $directory, $catalogue);
    }
    // we have module/catalogue pair
    elseif(strpos($catalogue, '/') !== false
        && preg_match(self::$moduleCatalogueRegexp, $catalogue, $matches))
    {
      $moduleName = $matches[1];
      $catalogue  = $matches[2];
      $directory  = sfLoader::getI18NDirs($moduleName);
      $hash       = sprintf('%s_%s', join('', $directory), $catalogue);
    }
    // we have any module
    elseif($moduleName = $this->context->getModuleName())
    {
      $directory = sfLoader::getI18NDirs($moduleName);
      // current module is taken
      $hash = sprintf('%s_%s', join('', $directory), $catalogue);
    }

    // nothing found, unknown directory
    if(!$directory)
    {
      return array(false, false);
    }

    if(!isset($this->sources[$hash]))
    {
      // we have directories
      if(is_array($directory))
      {
        // we will create the aggregate source only if there are more sources
        if(count($directory) > 1)
        {
          $sources = array();
          foreach($directory as $dir)
          {
            $sources[] = $this->createMessageSource($this->getOption('source_type'), $dir);
          }
          // we have to create aggregate source
          $source = new sfI18nMessageSourceAggregate($sources);
        }
        else
        {
          $source = $this->createMessageSource($this->getOption('source_type'), $directory[0]);
        }
      }
      else
      {
        $source = $this->createMessageSource($this->getOption('source_type'), $directory);
      }

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
      $source['formatter']->reset();
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
   * Resets the call cache
   *
   * @return sfI18n
   */
  public function resetCache()
  {
    $this->callCache = array();
    return $this;
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
      'source' => &$source,
      'formatter' => $this->createMessageFormatter($source)
    );

    return $this;
  }

  /**
   * Creates a message source object. Creates a message source and assigns cache object
   * to this instance.
   *
   * @param string $type
   * @param string $source
   * @param array $arguments Arguments for the
   * @return sfII18nMessageSource
   */
  public function createMessageSource($type, $source, $arguments = array())
  {
    $cache = false;

    if($this->getOption('cache'))
    {
      // cache directory
      // FIXME: is this unique enough?
      // http://programmers.stackexchange.com/questions/49550/which-hashing-algorithm-is-best-for-uniqueness-and-speed
      $cacheDir = dechex(crc32($source . $type . $this->getCulture() . serialize($arguments)));

      $cache = new sfFileCache(array(
        'cache_dir' => $this->getOption('cache_dir').'/'.$cacheDir
      ));
    }

    $source = sfI18nMessageSource::factory($type, $source, $arguments);

    if($cache)
    {
      $source->setCache($cache);
    }

    return $source;
  }

  /**
   * Creates message formatter
   *
   * @param sfII18NMessageSource $source
   * @return sfI18nMessageFormatter
   */
  public function createMessageFormatter(sfII18NMessageSource $source)
  {
    $messageFormat = new sfI18nMessageFormatter($source, $this->getOption('charset'));
    if($this->getOption('debug'))
    {
      $messageFormat->setUntranslatedPS(array(
        $this->getOption('untranslated_prefix'),
        $this->getOption('untranslated_suffix'))
      );
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
   * Listens to "user.change_culture" event
   *
   * @param sfEvent $event
   */
  public function listenToUserChangeCultureEvent(sfEvent $event)
  {
    $this->setCulture($event['culture']);
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
    list($d, $m, $y) = self::getDate($date, $culture);
    list($hour, $minute) = self::getTime($date, $culture);
    return mktime($hour, $minute, 0, $m, $d, $y);
  }

  /**
   * Return a d, m and y from a date formatted with a given culture
   *
   * @param integer $date
   * @param string $culture
   * @return int|null
   */
  public static function getDate($date, $culture = null)
  {
    if(!$date)
    {
      return 0;
    }

    $dateFormatInfo = sfI18nDateTimeFormat::getInstance($culture);
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
  public static function getTime($time, $culture = null)
  {
    if(!$time)
    {
      return;
    }

    $timeFormatInfo = sfI18nDateTimeFormat::getInstance($culture);
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