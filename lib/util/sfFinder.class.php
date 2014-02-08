<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Allow to build rules to find files and directories.
 *
 * All rules may be invoked several times, except for ->in() method.
 * Some rules are cumulative (->name() for example) whereas others are destructive
 * (most recent value is used, ->maxDepth() method for example).
 *
 * All methods return the current sfFinder object to allow easy chaining:
 *
 * <code>
 * // find php files in current directory
 * $files = sfFinder::type('file')->name('*.php')->in('.');
 * </code>
 *
 * @package    Sift
 * @subpackage util
 */
class sfFinder {

  /**
   * Type file
   */
  const TYPE_FILE = 'file';

  /**
   * Type directory
   */
  const TYPE_DIRECTORY = 'directory';

  /**
   * Type any (file or directory)
   */
  const TYPE_ANY = 'any';

  /**
   * The default search type
   *
   * @var string
   */
  protected $type = self::TYPE_FILE;

  /**
   * Names to seach for
   *
   * @var array
   */
  protected $names = array();

  /**
   * Array of prune items
   *
   * @var array
   */
  protected $prunes = array();

  /**
   * Array of discard items
   *
   * @var array
   */
  protected $discards = array();

  /**
   * Array of excecutable callbacks
   *
   * @var array
   */
  protected $execChecks = array();

  /**
   *
   * @var array
   */
  protected $sizes = array();

  /**
   * Minimal depth to search
   *
   * @var integer
   */
  protected $minDepth = 0;

  /**
   * Max depth to search
   *
   * @var integer
   */
  protected $maxDepth = PHP_INT_MAX;

  /**
   * Return relative paths?
   *
   * @var boolean
   */
  protected $relative = false;

  /**
   * Follow symlinks?
   *
   * @var boolean
   */
  protected $followLink = false;

  /**
   * Sort the result?
   *
   * @var string|false
   */
  protected $sort = false;

  /**
   * Ignore version control?
   *
   * @var boolean
   */
  protected $ignoreVersionControl = true;

  /**
   * Array of ignored patterns for version control systems
   *
   * @var array
   */
  public static $versionControlIgnores = array(
    '.svn', '_svn', 'CVS', '_darcs', '.arch-params',
    '.monotone', '.bzr', '.git', '.hg'
  );

  /**
   * Constructor
   *
   * @param string $type
   */
  public function __construct($type = self::TYPE_FILE)
  {
    $this->setType($type);
  }

  /**
   * Sets the type of elements to return.
   *
   * @param  string $name The directory or file or any (for both file and directory)
   * @return sfFinder new sfFinder object
   */
  public static function type($name)
  {
    return new self($name);
  }

  /**
   * Sets maximum directory depth.
   *
   * Finder will descend at most $level levels of directories below the starting point.
   *
   * @param  int $level
   * @return sfFinder current sfFinder object
   */
  public function maxDepth($level)
  {
    $this->maxDepth = $level;
    return $this;
  }

  /**
   * Sets minimum directory depth.
   *
   * @param integer $level The level
   * @return sfFinder current sfFinder object
   */
  public function minDepth($level)
  {
    $this->minDepth = $level;
    return $this;
  }

  /**
   * Returns the type which is searched for
   *
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Sets the type of elements to returns.
   *
   * @param string $type The type, either "directory" or "file" or "any" (for both file and directory)
   * @throws InvalidArgumentException If the type is invalid
   * @return sfFinder Current object
   */
  public function setType($type)
  {
    $type = strtolower($type);

    if(substr($type, 0, 3) === 'dir')
    {
      $this->type = self::TYPE_DIRECTORY;
    }
    else if($type === 'any')
    {
      $this->type = self::TYPE_ANY;
    }
    else if($type === 'file')
    {
      $this->type = self::TYPE_FILE;
    }
    else
    {
      throw new InvalidArgumentException(sprintf('Invalid type "%s" given. Valid types are: "file", "dir" or "any".', $type));
    }

    return $this;
  }

  /**
   * Adds rules that files must match. Accepts unlimited number of parameters.
   *
   * You can use patterns (delimited with / sign), globs or simple strings.
   *
   * $finder->name('*.php')
   * $finder->name('/\.php$/') // same as above
   * $finder->name('test.php')
   *
   * @param string $pattern A pattern (a regexp, a glob, or a string).
   * @return sfFinder Current object
   */
  public function name($pattern)
  {
    $args = func_get_args();
    $this->names = array_merge($this->names, $this->argumentsToArray($args));
    return $this;
  }

  /**
   * Adds rules that files must not match. Accepts unlimited number of parameters.
   *
   * @param string $pattern A pattern (a regexp, a glob, or a string)
   * @return sfFinder Current object
   */
  public function notName($pattern)
  {
    $args = func_get_args();
    $this->names = array_merge($this->names, $this->argumentsToArray($args, true));
    return $this;
  }

  /**
   * Adds tests for file sizes. Accepts unlimited number of parameters.
   *
   * $finder->size('> 10K');
   * $finder->size('<= 1Ki');
   * $finder->size(4);
   *
   * @param string $size A size range string
   * @return sfFinder Current object
   */
  public function size($size)
  {
    $args = func_get_args();
    for($i = 0, $count = count($args); $i < $count; $i++)
    {
      $this->sizes[] = new sfNumberCompare($args[$i]);
    }
    return $this;
  }

  /**
   * Traverses no further.
   *
   * @param string $pattern A pattern (a regexp, a glob, or a string)
   * @return sfFinder Current object
   */
  public function prune($pattern)
  {
    $args = func_get_args();
    $this->prunes = array_merge($this->prunes, $this->argumentsToArray($args));
    return $this;
  }

  /**
   * Discards elements that matches.
   *
   * @param string $pattern A pattern (a regexp, a glob, or a string)
   * @return sfFinder Current object
   */
  public function discard($pattern)
  {
    $args = func_get_args();
    $this->discards = array_merge($this->discards, $this->argumentsToArray($args));
    return $this;
  }

  /**
   * Ignores version control directories.
   *
   * Currently supports Subversion, CVS, DARCS, Gnu Arch, Monotone, Bazaar-NG, GIT, Mercurial
   *
   * @param  bool   $ignore  falase when version control directories shall be included (default is true)
   *
   * @return sfFinder Current object
   */
  public function ignoreVersionControl($ignore = true)
  {
    $this->ignoreVersionControl = $ignore;
    return $this;
  }

  /**
   * Returns files and directories ordered by name
   *
   * @return sfFinder Current object
   */
  public function sortByName()
  {
    $this->sort = 'name';
    return $this;
  }

  /**
   * Returns files and directories ordered by type (directories before files), then by name
   *
   * @return sfFinder Current object
   */
  public function sortByType()
  {
    $this->sort = 'type';
    return $this;
  }

  /**
   * Executes function or method for each element. The element matches if the callback returns true.
   *
   * $finder->exec('myfunction');
   * $finder->exec(array($object, 'mymethod'));
   *
   * @param callable $function function or method to call
   * @return sfFinder Current object
   */
  public function exec($function)
  {
    $args = func_get_args();
    for($i = 0, $count = count($args); $i < $count; $i++)
    {
      if(is_array($args[$i]) && !method_exists($args[$i][0], $args[$i][1]))
      {
        throw new sfException(sprintf('The method "%s" does not exist for object "%s".', $args[$i][1], $args[$i][0]));
      }
      if(!is_array($args[$i]) && !function_exists($args[$i]))
      {
        throw new sfException(sprintf('The function "%s" does not exist.', $args[$i]));
      }
      $this->execChecks[] = $args[$i];
    }
    return $this;
  }

  /**
   * Returns relative paths for all files and directories.
   *
   * @return sfFinder Current object
   */
  public function relative()
  {
    $this->relative = true;
    return $this;
  }

  /**
   * Symlink following.
   *
   * @param $follow Follow the symlinks?
   * @return sfFinder Current object
   */
  public function followLink($follow = true)
  {
    $this->followLink = filter_var($follow, FILTER_VALIDATE_BOOLEAN);
    return $this;
  }

  /**
   * Searches files and directories which match defined rules.
   *
   * @param string $directory The directory to search in
   * @return array Array of items
   */
  public function in($directory)
  {
    $files = array();
    $here = getcwd();
    $finder = clone $this;

    if($this->ignoreVersionControl)
    {
      $finder->discard(self::$versionControlIgnores)->prune(self::$versionControlIgnores);
    }

    // first argument is an array?
    $numberOfArgs = func_num_args();
    $arg_list = func_get_args();
    if($numberOfArgs === 1 && is_array($arg_list[0]))
    {
      $arg_list = $arg_list[0];
      $numberOfArgs = count($arg_list);
    }

    for($i = 0; $i < $numberOfArgs; $i++)
    {
      $dir = realpath($arg_list[$i]);
      if(!is_dir($dir))
      {
        continue;
      }

      $dir = str_replace('\\', '/', $dir);
      // absolute path?
      if(!self::isPathAbsolute($dir))
      {
        $dir = $here . '/' . $dir;
      }

      $newFiles = str_replace('\\', '/', $finder->searchIn($dir));

      if($this->relative)
      {
        $newFiles = preg_replace('#^' . preg_quote(rtrim($dir, '/'), '#') . '/#', '', $newFiles);
      }

      $files = array_merge($files, $newFiles);
    }

    if($this->sort === 'name')
    {
      sort($files);
    }

    return array_unique($files);
  }

  /**
   * Search in the directory
   *
   * @param string $dir The directory
   * @param integer $depth The depth
   * @return array
   */
  protected function searchIn($dir, $depth = 0)
  {
    if($depth > $this->maxDepth)
    {
      return array();
    }

    $dir = realpath($dir);

    if(!$this->followLink && is_link($dir))
    {
      return array();
    }

    $files = $tempFiles = $tempFolders = array();

    if(is_dir($dir) && is_readable($dir))
    {
      $current_dir = opendir($dir);
      while(false !== $entryName = readdir($current_dir))
      {
        if($entryName == '.' || $entryName == '..')
        {
          continue;
        }

        $currentEntry = $dir . DIRECTORY_SEPARATOR . $entryName;

        if(!$this->followLink && is_link($currentEntry))
        {
          continue;
        }

        if(is_dir($currentEntry))
        {
          if($this->sort === 'type')
          {
            $tempFolders[$entryName] = $currentEntry;
          }
          else
          {
            if(($this->type === 'directory' || $this->type === 'any')
                && ($depth >= $this->minDepth) && !$this->isDiscarded($entryName)
                && $this->matchNames($entryName) && $this->execOk($dir, $entryName))
            {
              $files[] = $currentEntry;
            }

            if(!$this->isPruned($entryName))
            {
              $files = array_merge($files, $this->searchIn($currentEntry, $depth + 1));
            }
          }
        }
        else
        {
          if(($this->type !== 'directory' || $this->type === 'any')
              && ($depth >= $this->minDepth) && !$this->isDiscarded($entryName)
              && $this->matchNames($entryName)
              && $this->isSizeOk($dir . DIRECTORY_SEPARATOR . $entryName)
              && $this->execOk($dir, $entryName))
          {
            if($this->sort === 'type')
            {
              $tempFiles[] = $currentEntry;
            }
            else
            {
              $files[] = $currentEntry;
            }
          }
        }
      }

      if($this->sort === 'type')
      {
        ksort($tempFolders);
        foreach($tempFolders as $entryName => $currentEntry)
        {
          if(($this->type === 'directory' || $this->type === 'any')
              && ($depth >= $this->minDepth) && !$this->isDiscarded($entryName)
              && $this->matchNames($dir, $entryName)
              && $this->execOk($dir, $entryName))
          {
            $files[] = $currentEntry;
          }

          if(!$this->isPruned($entryName))
          {
            $files = array_merge($files, $this->searchIn($currentEntry, $depth + 1));
          }
        }

        sort($tempFiles);
        $files = array_merge($files, $tempFiles);
      }

      closedir($current_dir);
    }

    return $files;
  }

  /**
   * Matches the names which are we searching for?
   *
   * @param string $entry The entry name
   * @return boolean
   */
  protected function matchNames($entry)
  {
    if(!count($this->names))
    {
      return true;
    }

    // Flags indicating that there was attempts to match
    // at least one "notName" or "name" rule respectively
    // to following variables:
    $oneNotNameRule = $oneNameRule = false;

    foreach($this->names as $args)
    {
      list($not, $regex) = $args;
      $not ? $oneNotNameRule = true : $oneNameRule = true;
      if(preg_match($regex, $entry))
      {
        // We must match ONLY ONE "not_name" or "name" rule:
        // if "not_name" rule matched then we return "false"
        // if "name" rule matched then we return "true"
        return $not ? false : true;
      }
    }

    if($oneNotNameRule && $oneNameRule)
    {
      return false;
    }
    else if($oneNotNameRule)
    {
      return true;
    }
    else if($oneNameRule)
    {
      return false;
    }
    return true;
  }

  /**
   * Is size ok for the item?
   *
   * @param string $entry The absolute path to the item (file or directory)
   * @return boolean
   */
  protected function isSizeOk($entry)
  {
    if(0 === count($this->sizes))
    {
      return true;
    }

    if(!is_file($entry))
    {
      return true;
    }

    $filesize = filesize($entry);
    foreach($this->sizes as $numberCompare)
    {
      if(!$numberCompare->test($filesize))
      {
        return false;
      }
    }
    return true;
  }

  /**
   * Checks if the entry is marked as pruned.
   *
   * @param string $entry The entry name
   * @return boolean
   */
  protected function isPruned($entry)
  {
    if(0 === count($this->prunes))
    {
      return false;
    }
    foreach($this->prunes as $args)
    {
      if(preg_match($args[1], $entry))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Is the given item mark as to be discarded?
   *
   * @param string $entry The entry name
   * @return boolean
   */
  protected function isDiscarded($entry)
  {
    if(0 === count($this->discards))
    {
      return false;
    }
    foreach($this->discards as $args)
    {
      if(preg_match($args[1], $entry))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Does the exceute check marked this entry as ok?
   *
   * @param string $dir The directory
   * @param string $entry The file name
   * @return boolean
   */
  protected function execOk($dir, $entry)
  {
    if(0 === count($this->execChecks))
    {
      return true;
    }

    foreach($this->execChecks as $exec)
    {
      if(call_user_func_array($exec, array($dir, $entry)) !== true)
      {
        return false;
      }
    }
    return true;
  }

  /**
   * Is the path absolute?
   *
   * @param string $path
   * @return boolean
   */
  public static function isPathAbsolute($path)
  {
    if($path{0} === '/' || $path{0} === '\\' ||
      (strlen($path) > 3 && ctype_alpha($path{0}) && $path{1} === ':' && ($path{2} === '\\' || $path{2} === '/')))
    {
      return true;
    }
    return false;
  }

  /**
   * Converts the string to regular expression
   *
   * @param type $str
   * @return type
   */
  protected function toRegex($str)
  {
    if(preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $str))
    {
      return $str;
    }
    return sfGlobToRegex::toRegex($str);
  }

  /**
   * Converts the arguments to array
   *
   * @param array $arg_list
   * @param boolean $not
   * @return array
   */
  protected function argumentsToArray($arguments, $not = false)
  {
    $list = array();
    for($i = 0, $count = count($arguments); $i < $count; $i++)
    {
      if(is_array($arguments[$i]))
      {
        foreach($arguments[$i] as $arg)
        {
          $list[] = array($not, $this->toRegex($arg));
        }
      }
      else
      {
        $list[] = array($not, $this->toRegex($arguments[$i]));
      }
    }
    return $list;
  }

  /**
   * Magic call method.
   *
   * @param string $method The method name
   * @param array $arguments Array of arguments
   * @return mixed
   * @throws sfException If the method does not exist
   */
  public function __call($method, $arguments)
  {
    // BC compatibility
    // the call to get_type() will be converted to getType()
    if(strpos($method, '_') !== false)
    {
      $alias = sfInflector::camelize($method);
      if(method_exists($this, $alias))
      {
        return call_user_func_array(array($this, $alias), $arguments);
      }
    }
    throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
  }

}
