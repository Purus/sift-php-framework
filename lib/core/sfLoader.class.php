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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @author     Mishal.cz <mishal@mishal.cz>
 */
class sfLoader
{
  /**
   * Gets directories where lib files are stored for a given module.
   *
   * @param string $moduleName The module name
   * @return array An array of directories
   */
  public static function getLibDirs($moduleName)
  {
    $dirs = array();

    $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/lib';                  // application
    $dirs = array_merge($dirs, glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'lib'));
    $dirs[] = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($moduleName.'/lib');   // generated templates in cache

    return $dirs;
  }
  
  /**
   * Gets directories where model classes are stored.
   *
   * @return array An array of directories
   */
  static public function getModelDirs()
  {
     // project
    $dirs = array(sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'model' ? sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'model' : 'lib'.DIRECTORY_SEPARATOR.'model');
    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'model'))
    {
      // plugins
      $dirs = array_merge($dirs, $pluginDirs);
    }
    return $dirs;
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  static public function getControllerDirs($moduleName)
  {
    $suffix = $moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_action_dir_name');

    $sf_app_module_dir = sfConfig::get('sf_app_module_dir');

    $dirs = array();

    /**
     * Add paths for each dimension to search for new controller
     */
    $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');
    if(is_array($sf_dimension_dirs))
    {
      foreach($sf_dimension_dirs as $dimension)
      {
        $dirs[$sf_app_module_dir.DIRECTORY_SEPARATOR.$suffix.DIRECTORY_SEPARATOR.$dimension] = true;
      }
    }

    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[$key.DIRECTORY_SEPARATOR.$suffix] = $value;
    }

    $dirs[$sf_app_module_dir.DIRECTORY_SEPARATOR.$suffix] = false;                                     // application

    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix))
    {
      $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), true))); // plugins
    }

    $dirs[sfConfig::get('sf_sift_data_dir').DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix] = true;                            // core modules

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  static public function getTemplateDirs($moduleName)
  {
    $suffix = $moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name');

    $sf_app_module_dir = sfConfig::get('sf_app_module_dir');

    $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');

    $dirs = array();

    /**
     * Add paths for each dimension to search for new templates
     */
    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      if(is_array($sf_dimension_dirs))
      {
        foreach($sf_dimension_dirs as $dimension)
        {
          $dirs[] = $key.DIRECTORY_SEPARATOR.$suffix.DIRECTORY_SEPARATOR.$dimension;
        }
      }
      $dirs[] = $key.DIRECTORY_SEPARATOR.$suffix;
    }

    if(is_array($sf_dimension_dirs))
    {
      foreach($sf_dimension_dirs as $dimension)
      {
        $dirs[] = $sf_app_module_dir.DIRECTORY_SEPARATOR.$suffix.DIRECTORY_SEPARATOR.$dimension;
      }
    }

    $dirs[] = $sf_app_module_dir.DIRECTORY_SEPARATOR.$suffix;                        // application

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                       // plugins
    }

    $dirs[] = sfConfig::get('sf_sift_data_dir').DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix;              // core modules
    $dirs[] = sfConfig::get('sf_module_cache_dir').DIRECTORY_SEPARATOR.'auto'.ucfirst($suffix);         // generated templates in cache

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
  static public function getTemplateDir($moduleName, $templateFile)
  {
    $dirs = self::getTemplateDirs($moduleName);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.DIRECTORY_SEPARATOR.$templateFile))
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
  static public function getTemplatePath($moduleName, $templateFile)
  {
    $dir = self::getTemplateDir($moduleName, $templateFile);

    return $dir ? $dir.DIRECTORY_SEPARATOR.$templateFile : null;
  }

  /**
   * Gets the i18n directory to use for a given module.
   *
   * @param string The module name
   *
   * @return string An i18n directory
   */
  static public function getI18NDir($moduleName)
  {
    $suffix = $moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name');

    // application
    $dir = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$suffix;
    if (is_dir($dir))
    {
      return $dir;
    }

    // plugins
    $dirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix);
    if (isset($dirs[0]))
    {
      return $dirs[0];
    }
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
   *
   * @return array An array of directories
   */
  static public function getGeneratorTemplateDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'template');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'template'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    $dirs[] = sfConfig::get('sf_sift_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'template';                  // default theme

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
  static public function getGeneratorSkeletonDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'skeleton');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'skeleton'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    $dirs[] = sfConfig::get('sf_sift_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'skeleton';                  // default theme

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
  static public function getGeneratorTemplate($class, $theme, $path)
  {
    $dirs = self::getGeneratorTemplateDirs($class, $theme);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.DIRECTORY_SEPARATOR.$path))
      {
        return $dir.DIRECTORY_SEPARATOR.$path;
      }
    }

    throw new sfException(sprintf('Unable to load "%s" generator template in: %s', $path, implode(', ', $dirs)));
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string The configuration path
   *
   * @return array An array of paths
   */
  static public function getConfigPaths($configPath)
  {
    // fix for windows paths
    $configPath = str_replace('/', DIRECTORY_SEPARATOR, $configPath);

    $sf_sift_data_dir = sfConfig::get('sf_sift_data_dir');
    $sf_root_dir = sfConfig::get('sf_root_dir');
    $sf_app_dir = sfConfig::get('sf_app_dir');
    $sf_plugins_dir = sfConfig::get('sf_plugins_dir');

    $configName       = basename($configPath);
    $globalConfigPath = basename(dirname($configPath)).DIRECTORY_SEPARATOR.$configName;

    $files = array(
      $sf_sift_data_dir.DIRECTORY_SEPARATOR.$globalConfigPath,                    // sift
      $sf_sift_data_dir.DIRECTORY_SEPARATOR.$configPath,                          // core modules
    );

    if($pluginDirs = glob($sf_plugins_dir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$globalConfigPath))
    {
      $files = array_merge($files, $pluginDirs);                                     // plugins
    }

    $files = array_merge($files, array(
      $sf_root_dir.DIRECTORY_SEPARATOR.$globalConfigPath,                            // project
      $sf_root_dir.DIRECTORY_SEPARATOR.$configPath,                                  // project
      $sf_app_dir.DIRECTORY_SEPARATOR.$globalConfigPath,                             // application
      // disable generated module
      // sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.$configPath,                                 // generated modules
    ));

    if($pluginDirs = glob($sf_plugins_dir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$configPath))
    {
      $files = array_merge($files, $pluginDirs);                                     // plugins
    }

    $files[] = $sf_app_dir.DIRECTORY_SEPARATOR.$configPath;                          // module

    /**
     * If the configuration file can be overridden with a dimension, inject the appropriate path
     */
    $applicationConfigurationFiles = array('app.yml', 'factories.yml', 'filters.yml', 'i18n.yml', 'logging.yml', 'settings.yml', 'databases.yml', 'routing.yml');
    $moduleConfigurationFiles = array('cache.yml', 'module.yml', 'security.yml', 'view.yml');

    $configurationFiles = array_merge($applicationConfigurationFiles, $moduleConfigurationFiles);

    if((in_array($configName, $configurationFiles) || (strpos($configPath, 'validate'))))
    {
      $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');

      if(is_array($sf_dimension_dirs) && !empty($sf_dimension_dirs))
      {
        $sf_dimension_dirs = array_reverse($sf_dimension_dirs);     // reverse dimensions for proper cascading

        $applicationDimensionDirectory = $sf_app_dir.DIRECTORY_SEPARATOR.dirname($globalConfigPath).DIRECTORY_SEPARATOR.'%s'.DIRECTORY_SEPARATOR.$configName;
        $moduleDimensionDirectory = $sf_app_dir.DIRECTORY_SEPARATOR.dirname($configPath).DIRECTORY_SEPARATOR.'%s'.DIRECTORY_SEPARATOR.$configName;

        foreach($sf_dimension_dirs as $dimension)
        {
          if(in_array($configName, $configurationFiles))							// application
          {
            foreach ($sf_dimension_dirs as $dimension)
            {             
              $files[] = sprintf($applicationDimensionDirectory, $dimension);
            }
          }

          if(in_array($configName, $moduleConfigurationFiles) || strpos($configPath, 'validate'))	     // module
          {
            foreach($sf_dimension_dirs as $dimension)
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
  static public function getHelperDirs($moduleName = '')
  {
    $dirs = array();

    if ($moduleName)
    {
      $dirs[] = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_lib_dir_name').DIRECTORY_SEPARATOR.'helper'; // module

      if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
      {
        $dirs = array_merge($dirs, $pluginDirs);                                                                              // module plugins
      }
    }

    $dirs[] = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'helper';                                                                      // application

    $dirs[] = sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'helper';                                                                          // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                                // plugins
    }

    $dirs[] = sfConfig::get('sf_sift_lib_dir').DIRECTORY_SEPARATOR.'helper';                                                                  // global

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
  static public function loadHelpers($helpers, $moduleName = '')
  {
    static $loaded = array();

    $dirs = self::getHelperDirs($moduleName);
    foreach ((array) $helpers as $helperName)
    {
      if(isset($loaded[$helperName]))
      {
        continue;
      }

      $fileName = $helperName.'Helper.php';
      foreach ($dirs as $dir)
      {
        $included = false;
        if (is_readable($dir.DIRECTORY_SEPARATOR.$fileName))
        {
          include($dir.DIRECTORY_SEPARATOR.$fileName);
          $included = true;
          break;
        }
      }

      if (!$included)
      {
        // search in the include path
        if ((@include('helper'.DIRECTORY_SEPARATOR.$fileName)) != 1)
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

      $loaded[$helperName] = true;
    }
  }
}
