<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoader is a class which contains the logic to look for files/classes
 *
 * @package    Sift
 * @subpackage core
 */
class sfLoader {

  /**
   * Array of application wide configuration files which can
   * be overridden with a dimension
   *
   * @var array
   */
  protected static $applicationConfigurationFiles = array(
    'app.yml', 'factories.yml',
    'filters.yml', 'i18n.yml',
    'logging.yml', 'settings.yml',
    'databases.yml', 'routing.yml',
    'asset_packages',
  );

  /**
   * Array of module wide configuration files which can
   * be overridden with a dimension
   *
   * @var array
   */
  protected static $moduleConfigurationFiles = array(
    'cache.yml', 'module.yml', 'security.yml', 'view.yml'
  );

  /**
   * Array of all confuration files (app wide + module wide) which can
   * be overridden with a dimension
   *
   * @var array
   */
  protected static $allConfigurationFiles = array(
    'app.yml', 'factories.yml',
    'filters.yml', 'i18n.yml',
    'logging.yml', 'settings.yml',
    'databases.yml', 'routing.yml',
    'asset_packages',
    'cache.yml', 'module.yml', 'security.yml', 'view.yml'
  );

  /**
   * Loaded helpers cache
   *
   * @var array
   */
  static $loadedHelpers = array();

  /**
   * Gets directories where lib files are stored for a given module.
   *
   * @param string $moduleName The module name
   * @return array An array of directories
   */
  public static function getLibDirs($moduleName)
  {
    $libDirName = sfConfig::get('sf_app_module_lib_dir_name');
    $moduleDirName = sfConfig::get('sf_app_module_dir_name');

    $dirs = array();

    // application
    $dirs[] = sfConfig::get('sf_app_module_dir') . DS . $moduleName . DS . $libDirName;

    // plugins
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($dir = sfConfig::get('sf_plugins_dir') . DS . $plugin . DS .
                $moduleDirName . DS . $moduleName . DS . $libDirName))
      {
        $dirs[] = $dir;
      }
    }

    $dirs = array_merge($dirs, self::getCustomDirectories($moduleName . DS . $libDirName));

    // generated templates in cache
    $dirs[] = sfConfig::get('sf_module_cache_dir') . DS . 'auto' . ucfirst($moduleName) . DS . $libDirName;
    return $dirs;
  }

  /**
   * Gets directories where model classes are stored.
   *
   * @return array An array of directories
   */
  public static function getModelDirs()
  {
    // project
    $dirs = array(sfConfig::get('sf_model_lib_dir'));
    // plugins
    $dirs = array_merge($dirs, self::getPluginDirectories(
            sfConfig::get('sf_app_lib_dir_name') . DS . sfConfig::get('sf_model_dir_name'), false));
    return $dirs;
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  public static function getControllerDirs($moduleName)
  {
    $suffix = $moduleName . DS . sfConfig::get('sf_app_module_action_dir_name');

    $dirs = array();

    // load application directories
    foreach(self::getApplicationDirectories($suffix) as $dir)
    {
      // modules in the app are automatically enabled
      $dirs[$dir] = false;
    }

    // load custom directories
    foreach(self::getCustomDirectories($suffix) as $dir)
    {
      // enableCheck is true
      $dirs[$dir] = true;
    }

    // plugin directories
    foreach(self::getPluginDirectories(sfConfig::get('sf_app_module_dir_name') . DS . $suffix) as $dir)
    {
      // enableCheck is true
      $dirs[$dir] = true;
    }

    foreach(self::getCoreDirectories(sfConfig::get('sf_app_module_dir_name') . DS . $suffix) as $dir)
    {
      // enable check si true
      $dirs[$dir] = true;
    }

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  public static function getTemplateDirs($moduleName)
  {
    $suffix = $moduleName . DS . sfConfig::get('sf_app_module_template_dir_name');
    // application
    $dirs = array_merge(array(), self::getApplicationDirectories($suffix));
    // custom modules
    $dirs = array_merge($dirs, self::getCustomDirectories($suffix));
    // plugins
    $dirs = array_merge($dirs, self::getPluginDirectories(sfConfig::get('sf_app_module_dir_name') . DS . $suffix));
    // core
    $dirs = array_merge($dirs, self::getCoreDirectories(sfConfig::get('sf_app_module_dir_name') . DS . $suffix));
    // generated templates in cache
    $dirs[] = sfConfig::get('sf_module_cache_dir') . DS . 'auto' . ucfirst($suffix);
    return $dirs;
  }

  /**
   * Gets the template directory to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template directory
   */
  public static function getTemplateDir($moduleName, $templateFile)
  {
    $dirs = self::getTemplateDirs($moduleName);
    foreach($dirs as $dir)
    {
      if(is_readable($dir . DS . $templateFile))
      {
        return $dir;
      }
    }
    return null;
  }

  /**
   * Gets the template to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template path
   */
  public static function getTemplatePath($moduleName, $templateFile)
  {
    $dir = self::getTemplateDir($moduleName, $templateFile);
    return $dir ? $dir . DS . $templateFile : null;
  }

  /**
   * Gets the i18n directory to use for a given module.
   *
   * @param string The module name
   *
   * @return string An i18n directory
   */
  public static function getI18NDir($moduleName)
  {
    $suffix = $moduleName . DS . sfConfig::get('sf_app_module_i18n_dir_name');

    // application
    if(is_dir($dir = sfConfig::get('sf_app_module_dir') . DS . $suffix))
    {
      return $dir;
    }

    $moduleDirName = sfConfig::get('sf_app_module_dir_name');

    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($dir = sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . $moduleDirName . DS . $suffix))
      {
        return $dir;
      }
    }

    if(is_dir($dir = sfConfig::get('sf_sift_data_dir') . DS . $moduleDirName . DS . $suffix))
    {
      return $dir;
    }
  }

  /**
   * Gets the i18n directories to use for a given module.
   *
   * @param string $moduleName The module name
   *
   * @return array An array of i18n directories
   */
  public static function getI18NDirs($moduleName)
  {
    $dirs = array();

    $suffix = $moduleName . DS . sfConfig::get('sf_app_module_i18n_dir_name');

    // application
    if(is_dir($dir = sfConfig::get('sf_app_module_dir') . DS . $suffix))
    {
      $dirs[] = $dir;
    }

    $moduleDirName = sfConfig::get('sf_app_module_dir_name');

    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($dir = sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . $moduleDirName . DS . $suffix))
      {
        $dirs[] = $dir;
      }
    }

    if(is_dir($dir = sfConfig::get('sf_sift_data_dir') . DS . $moduleDirName . DS . $suffix))
    {
      $dirs[] = $dir;
    }

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
   *
   * @return array An array of directories
   */
  public static function getGeneratorTemplateDirs($class, $theme)
  {
    // project
    $dirs = array(sfConfig::get('sf_data_dir') . DS . 'generator' . DS . $class . DS . $theme . DS . 'template');

    // plugins
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($pluginDir = (sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . 'data' .
                      DS . 'generator' . DS . $class . DS . $theme . DS . 'template')))
      {
        // plugin
        $dirs[] = $pluginDir;
      }
    }

    // core
    if(is_dir($dir = sfConfig::get('sf_sift_data_dir') . DS . 'generator' . DS . $class . DS .
                    $theme . DS . 'template'))
    {
      $dirs[] = $dir;
    }

    return $dirs;
  }

  /**
   * Gets directories where the skeleton is stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
   *
   * @return array An array of directories
   */
  public static function getGeneratorSkeletonDirs($class, $theme)
  {
    // project
    $dirs = array(sfConfig::get('sf_data_dir') . DS . 'generator' . DS . $class . DS . $theme . DS . 'skeleton');

    // plugins
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($pluginDir = (sfConfig::get('sf_plugins_dir') . DS . $plugin .
                DS . 'data' . DS . 'generator' . DS . $class . DS . $theme . DS . 'skeleton')))
      {
        $dirs[] = $pluginDir;
      }
    }

    if(is_dir($dir = sfConfig::get('sf_sift_data_dir') . DS . 'generator' . DS . $class . DS . $theme . DS . 'skeleton'))
    {
      $dirs[] = $dir;
    }

    return $dirs;
  }

  /**
   * Gets the template to use for a generator class.
   *
   * @param string The generator class name
   * @param string The theme name
   * @param string The template path
   *
   * @return string A template path
   *
   * @throws sfException
   */
  public static function getGeneratorTemplate($class, $theme, $path)
  {
    $dirs = self::getGeneratorTemplateDirs($class, $theme);
    foreach($dirs as $dir)
    {
      if(is_readable($dir . DS . $path))
      {
        return $dir . DS . $path;
      }
    }
    throw new sfException(sprintf('Unable to load "%s" generator template in: %s', $path, implode(', ', $dirs)));
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string The configuration path
   * @return array An array of paths
   */
  public static function getConfigPaths($configPath)
  {
    // fix for windows paths
    $configPath = str_replace('/', DS, $configPath);

    $rootDir = sfConfig::get('sf_root_dir');
    $appDir = sfConfig::get('sf_app_dir');
    $pluginsDir = sfConfig::get('sf_plugins_dir');

    $configName = basename($configPath);
    $globalConfigPath = basename(dirname($configPath)).DS.$configName;

    // sift core
    $files = array(
      // sift
      sfConfig::get('sf_sift_data_dir').DS.$globalConfigPath,
      // sift core modules
      sfConfig::get('sf_sift_data_dir').DS.$configPath,
    );

    // plugins, global
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      $files[] = $pluginsDir.DS.$plugin.DS.$globalConfigPath;
    }

    // project
    $files[] = $rootDir.DS.$globalConfigPath;
    $files[] = $rootDir.DS.$configPath;

    // application
    $files[] = $appDir.DS.$globalConfigPath;

    // generated modules
    if(strpos($configPath, sfConfig::get('sf_app_module_dir_name')) !== false)
    {
      // strip modules from the path which looks like:
      // moduleName/config/foo.yml
      // we need to convert it to: autoModuleName/config/foo.yml
      $generateConfig =
              'auto' . ucfirst(str_replace(sfConfig::get('sf_app_module_dir_name') . DS, '', $configPath));
      // generated modules
      $files[] = sfConfig::get('sf_module_cache_dir').DS.$generateConfig;
    }

    // plugins, but local
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      $files[] = $pluginsDir.DS.$plugin.DS.$configPath;
    }

    // module
    $files[] = $appDir.DS.$configPath;

    // If the configuration file can be overridden with a dimension, inject the appropriate path
    if((in_array($configName, self::$allConfigurationFiles) || (strpos($configPath, 'validate'))))
    {
      $dimensionDirs = sfConfig::get('sf_dimension_dirs');
      if(is_array($dimensionDirs) && !empty($dimensionDirs))
      {
        // reverse dimensions for proper cascading
        $dimensionDirs = array_reverse($dimensionDirs);

        $applicationDimensionDirectory = $appDir . DS . dirname($globalConfigPath) . DS . '%s' . DS . $configName;
        $moduleDimensionDirectory = $appDir . DS . dirname($configPath) . DS . '%s' . DS . $configName;

        foreach($dimensionDirs as $dimension)
        {
          // application
          if(in_array($configName, self::$allConfigurationFiles))
          {
            foreach($dimensionDirs as $dimension)
            {
              $files[] = sprintf($applicationDimensionDirectory, $dimension);
            }
          }

          // module
          if(in_array($configName, self::$moduleConfigurationFiles) || strpos($configPath, 'validate'))
          {
            foreach($dimensionDirs as $dimension)
            {
              $files[] = sprintf($moduleDimensionDirectory, $dimension);
            }
          }
        }
      }
    }

    $configs = array();
    foreach(array_unique($files) as $file)
    {
      // check existance
      if(is_readable($file))
      {
        $configs[] = $file;
      }
    }

    return $configs;
  }

  /**
   * Gets the helper directories for a given module name.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  public static function getHelperDirs($moduleName = '')
  {
    $dirs = array();

    if($moduleName)
    {
      if(is_dir($dir = sfConfig::get('sf_app_module_dir') . DS . $moduleName . DS .
              sfConfig::get('sf_app_module_lib_dir_name') . DS . 'helper'))
      {
        $dirs[] = $dir;
      }

      // plugins
      foreach(sfConfig::get('sf_plugins', array()) as $plugin)
      {
        if(is_dir($dir = (sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . 'modules' . DS .
                $moduleName . DS . 'lib' . DS . 'helper')))
        {
          $dirs[] = $dir;
        }
      }
    }

    // application
    if(is_dir($dir = sfConfig::get('sf_app_lib_dir') . DS . 'helper'))
    {
      $dirs[] = $dir;
    }

    // project
    if(is_dir($dir = sfConfig::get('sf_lib_dir') . DS . 'helper'))
    {
      $dirs[] = $dir;
    }

    // plugins
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if(is_dir($dir = sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . 'lib' . DS . 'helper'))
      {
        $dirs[] = $dir;
      }
    }

    // core
    $dirs[] = sfConfig::get('sf_sift_lib_dir') . DS . 'helper';
    return $dirs;
  }

  /**
   * Loads helpers.
   *
   * @param array  An array of helpers to load
   * @param string A module name (optional)
   *
   * @throws sfViewException
   */
  public static function loadHelpers($helpers, $moduleName = '')
  {
    $dirs = self::getHelperDirs($moduleName);

    foreach((array) $helpers as $helperName)
    {
      if(isset(self::$loadedHelpers[$helperName]))
      {
        continue;
      }

      $fileName = $helperName . 'Helper.php';
      foreach($dirs as $dir)
      {
        $included = false;
        if(is_readable($dir . DS . $fileName))
        {
          include($dir . DS . $fileName);
          $included = true;
          break;
        }
      }

      if(!$included)
      {
        // search in the include path
        if((@include('helper' . DS . $fileName)) === false)
        {
          $dirs = array_merge($dirs, explode(PATH_SEPARATOR, get_include_path()));
          // remove sf_root_dir from dirs
          foreach($dirs as &$dir)
          {
            $dir = str_replace('%SF_ROOT_DIR%', sfConfig::get('sf_root_dir'), $dir);
          }
          throw new sfViewException(sprintf('Unable to load "%sHelper.php" helper in: %s', $helperName, implode(', ', $dirs)));
        }
      }

      // mark as loaded
      self::$loadedHelpers[$helperName] = true;
    }
  }

  /**
   * Returns application directories for given path suffix
   *
   * @param string $suffix Path suffix to look for
   * @param boolean $dimensions Look for dimensions directories?
   * @return array
   */
  protected static function getApplicationDirectories($suffix, $dimensions = true)
  {
    $appModuleDir = sfConfig::get('sf_app_module_dir');
    $dimensionDirs = sfConfig::get('sf_dimension_dirs', array());
    $dirs = array();
    if($dimensions && is_array($dimensionDirs))
    {
      foreach($dimensionDirs as $dimension)
      {
        if(is_dir($dir = $appModuleDir . DS . $suffix . DS . $dimension))
        {
          $dirs[] = $dir;
        }
      }
    }
    // application
    $dirs[] = $appModuleDir . DS . $suffix;
    return $dirs;
  }

  /**
   * Returns plugin directories for given path suffix
   *
   * @param string $suffix Path suffix to look for
   * @param boolean $dimensions Look for dimensions directories?
   * @return array
   */
  protected static function getPluginDirectories($suffix, $dimensions = true)
  {
    $dimensionDirs = sfConfig::get('sf_dimension_dirs');
    $pluginsDir = sfConfig::get('sf_plugins_dir');
    $dirs = array();
    foreach(sfConfig::get('sf_plugins', array()) as $plugin)
    {
      if($dimensions && is_array($dimensionDirs))
      {
        foreach($dimensionDirs as $dimension)
        {
          if(is_dir($dir = $pluginsDir . DS . $plugin . DS . $suffix . DS . $dimension))
          {
            // enable checks if true for plugin
            $dirs[] = $dir;
          }
        }
      }
      if(is_dir($dir = (sfConfig::get('sf_plugins_dir') . DS . $plugin . DS . $suffix)))
      {
        $dirs[] = $dir;
      }
    }
    return $dirs;
  }

  /**
   * Returns custom directories for $suffix. Takes "sf_module_dirs" setting and looks
   * for the directories. Also handles dimension directories setting "sf_dimension_dirs"
   *
   * @param string $suffix Path suffix to look for
   * @param boolean $dimensions Look for dimensions directories?
   * @return array
   */
  protected static function getCustomDirectories($suffix, $dimensions = true)
  {
    $dimensionDirs = sfConfig::get('sf_dimension_dirs');
    $dirs = array();

    // check sf_module_dirs
    foreach(sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      if(is_numeric($key))
      {
        $key = $value;
      }

      // add paths for each dimension to search for new controller
      if($dimensions && is_array($dimensionDirs))
      {
        foreach($dimensionDirs as $dimension)
        {
          if(is_dir($dir = $key . DS . $suffix . DS . $dimension))
          {
            $dirs[] = $dir;
          }
        }
      }

      if(is_dir($dir = $key . DS . $suffix))
      {
        $dirs[] = $dir;
      }
    }

    return $dirs;
  }

  /**
   * Returns Sift core directories for given path suffix
   *
   * @param string $suffix Path suffix to look for
   * @param boolean $dimensions Look for dimensions directories?
   * @return array
   */
  protected static function getCoreDirectories($suffix, $dimensions = true)
  {
    $dimensionDirs = sfConfig::get('sf_dimension_dirs', array());
    $dataDir = sfConfig::get('sf_sift_data_dir');

    $dirs = array();

    if($dimensions && is_array($dimensionDirs))
    {
      foreach($dimensionDirs as $dimension)
      {
        if(is_dir($dir = $dataDir . DS . $suffix . DS . $dimension))
        {
          $dirs[] = $dir;
        }
      }
    }

    // core modules
    if(is_dir($dir = $dataDir . DS . $suffix))
    {
      $dirs[] = $dir;
    }

    return $dirs;
  }

}
