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
   * Gets directories where lib files are stored for a given module.
   *
   * @param string $moduleName The module name
   * @return array An array of directories
   */
  public static function getLibDirs($moduleName)
  {
    $dirs = array();
    
    // application
    $dirs[] = sfConfig::get('sf_app_module_dir') . DS . $moduleName . DS . 'lib';
    $dirs = array_merge($dirs, glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'modules' . DS . $moduleName . DS . 'lib'));
    // generated templates in cache
    $dirs[] = sfConfig::get('sf_module_cache_dir') . DS . 'auto' . ucfirst($moduleName) . DS . 'lib';   
    
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
    $dirs = array(sfConfig::get('sf_lib_dir') . DS . 'model' ? sfConfig::get('sf_lib_dir') . DS . 'model' : 'lib' . DS . 'model');
    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'lib' . DS . 'model'))
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
  public static function getControllerDirs($moduleName)
  {
    $suffix = $moduleName . DS . sfConfig::get('sf_app_module_action_dir_name');

    $sf_app_module_dir = sfConfig::get('sf_app_module_dir');

    $dirs = array();
    
    // Add paths for each dimension to search for new controller     
    $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');
    if(is_array($sf_dimension_dirs))
    {
      foreach($sf_dimension_dirs as $dimension)
      {
        $dirs[$sf_app_module_dir . DS . $suffix . DS . $dimension] = true;
      }
    }

    foreach(sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[$key . DS . $suffix] = $value;
    }

    // application
    $dirs[$sf_app_module_dir . DS . $suffix] = false; 

    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'modules' . DS . $suffix))
    {
      // plugins
      $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), true)));
    }

    // core modules
    $dirs[sfConfig::get('sf_sift_data_dir') . DS . 'modules' . DS . $suffix] = true;                            

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

    $sf_app_module_dir = sfConfig::get('sf_app_module_dir');
    $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');

    $dirs = array();
    // add paths for each dimension to search for new templates
    foreach(sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      if(is_array($sf_dimension_dirs))
      {
        foreach($sf_dimension_dirs as $dimension)
        {
          $dirs[] = $key . DS . $suffix . DS . $dimension;
        }
      }
      $dirs[] = $key . DS . $suffix;
    }

    if(is_array($sf_dimension_dirs))
    {
      foreach($sf_dimension_dirs as $dimension)
      {
        $dirs[] = $sf_app_module_dir . DS . $suffix . DS . $dimension;
      }
    }

    // application
    $dirs[] = $sf_app_module_dir . DS . $suffix;
    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'modules' . DS . $suffix))
    {
      // plugins
      $dirs = array_merge($dirs, $pluginDirs);                                       
    }

    // core modules
    $dirs[] = sfConfig::get('sf_sift_data_dir') . DS . 'modules' . DS . $suffix;           
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
    $dir = sfConfig::get('sf_app_module_dir') . DS . $suffix;
    if(is_dir($dir))
    {
      return $dir;
    }
    
    // plugins
    $dirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'modules' . DS . $suffix);
    if(isset($dirs[0]))
    {
      return $dirs[0];
    }
    
    $dirs = glob(sfConfig::get('sf_sift_data_dir') . DS . 'modules' . DS . $suffix);
    
    if(isset($dirs[0]))
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
  public static function getGeneratorTemplateDirs($class, $theme)
  {
    // project
    $dirs = array(sfConfig::get('sf_data_dir') . DS . 'generator' . DS . $class . DS . $theme . DS . 'template');

    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'data' . DS . 'generator' . DS . $class . DS . $theme . DS . 'template'))
    {
      // plugin
      $dirs = array_merge($dirs, $pluginDirs);
    }

    // default theme
    $dirs[] = sfConfig::get('sf_sift_data_dir') . DS . 'generator' . DS . $class . DS . 'default' . DS . 'template';

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

    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'data' . DS . 'generator' . DS . $class . DS . $theme . DS . 'skeleton'))
    {
      // plugin
      $dirs = array_merge($dirs, $pluginDirs);
    }

    // default theme
    $dirs[] = sfConfig::get('sf_sift_data_dir') . DS . 'generator' . DS . $class . DS . 'default' . DS . 'skeleton';
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
   *
   * @return array An array of paths
   */
  public static function getConfigPaths($configPath)
  {
    // fix for windows paths
    $configPath = str_replace('/', DS, $configPath);

    $sf_sift_data_dir = sfConfig::get('sf_sift_data_dir');
    $sf_root_dir = sfConfig::get('sf_root_dir');
    $sf_app_dir = sfConfig::get('sf_app_dir');
    $sf_plugins_dir = sfConfig::get('sf_plugins_dir');

    $configName = basename($configPath);
    $globalConfigPath = basename(dirname($configPath)) . DS . $configName;

    $files = array(
        // sift
        $sf_sift_data_dir . DS . $globalConfigPath,
         // core modules
        $sf_sift_data_dir . DS . $configPath,
    );

    if($pluginDirs = glob($sf_plugins_dir . DS . '*' . DS . $globalConfigPath))
    {
      // plugins
      $files = array_merge($files, $pluginDirs);
    }

    $files = array_merge($files, array(
        $sf_root_dir . DS . $globalConfigPath, // project
        $sf_root_dir . DS . $configPath, // project
        $sf_app_dir . DS . $globalConfigPath, // application
            // disable generated module
            // sfConfig::get('sf_cache_dir').DS.$configPath,                                 // generated modules
    ));

    if($pluginDirs = glob($sf_plugins_dir . DS . '*' . DS . $configPath))
    {
      $files = array_merge($files, $pluginDirs);                                     // plugins
    }

    $files[] = $sf_app_dir . DS . $configPath;                          // module

    // If the configuration file can be overridden with a dimension, inject the appropriate path
    $applicationConfigurationFiles = array('app.yml', 'factories.yml', 'filters.yml', 'i18n.yml', 'logging.yml', 'settings.yml', 'databases.yml', 'routing.yml');
    $moduleConfigurationFiles = array('cache.yml', 'module.yml', 'security.yml', 'view.yml');

    $configurationFiles = array_merge($applicationConfigurationFiles, $moduleConfigurationFiles);

    if((in_array($configName, $configurationFiles) || (strpos($configPath, 'validate'))))
    {
      $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs');

      if(is_array($sf_dimension_dirs) && !empty($sf_dimension_dirs))
      {
        $sf_dimension_dirs = array_reverse($sf_dimension_dirs);     // reverse dimensions for proper cascading

        $applicationDimensionDirectory = $sf_app_dir . DS . dirname($globalConfigPath) . DS . '%s' . DS . $configName;
        $moduleDimensionDirectory = $sf_app_dir . DS . dirname($configPath) . DS . '%s' . DS . $configName;

        foreach($sf_dimension_dirs as $dimension)
        {
          if(in_array($configName, $configurationFiles))       // application
          {
            foreach($sf_dimension_dirs as $dimension)
            {
              $files[] = sprintf($applicationDimensionDirectory, $dimension);
            }
          }

          if(in_array($configName, $moduleConfigurationFiles) || strpos($configPath, 'validate'))      // module
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
  public static function getHelperDirs($moduleName = '')
  {
    $dirs = array();

    if($moduleName)
    {
      // module
      $dirs[] = sfConfig::get('sf_app_module_dir') . DS . $moduleName . DS . sfConfig::get('sf_app_module_lib_dir_name') . DS . 'helper';

      if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'modules' . DS . $moduleName . DS . 'lib' . DS . 'helper'))
      {
        // module plugins
        $dirs = array_merge($dirs, $pluginDirs);
      }
    }

    // application
    $dirs[] = sfConfig::get('sf_app_lib_dir') . DS . 'helper';
    // project
    $dirs[] = sfConfig::get('sf_lib_dir') . DS . 'helper';

    if($pluginDirs = glob(sfConfig::get('sf_plugins_dir') . DS . '*' . DS . 'lib' . DS . 'helper'))
    {
       // plugins
      $dirs = array_merge($dirs, $pluginDirs);
    }

    // global
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
    static $loaded = array();

    $dirs = self::getHelperDirs($moduleName);
    foreach((array) $helpers as $helperName)
    {
      if(isset($loaded[$helperName]))
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
        if((@include('helper' . DS . $fileName)) != 1)
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
