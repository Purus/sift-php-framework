<?php
/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfClassLoader implements a PSR-0 class loader
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 *     $loader = new sfClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Namespace\Component', __DIR__.'/component');
 *     $loader->add('Namespace',           __DIR__.'/framework');
 *
 *     // activate the autoloader
 *     $loader->register();
 *
 *     // to enable searching the include path (eg. for PEAR packages)
 *     $loader->setUseIncludePath(true);
 *
 * In this example, if you try to use a class in the Namespace\Component
 * namespace or one of its children (Namespace\Component\Console for instance),
 * the autoloader will first look for the class under the component/
 * directory, and it will then fallback to the framework/ directory if not
 * found before giving up.
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @package Sift
 * @subpackage autoload
 */
class sfClassLoader {

  private $prefixes = array();
  private $fallbackDirs = array();
  private $useIncludePath = false;
  private $classMap = array();

  public function getPrefixes()
  {
    return $this->prefixes;
  }

  public function getFallbackDirs()
  {
    return $this->fallbackDirs;
  }

  public function getClassMap()
  {
    return $this->classMap;
  }

  /**
   * @param array $classMap Class to filename map
   */
  public function addClassMap(array $classMap)
  {
    if($this->classMap)
    {
      $this->classMap = array_merge($this->classMap, $classMap);
    }
    else
    {
      $this->classMap = $classMap;
    }
  }

  /**
   * Registers a set of classes
   *
   * @param string       $prefix The classes prefix
   * @param array|string $paths  The location(s) of the classes
   */
  public function add($prefix, $paths)
  {
    if(!$prefix)
    {
      foreach((array) $paths as $path)
      {
        $this->fallbackDirs[] = $path;
      }

      return;
    }
    if(isset($this->prefixes[$prefix]))
    {
      $this->prefixes[$prefix] = array_merge(
              $this->prefixes[$prefix], (array) $paths
      );
    }
    else
    {
      $this->prefixes[$prefix] = (array) $paths;
    }
  }

  /**
   * Turns on searching the include path for class files.
   *
   * @param bool $useIncludePath
   */
  public function setUseIncludePath($useIncludePath)
  {
    $this->useIncludePath = $useIncludePath;
  }

  /**
   * Can be used to check if the autoloader uses the include path to check
   * for classes.
   *
   * @return bool
   */
  public function getUseIncludePath()
  {
    return $this->useIncludePath;
  }

  /**
   * Registers this instance as an autoloader.
   *
   * @param bool $prepend Whether to prepend the autoloader or not
   */
  public function register($prepend = false)
  {
    if(version_compare(PHP_VERSION, '5.3.0', '>='))
    {
      spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }
    elseif($prepend)
    {
      $loaders = spl_autoload_functions();
      spl_autoload_register(array($this, 'loadClass'), true);
      if($loaders)
      {
        foreach($loaders as $loader) 
        {
          spl_autoload_unregister($loader);
          spl_autoload_register($loader, true);
        }
      }
    }
    else
    {
      spl_autoload_register(array($this, 'loadClass'));
    }
  }

  /**
   * Unregisters this instance as an autoloader.
   */
  public function unregister()
  {
    spl_autoload_unregister(array($this, 'loadClass'));
  }

  /**
   * Loads the given class or interface.
   *
   * @param  string    $class The name of the class
   * @return bool|null True, if loaded
   */
  public function loadClass($class)
  {
    if($file = $this->findFile($class))
    {
      include $file;

      return true;
    }
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param string $class The name of the class
   *
   * @return string|null The path, if found
   */
  public function findFile($class)
  {
    if('\\' == $class[0])
    {
      $class = substr($class, 1);
    }

    if(isset($this->classMap[$class]))
    {
      return $this->classMap[$class];
    }
    elseif(isset($this->classMap[strtolower($class)]))
    {
      return $this->classMap[strtolower($class)];
    }

    if(false !== $pos = strrpos($class, '\\'))
    {
      // namespaced class name
      $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    }
    else
    {
      // PEAR-like class name
      $classPath = null;
      $className = $class;
    }

    // see if the file exists in the current module lib directory
    // must be in a module context
    if(class_exists('sfContext', false) &&
        sfContext::hasInstance() &&
        ($module = sfContext::getInstance()->getModuleName()) &&
        isset($this->classMap[$module.'/'.strtolower($class)]))
    {
      return $this->classMap[$module.'/'.strtolower($class)];
    }

    $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    foreach($this->prefixes as $prefix => $dirs)
    {
      if(0 === strpos($class, $prefix))
      {
        foreach($dirs as $dir)
        {
          if(file_exists($dir . DIRECTORY_SEPARATOR . $classPath))
          {
            return $dir . DIRECTORY_SEPARATOR . $classPath;
          }
        }
      }
    }

    foreach($this->fallbackDirs as $dir)
    {
      if(file_exists($dir . DIRECTORY_SEPARATOR . $classPath))
      {
        return $dir . DIRECTORY_SEPARATOR . $classPath;
      }
    }

    if($this->useIncludePath && $file = stream_resolve_include_path($classPath))
    {
      return $file;
    }

    return $this->classMap[$class] = false;
  }

}