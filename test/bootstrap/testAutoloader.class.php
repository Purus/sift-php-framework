<?php

require_once(dirname(__FILE__).'/../../lib/util/sfFinder.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfGlobToRegex.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfNumberCompare.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');

class testAutoloader
{
  static public $class_paths = array();

  static public function initialize($with_cache = true)
  {
    $tmp_dir = sfToolkit::getTmpDir();
    if (is_readable($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php'))
    {      
      self::$class_paths = unserialize(file_get_contents($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php'));
    }
    else
    {
      $files = sfFinder::type('file')->name('*.php')->ignore_version_control()->in(realpath(dirname(__FILE__).'/../../lib'));
      self::$class_paths = array();
      foreach ($files as $file)
      {
        preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
        foreach ($classes[1] as $class)
        {
          self::$class_paths[$class] = $file;
        }
      }
      if ($with_cache)
      {
        file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'sf_autoload_paths.php', serialize(self::$class_paths));
      }
    }
  }

  static public function __autoload($class)
  {
    if (isset(self::$class_paths[$class]))
    {
      require(self::$class_paths[$class]);

      return true;
    }

    return false;
  }

  static public function removeCache()
  {
    unlink(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'sf_autoload_paths.php');
  }
}
