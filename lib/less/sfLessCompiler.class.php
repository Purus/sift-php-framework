<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load vendor library
require_once dirname(__FILE__) . '/../vendor/lessphp/lessc.inc.php';

/**
 * sfLessCompiler compiles .less files. It extends lessc vendor library.
 * LESS css compiler, adapted from http://lesscss.org
 *
 * @package    Sift
 * @subpackage less
 * */
class sfLessCompiler extends lessc {

  public $importDir = array();
  static $instance;

  /**
   * Name for cache files
   *
   * @var string
   */
  protected $cacheName = '%s.min.css';

  /**
   * Debug mode?
   *
   * @var boolean
   */
  protected $debugMode = false;

  /**
   * Returns singleton instance of sfLessCompiler
   *
   * @return sfLessCompiler
   */
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Constructs the compiler
   */
  public function __construct()
  {
    parent::__construct();

    $this->construct();
  }

  protected function getRelativeUrlRoot()
  {
    return sfContext::getInstance()->getRequest()->getRelativeUrlRoot();
  }

  /**
   * Assigns variables after object construction
   *
   */
  public function construct()
  {
    $rootPath = sfConfig::get('sf_web_dir') . '/css';
    $componentsPath = sfConfig::get('sf_less_components_path', sfConfig::get('sf_web_dir') . '/css/_');
    $imagesPath = sprintf('%s/images', $this->getRelativeUrlRoot());
    $fontsPath = sprintf('%s/files/fonts', $this->getRelativeUrlRoot());
    $variablesPath = sfConfig::get('sf_less_variables_path', '_themes/default');

    $siftDataDir = sfConfig::get('sf_sift_data_dir') . '/web/sf/css';
    $siftWebDir = sfConfig::get('sf_sift_web_dir');

    // initial variables
    $variables = array(
        '@root_path' => sprintf('"%s"', $rootPath),
        '@components_path' => sprintf('"%s"', $componentsPath),
        '@images_path' => sprintf('"%s"', $imagesPath),
        '@fonts_path' => sprintf('"%s"', $fontsPath),
        '@variables_path' => sprintf('"%s"', $variablesPath),
        // base CSS/Less components
        '@sf_sift_base_components_dir' => sprintf('"%s"', $siftDataDir),
        // directories accessible via web
        '@sf_sift_web_dir' => sprintf('"%s"', $siftWebDir)
    );

    // pass thru event system
    $variables = sfCore::filterByEventListeners($variables, 'less.compile.variables');
    $this->setVariables($variables);

    $importDir = array(
        str_replace(DIRECTORY_SEPARATOR, '/', sfConfig::get('sf_less_import_path', sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'css')),
        str_replace(DIRECTORY_SEPARATOR, '/', $siftDataDir)
    );

    $importDir = sfCore::filterByEventListeners($importDir, 'less.compile.import_dir');
    foreach($importDir as $dir)
    {
      $this->addImportDir($dir);
    }

    if(sfConfig::get('sf_web_debug'))
    {
      $this->setCacheName('%s.css');
      // preserve comments in the stylesheets
      // $this->setPreserveComments(true);
      $this->setFormatter('classic');
      $this->setDebugMode(true);
    }
    else
    {
      $this->setFormatter('compressed');
      $this->setCacheName('%s.min.css');
    }
  }

  /**
   * Sets debug mode
   *
   * @param boolean $flag
   * @return sfLessCompiler
   */
  public function setDebugMode($flag)
  {
    $this->debugMode = (boolean) $flag;
    return $this;
  }

  /**
   * Returns debug mode
   *
   * @return boolean
   */
  public function getDebugMode()
  {
    return $this->debugMode;
  }

  /**
   * Sets name for generated images. Uses sprintf to replace %s by hash of the file.
   *
   * @param string $mask
   * @return sfLessCompiler
   */
  public function setCacheName($mask)
  {
    $this->cacheName = $mask;
    return $this;
  }

  /**
   * Returns cache name mask.
   *
   * @return string
   */
  public function getCacheName()
  {
    return $this->cacheName;
  }

  /**
   *
   * @param string $source
   */
  public function compileStylesheetIfNeeded($source)
  {
    // hexadecimal representation of the checksum you can either
    // use the "%x" formatter of sprintf() or printf() or the dechex()
    // conversion functions, both of these also take care of converting
    // the crc32() result to an unsigned integer.

    $cache = $this->getDebugMode() ?
            sprintf('%s.css', str_replace('/', '.', ltrim(str_replace('/css', '', $source), '/'))) :
            sprintf($this->getCacheName(), dechex(crc32($source)));

    // sift webdirectory
    if(strpos($source, sfConfig::get('sf_sift_web_dir')) !== false)
    {
      $part = str_replace(sfConfig::get('sf_sift_web_dir'), '', $source);
      $source = sfConfig::get('sf_web_dir') . sfConfig::get('sf_sift_web_dir') . $part;

      // file is not present in web dir, so we are using Alias to data directory
      if(!is_readable($source))
      {
        $source = sfConfig::get('sf_sift_data_dir') . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR .
                'sf' . DIRECTORY_SEPARATOR . $part;
      }
    }
    else
    {
      $source = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $source;
    }

    try
    {
      $this->compileIfNeeded($source, sfConfig::get('sf_web_dir') .
              DIRECTORY_SEPARATOR . 'cache' .
              DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $cache);
    }
    catch(Exception $e)
    {
      throw sfLessCompilerException::createFromException($e);
    }

    return sprintf('%s/cache/css/%s', $this->getRelativeUrlRoot(), $cache);
  }

  /**
   * Compiles file if needed.
   *
   * @param type $inputFile
   * @param type $outputFile
   * @return type
   */
  public function compileIfNeeded($inputFile, $outputFile)
  {
    $cacheDir = sfConfig::get('sf_cache_dir') . DIRECTORY_SEPARATOR . 'less_compile';

    if(!is_dir($cacheDir))
    {
      $current_umask = umask(0000);
      mkdir($cacheDir, 0777, true);
      umask($current_umask);
    }

    $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . basename($outputFile) . '.cache';

    if(file_exists($cacheFile))
    {
      $cache = unserialize(file_get_contents($cacheFile));
    }
    else
    {
      $cache = $inputFile;
    }

    $newCache = $this->cachedCompile($cache);

    if(!is_array($cache) || $newCache['updated'] > $cache['updated'])
    {
      file_put_contents($cacheFile, serialize($newCache));
      file_put_contents($outputFile, $newCache['compiled']);
    }

    return $newCache;
  }

  /**
   * Returns assigned variables to the parser
   *
   * @return array
   */
  public function getVariables()
  {
    return $this->registeredVars;
  }

  public function setImportDir($dirs)
  {
    $this->importDir = (array) $dirs;
  }

  public function addImportDir($dir)
  {
    $this->importDir[] = $dir;
    $this->importDir = array_unique($this->importDir);
  }

  public function compileFile($fname, $outFname = null)
  {
    if(!is_readable($fname))
    {
      throw new sfLessCompilerException(sprintf('File "%s" does not exist or is not readable', $fname));
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->info(sprintf('{sfLessCompiler} Compiling file "%s"', $fname));
    }

    $pi = pathinfo($fname);

    // prepend current directory
    array_unshift($this->importDir, $pi['dirname']);

    $this->allParsedFiles = array();
    $this->addParsedFile($fname);

    $out = $this->compile(file_get_contents($fname), $fname);

    if($outFname !== null)
    {
      return file_put_contents($outFname, $out);
    }

    return $out;
  }

  /**
   * Find the real file for import of $url
   *
   * @param string $url
   * @return null|string
   */
  protected function findImport($url)
  {
    // this is an url
    if(strpos($url, '//') !== false)
    {
      return null;
    }

    $url = preg_replace('/.less$/i', '', $url);
    $url = sprintf(sprintf('%s.less', $url));

    // we have an absolute path to the less stylesheet
    if(sfToolkit::isPathAbsolute($url))
    {
      if(is_readable($url))
      {
        return $url;
      }
      return null;
    }

    foreach($this->importDir as $dir)
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->log(sprintf('{sfLessCompiler} Looking up for "%s" in "%s"', $url, $dir));
      }

      $dir = rtrim($dir, DIRECTORY_SEPARATOR);
      $file = $dir . DIRECTORY_SEPARATOR . $url;

      if($this->fileExists($file))
      {
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->log(sprintf('{sfLessCompiler} Found "%s"', $file));
        }
        return $file;
      }
    }

    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->err(sprintf('{sfLessCompiler} No import for file found for "%s"', $url));
    }

    return null;
  }

}
