<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores content in files.
 *
 * @package    Sift
 * @subpackage cache
 */
class sfFileCache extends sfCache {

  /**
   * Read data flag
   */
  const READ_DATA = 1;

  /**
   * Read timeout flag
   */
  const READ_TIMEOUT = 2;

  /**
   * Last modified flag
   */
  const READ_LAST_MODIFIED = 4;

  /**
   * Hash flag
   */
  const READ_HASH = 8;

  /**
   * Array of default options. Inherits also default options
   * from sfCache
   *
   * @var array
   */
  protected $defaultOptions = array(
    'suffix' => '.cache'
  );

  /**
   * Array of valid options. Inherits options from parent classes.
   *
   * @var array
   */
  protected $validOptions = array(
    'cache_dir',
    'suffix'
  );

  /**
   * Array of required options.
   *
   * @var array
   */
  protected $requiredOptions = array(
    'cache_dir'
  );

  /**
   * Constructor.
   *
   * Available options:
   *
   *   * file_locking: Enable / disable fileLocking (can avoid cache corruption under bad circumstances)
   *   * suffix: Cache suffix - default to ".cache"
   *   * automatic_cleaning_factor:  The automatic cleaning process destroy too old (for the given life time)
   *          cache files when a new cache file is written.
   *          0               => no automatic cache cleaning
   *          1               => systematic cache cleaning
   *          x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
   *   * hashed_directory_level: Nested directory level
   *   * filename_protection: Enable / disable filename protection
   *
   * @param array|string $cacheDir Options of the cache root directory
   */
  public function __construct($options = array())
  {
    // cache dir is not cache dir, but options!
    if(is_array($options))
    {
      // BC fixes, convert option like: cacheDir to cache_dir
      foreach($options as $optionName => $optionValue)
      {
        unset($options[$optionName]);
        $options[sfInflector::tableize($optionName)] = $optionValue;
      }
    }
    // BC compatibility, $options is $cache_dir
    elseif(is_string($options))
    {
      $options = array(
        'cache_dir' => $options
      );
    }

    parent::__construct($options);
  }

  /**
   * Setup the cache
   *
   */
  public function setup()
  {
    $this->setupCacheDir($this->getOption('cache_dir'));
  }

  /**
   * Sets the cache root directory.
   *
   * @param string $cacheDir The directory where to put the cache files
   * @return sfFileCache
   */
  public function setCacheDir($cacheDir)
  {
    $this->setOption('cache_dir', $cacheDir);
    $this->setupCacheDir($cacheDir);
    return $this;
  }

  /**
   * Setups cache directory. Creates it if it does not exist.
   *
   * @param string $cacheDir
   */
  protected function setupCacheDir($cacheDir)
  {
    // remove last DIRECTORY_SEPARATOR
    if(DIRECTORY_SEPARATOR == substr($cacheDir, -1))
    {
      $cacheDir = substr($cacheDir, 0, -1);
    }

    // create cache dir if needed
    if(!is_dir($cacheDir))
    {
      $current_umask = umask(0000);
      @mkdir($cacheDir, 0777, true);
      umask($current_umask);
    }
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    $file_path = $this->getFilePath($key);

    if(!file_exists($file_path))
    {
      return $default;
    }

    $data = $this->read($file_path, self::READ_DATA);

    if($data[self::READ_DATA] === null)
    {
      return $default;
    }

    return $data[self::READ_DATA];
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    $path = $this->getFilePath($key);
    return file_exists($path) && $this->isValid($path);
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    if($this->getOption('automatic_cleaning_factor') > 0 &&
        rand(1, $this->getOption('automatic_cleaning_factor')) == 1)
    {
      $this->clean(self::MODE_OLD);
    }

    return $this->write($this->getFilePath($key), $data, time() + $this->getLifetime($lifetime));
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return @unlink($this->getFilePath($key));
  }

  /**
   * @see sfICache
   */
  public function removePattern($pattern)
  {
    if(false !== strpos($pattern, '**'))
    {
      $pattern = str_replace(self::SEPARATOR, DIRECTORY_SEPARATOR, $pattern) . $this->getOption('suffix');
      $regexp = self::patternToRegexp($pattern);
      $paths = array();
      foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getOption('cache_dir'))) as $path)
      {
        if(preg_match($regexp, str_replace($this->getOption('cache_dir') . DIRECTORY_SEPARATOR, '', $path)))
        {
          $paths[] = $path;
        }
      }
    }
    else
    {
      $paths = sfGlob::find($this->getOption('cache_dir') . DIRECTORY_SEPARATOR . str_replace(self::SEPARATOR, DIRECTORY_SEPARATOR, $pattern) . $this->getOption('suffix'));
    }

    foreach($paths as $path)
    {
      if(is_dir($path))
      {
        sfToolkit::clearDirectory($path);
      }
      else
      {
        @unlink($path);
      }
    }
  }

  /**
   * @see sfCache
   */
  public function clean($mode = self::MODE_ALL)
  {
    if(!is_dir($this->getOption('cache_dir')))
    {
      return true;
    }

    $result = true;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getOption('cache_dir'))) as $file)
    {
      if($file->isDot())
      {
        continue;
      }
      if(self::MODE_ALL == $mode || !$this->isValid($file))
      {
        $result = @unlink($file) && $result;
      }
    }

    return $result;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    $path = $this->getFilePath($key);

    if(!file_exists($path))
    {
      return 0;
    }

    $data = $this->read($path, self::READ_TIMEOUT);
    return $data[self::READ_TIMEOUT] < time() ? 0 : $data[self::READ_TIMEOUT];
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    $path = $this->getFilePath($key);

    if (!file_exists($path))
    {
      return 0;
    }

    $data = $this->read($path, self::READ_TIMEOUT | self::READ_LAST_MODIFIED);

    if ($data[self::READ_TIMEOUT] < time())
    {
      return 0;
    }
    return $data[self::READ_LAST_MODIFIED];
  }

  /**
   * Validate the path
   *
   * @param string $path
   * @return boolean
   */
  protected function isValid($path)
  {
    $data = $this->read($path, self::READ_TIMEOUT);
    return time() < $data[self::READ_TIMEOUT];
  }

 /**
  * Converts a cache key to a full path.
  *
  * @param string $key The cache key
  * @return string The full path to the cache file
  */
  protected function getFilePath($key)
  {
    return $this->getOption('cache_dir').DIRECTORY_SEPARATOR.str_replace(self::SEPARATOR, DIRECTORY_SEPARATOR, $key).$this->getOption('suffix');
  }

 /**
  * Reads the cache file and returns the content.
  *
  * @param string $path The file path
  * @param mixed  $type The type of data you want to be returned
  *                     sfFileCache::READ_DATA: The cache content
  *                     sfFileCache::READ_TIMEOUT: The timeout
  *                     sfFileCache::READ_LAST_MODIFIED: The last modification timestamp
  *
  * @return array the (meta)data of the cache file. E.g. $data[sfFileCache::READ_DATA]
  *
  * @throws sfCacheException
  */
  protected function read($path, $type = self::READ_DATA)
  {
    if(!$fp = @fopen($path, 'rb'))
    {
      throw new sfCacheException(sprintf('Unable to read cache file "%s".', $path));
    }

    @flock($fp, LOCK_SH);
    $data[self::READ_TIMEOUT] = intval(@stream_get_contents($fp, 12, 0));
    if($type != self::READ_TIMEOUT && time() < $data[self::READ_TIMEOUT])
    {
      if($type & self::READ_LAST_MODIFIED)
      {
        $data[self::READ_LAST_MODIFIED] = intval(@stream_get_contents($fp, 12, 12));
      }
      if($type & self::READ_DATA)
      {
        fseek($fp, 0, SEEK_END);
        $length = ftell($fp) - 24;
        fseek($fp, 24);
        $data[self::READ_DATA] = @fread($fp, $length);
      }
    }
    else
    {
      $data[self::READ_LAST_MODIFIED] = null;
      $data[self::READ_DATA] = null;
    }
    @flock($fp, LOCK_UN);
    @fclose($fp);

    return $data;
  }

  /**
   * Writes the given data in the cache file.
   *
   * @param string  $path    The file path
   * @param string  $data    The data to put in cache
   * @param integer $timeout The timeout timestamp
   *
   * @return boolean true if ok, otherwise false
   *
   * @throws sfCacheException
   */
  protected function write($path, $data, $timeout)
  {
    $current_umask = umask();
    umask(0000);

    if (!is_dir(dirname($path)))
    {
      // create directory structure if needed
      mkdir(dirname($path), 0777, true);
    }

    $tmpFile = tempnam(dirname($path), basename($path));

    if (!$fp = @fopen($tmpFile, 'wb'))
    {
       throw new sfCacheException(sprintf('Unable to write cache file "%s".', $tmpFile));
    }

    @fwrite($fp, str_pad($timeout, 12, 0, STR_PAD_LEFT));
    @fwrite($fp, str_pad(time(), 12, 0, STR_PAD_LEFT));
    @fwrite($fp, $data);
    @fclose($fp);

    // Hack from Agavi (http://trac.agavi.org/changeset/3979)
    // With php < 5.2.6 on win32, renaming to an already existing file doesn't work, but copy does,
    // so we simply assume that when rename() fails that we are on win32 and try to use copy()
    if (!@rename($tmpFile, $path))
    {
      if (copy($tmpFile, $path))
      {
        unlink($tmpFile);
      }
    }

    chmod($path, 0666);
    umask($current_umask);

    return true;
  }

}
