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

  const READ_DATA = 1;
  const READ_TIMEOUT = 2;
  const READ_LAST_MODIFIED = 4;
  const READ_HASH = 8;

  const DEFAULT_NAMESPACE = '';

  /**
   * Array of default options. Inherits also default options
   * from sfCache
   *
   * @var array
   */
  protected $defaultOptions = array(
    'cache_dir' => '',
    'file_locking' => true,
    'write_control' => false, // buggy
    'read_control' => false, // buggy
    'filename_protection' => false,
    'hashed_directory_level' => 0,
    'suffix' => '.cache'
  );

  /**
   * Array of valid options
   *
   * @var array
   */
  protected $validOptions = array(
    'cache_dir', 'file_locking',
    'write_control', 'read_control',
    'filename_protection', 'hashed_directory_level',
    'suffix'
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
   *   * write_control: Enable / disable write control (the cache is read just after writing to detect corrupt entries)
   *   * read_control:  Enable / disable read control (If enabled, a control key is embeded in cache file and this key is compared with the one calculated after the reading.)
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

    $this->initialize($options);
  }

  /**
   * Initializes the cache.
   *
   * @param array An array of options
   * Available options:
   *  - cacheDir:                cache root directory
   *  - fileLocking:             enable / disable file locking (boolean)
   *  - writeControl:            enable / disable write control (boolean)
   *  - readControl:             enable / disable read control (boolean)
   *  - fileNameProtection:      enable / disable automatic file name protection (boolean)
   *  - automaticCleaningFactor: disable / tune automatic cleaning process (int)
   *  - hashedDirectoryLevel:    level of the hashed directory system (int)
   *  - lifeTime:                default life time
   *
   */
  public function initialize($options = array())
  {
    if(count($options))
    {
      // BC fixes
      foreach($options as $optionName => $optionValue)
      {
        unset($options[$optionName]);
        $options[sfInflector::tableize($optionName)] = $optionValue;
      }

      $this->setOptions($options);
    }

    $this->setupCacheDir($this->getOption('cache_dir'));
  }

  /**
   * Sets the suffix for cache files.
   *
   * @param string $suffix The suffix name (with the leading .)
   * @return sfFileCache
   */
  public function setSuffix($suffix)
  {
    return $this->setOption('suffix', $suffix);
  }

  /**
   * Returns cache suffix
   *
   * @return string
   */
  public function getSuffix()
  {
    return $this->getOption('suffix');
  }

  /**
   * Sets hashed directory level
   *
   * @param integer $level
   * @return sfFileCache
   */
  public function setHashedDirectoryLevel($level)
  {
    return $this->setOption('hashed_directory_level', (int) $level);
  }

  /**
   * Return hashed_directory_level setting
   *
   * @return integer
   */
  public function getHashedDirectoryLevel()
  {
    return $this->getOption('hashed_directory_level');
  }

  /**
   * Enables / disables write control.
   *
   * @param boolean
   * @return sfFileCache
   */
  public function setWriteControl($boolean)
  {
    return $this->setOption('write_control', (boolean) $boolean);
  }

  /**
   * Gets the value of the write_control option.
   *
   * @return boolean
   */
  public function getWriteControl()
  {
    return $this->getOption('write_control');
  }

  /**
   *
   * @return boolean
   */
  public function getFilenameProtection()
  {
    return $this->getOption('filename_protection');
  }

  /**
   * Return cache dir
   *
   * @return string
   */
  public function getCacheDir()
  {
    return $this->getOption('cache_dir');
  }

  /**
   * Enables / disables file locking.
   *
   * @param boolean $boolean
   * @return sfFileCache
   */
  public function setFileLocking($boolean)
  {
    return $this->setOption('file_locking', (boolean) $boolean);
  }

  /**
   * Gets the value of the file_locking option.
   *
   * @return boolean
   */
  public function getFileLocking()
  {
    return $this->getOption('file_locking');
  }

  /**
   * Returns read_control config
   *
   * @return boolean
   */
  public function getReadControl()
  {
    return $this->getOption('read_control');
  }

  /**
   * Enables / disables read control.
   *
   * @param boolean $readControl
   * @return sfFileCache
   */
  public function setReadControl($readControl)
  {
    return $this->setOption('read_control', (boolean)$readControl);
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
   * Returns cache
   *
   * @param string $id
   * @param string $namespace
   * @param boolean $doNotTestCacheValidity
   * @return mixed
   */
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    $file_path = $this->getFilePath($id, $namespace);

    if(!file_exists($file_path))
    {
      return null;
    }

    $data = $this->read($file_path, self::READ_DATA, $doNotTestCacheValidity);

    if($data[self::READ_DATA] === null)
    {
      return null;
    }

    return $data[self::READ_DATA];
  }

  /**
   * Returns true if there is a cache for the given id and namespace.
   *
   * @param  string  The cache id
   * @param  string  The name of the cache namespace
   * @param  boolean If set to true, the cache validity won't be tested
   *
   * @return boolean true if the cache exists, false otherwise
   *
   * @see sfCache
   */
  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false)
  {
    $file_path = $this->getFilePath($id, $namespace);

    if($doNotTestCacheValidity)
    {
      if(file_exists($file_path))
      {
        return true;
      }
      return false;
    }

    return file_exists($file_path) && $this->isValid($file_path);
  }

  /**
   * Saves some data in a cache file.
   *
   * @param string The cache id
   * @param string The name of the cache namespace
   * @param string The data to put in cache
   * @param integer $lifetime Lifetime of the cache
   * @return boolean true if no problem
   *
   * @see sfCache
   */
  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data, $lifetime = null)
  {
    $file_path = $this->getFilePath($id, $namespace);

    $cleaningFactor = $this->getAutomaticCleaningFactor();
    if($cleaningFactor > 0)
    {
      if(rand(1, $cleaningFactor) == 1)
      {
        $this->clean(null, self::MODE_OLD);
      }
    }

    if($this->getWriteControl())
    {
      return $this->writeAndControl($file_path, $data, time() + $this->getLifetime($lifetime));
    }
    else
    {
      return $this->write($file_path, $data, time() + $this->getLifetime($lifetime));
    }
  }

  /**
   * Removes a cache file.
   *
   * @param string The cache id
   * @param string The name of the cache namespace
   *
   * @return boolean true if no problem
   */
  public function remove($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    $file_path = $this->getFilePath($id, $namespace);
    return $this->unlink($file_path);
  }

  /**
   * Cleans the cache.
   *
   * If no namespace is specified all cache files will be destroyed
   * else only cache files of the specified namespace will be destroyed.
   *
   * @param string The name of the cache namespace
   *
   * @return boolean true if no problem
   */
  public function clean($namespace = null, $mode = sfCache::MODE_ALL)
  {
    $dir = $this->getOption('cache_dir');

    if($namespace)
    {
      $dir .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $namespace);
    }

    if(!is_dir($dir))
    {
      return true;
    }

    $result = true;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file)
    {
      if(sfCache::MODE_ALL == $mode || !$this->isValid($file))
      {
        $result = $this->unlink($file) && $result;
      }
    }

    return $result;
  }

  /**
   * Checks if $path is valid
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
   * Returns the cache last modification time.
   *
   * @return int The last modification time
   */
  public function getLastModified($id, $namespace = self::DEFAULT_NAMESPACE)
  {
    $path = $this->getFilePath($id, $namespace);
    $data = $this->read($path, self::READ_TIMEOUT | self::READ_LAST_MODIFIED);
    if($data[self::READ_TIMEOUT] < time())
    {
      return 0;
    }
    return $data[self::READ_LAST_MODIFIED];
  }

  /**
   * Reads the cache file and returns the content.
   *
   * @param string $path The file path
   * @param mixed  $type The type of data you want to be returned
   *                     sfFileCache::READ_DATA: The cache content
   *                     sfFileCache::READ_TIMEOUT: The timeout
   *                     sfFileCache::READ_LAST_MODIFIED: The last modification timestamp
   *                     sfFileCache::READ_HASH: Hash of the cache
   *
   * @return array the (meta)data of the cache file. E.g. $data[sfFileCache::READ_DATA]
   *
   * @throws sfCacheException
   */
  protected function read($path, $type = self::READ_DATA, $doNotTestCacheValidity = false)
  {
    if(!$fp = @fopen($path, 'rb'))
    {
      throw new sfCacheException(sprintf('Unable to read cache file "%s".', $path));
    }

    if($this->getFileLocking())
    {
      // lock file
      @flock($fp, LOCK_SH);
    }

    // return data
    $data = array();

    // read timeout information
    $data[self::READ_TIMEOUT] = intval(@stream_get_contents($fp, 12, 0));

    if($this->getReadControl())
    {
      $data[self::READ_HASH] = dechex(@stream_get_contents($fp, 24, 32));
    }

    if($type != self::READ_TIMEOUT && !$doNotTestCacheValidity && (time() < $data[self::READ_TIMEOUT]))
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

    if($this->getFileLocking())
    {
      @flock($fp, LOCK_UN);
    }

    @fclose($fp);

    if($this->getReadControl())
    {
    }

    return $data;
  }

  /**
   * Makes a file name (with path).
   *
   * @param string The cache id
   * @param string The name of the namespace
   *
   * @return string File path
   * @return array An array containing the path and the file name
   */
  protected function getFilePath($id, $namespace)
  {
    $file = ($this->getFilenameProtection()) ?
            md5($id) . $this->getSuffix() : $id . $this->getSuffix();

    if($namespace)
    {
      $namespace = str_replace('/', DIRECTORY_SEPARATOR, $namespace);
      $path = $this->getOption('cache_dir') . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR;
    }
    else
    {
      $path = $this->getOption('cache_dir') . DIRECTORY_SEPARATOR;
    }
    if($this->getHashedDirectoryLevel() > 0)
    {
      $hash = md5($file);
      for($i = 0; $i < $this->getHashedDirectoryLevel(); $i++)
      {
        $path = $path . substr($hash, 0, $i + 1) . DIRECTORY_SEPARATOR;
      }
    }

    return $path . $file;
  }

  /**
   * Removes a file.
   *
   * @param string The complete file path and name
   *
   * @return boolean true if no problem
   */
  protected function unlink($file)
  {
    return @unlink($file) ? true : false;
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

    if(!is_dir(dirname($path)))
    {
      // create directory structure if needed
      mkdir(dirname($path), 0777, true);
    }

    $tmpFile = tempnam(dirname($path), basename($path));

    if(!$fp = @fopen($tmpFile, 'wb'))
    {
      throw new sfCacheException(sprintf('Unable to write cache file "%s".', $tmpFile));
    }

    @fwrite($fp, str_pad($timeout, 12, 0, STR_PAD_LEFT));
    @fwrite($fp, str_pad(time(), 12, 0, STR_PAD_LEFT));

    if($this->getReadControl())
    {
      // @fwrite($fp, $this->hash($data), 32);
    }

    @fwrite($fp, $data);
    @fclose($fp);

    // Hack from Agavi (http://trac.agavi.org/changeset/3979)
    // With php < 5.2.6 on win32, renaming to an already existing file doesn't work, but copy does,
    // so we simply assume that when rename() fails that we are on win32 and try to use copy()
    if(!@rename($tmpFile, $path))
    {
      if(copy($tmpFile, $path))
      {
        unlink($tmpFile);
      }
    }

    chmod($path, 0666);
    umask($current_umask);

    return true;
  }

  /**
   * Writes the given data in the cache file and controls it just after to avoid corrupted cache entries.
   *
   * @param string The file path
   * @param string The file name
   * @param string The data to put in cache
   *
   * @return boolean true if the test is ok
   */
  protected function writeAndControl($path, $file, $data)
  {
    $this->write($path, $file, $data);
    $dataRead = $this->read($path, $file);

    return ($dataRead == $data);
  }

  /**
   * Makes a control key with the string containing datas.
   *
   * @param string $data data
   *
   * @return string control key
   */
  protected function hash($data)
  {
    // return dechex(crc32($data));
    return sprintf('% 32d', crc32($data));
  }

}
