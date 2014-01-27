<?php

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(19, new lime_output_color());

$rootDir = dirname(__FILE__) . '/fixtures';

$dimension_string = '';

$sf_app = 'front';
$sf_root_cache_dir = $rootDir . '/cache';
$sf_apps_dir = $rootDir . '/apps';

// simulate environment
sfConfig::add(array(
  'sf_test' => false,
  'sf_data_dir_name' => 'data',
  'sf_cache_dir_name' => 'cache',
  'sf_config_dir_name' => 'config',
  'sf_apps_dir_name' => 'apps',
  'sf_test_dir_name' => 'test',
  'sf_doc_dir_name' => 'doc',
  'sf_bin_dir_name' => 'batch',
  'sf_log_dir_name' => 'log',
  'sf_lib_dir_name' => 'lib',
  'sf_plugins_dir_name' => 'plugins',
  'sf_web_dir_name' => 'web',
  'sf_model_dir_name' => 'model',
  'sf_upload_dir_name' => 'files',
  'sf_i18n_dir_name' => 'i18n',

  // application wide configuration
  'sf_app_i18n_dir_name' => 'i18n',
  'sf_app_config_dir_name' => 'config',
  'sf_app_lib_dir_name' => 'lib',
  'sf_app_module_dir_name' => 'modules',
  'sf_app_template_dir_name' => 'templates',

  // module wide configuration
  'sf_app_module_action_dir_name' => 'actions',
  'sf_app_module_template_dir_name' => 'templates',
  'sf_app_module_lib_dir_name' => 'lib',
  'sf_app_module_view_dir_name' => 'views',
  'sf_app_module_validate_dir_name' => 'validate',
  'sf_app_module_config_dir_name' => 'config',
  'sf_app_module_i18n_dir_name' => 'i18n',

  'sf_sift_data_dir' => $sf_sift_data_dir,
  'sf_sift_lib_dir' => $sf_sift_lib_dir,
  'sf_root_dir' => $rootDir,
  'sf_plugins_dir' => $rootDir . '/plugins',
  'sf_app_lib_dir_name' => $sf_app_lib_dir_name = 'lib',
  'sf_app_module_dir_name' => $sf_app_module_dir_name = 'modules',
  'sf_app_config_dir_name' => $sf_app_config_dir_name = 'config',
  'sf_app_i18n_dir_name' => $sf_app_i18n_dir_name = 'i18n',
  'sf_module_cache_dir' => null,
  'sf_dimension' => null,
  'sf_config_dir_name' => 'config',
  'sf_app_template_dir_name' => $sf_app_template_dir_name = 'templates',
  // stores the dimension directories that sift will search through
  'sf_dimension_dirs' => array(),
  'sf_cache_dir' => $sf_cache_dir = $rootDir . DS . 'dev' . ($dimension_string ? DS . $dimension_string : ''),
  'sf_template_cache_dir' => $sf_cache_dir . DS . 'template',
  'sf_i18n_cache_dir' => $sf_cache_dir . DS . 'i18n',
  'sf_config_cache_dir' => $sf_cache_dir . DS . 'config',
  'sf_test_cache_dir' => $sf_cache_dir . DS . 'test',
  'sf_module_cache_dir' => $sf_cache_dir . DS . 'modules',
  // SF_APP_DIR directory structure
  'sf_cache_dir' => $sf_cache_dir . DS . 'front' . DS . 'dev' . ($dimension_string ? DS . $dimension_string : ''),
  'sf_debug' => true,
  'sf_environment' => 'dev',
  'sf_base_cache_dir' => $sf_root_cache_dir . DS . $sf_app,
  'sf_app_dir' => $sf_app_dir = ($sf_apps_dir . DS . $sf_app),
  'sf_app_config_dir' => $sf_app_dir . DS . $sf_app_config_dir_name,
  'sf_app_lib_dir' => $sf_app_dir . DS . $sf_app_lib_dir_name,
  'sf_app_module_dir' => $sf_app_dir . DS . $sf_app_module_dir_name,
  'sf_app_template_dir' => $sf_app_dir . DS . $sf_app_template_dir_name,
  'sf_app_i18n_dir' => $sf_app_dir . DS . $sf_app_i18n_dir_name,

  'sf_data_dir' => ($rootDir.DS.'data'),
  'sf_lib_dir' => $sf_lib_dir = ($rootDir.DS.'lib'),
  'sf_model_lib_dir' => $sf_lib_dir . DS . 'model',
  'sf_plugins' => array(
    'myFooBarPlugin'
  )

));

// simple configuration files
$t->diag('sfLoader::getConfigDirs()');

sfLoader::resetCache();

// lib directories
$t->is(get_lib_dirs('foobar'), array(
 'PROJECT/apps/front/modules/foobar/lib',
 'PROJECT/plugins/myFooBarPlugin/modules/foobar/lib',
 'PROJECT/dev/modules/autoFoobar/lib',
), 'sfLoader::getLibDirs() returns directories');

$t->is(get_config_dirs('config/settings.yml'), array(
  'SIFT/config/settings.yml',
  'PROJECT/plugins/myFooBarPlugin/config/settings.yml',
  'PROJECT/config/settings.yml',
  'PROJECT/apps/front/config/settings.yml',
), 'sfLoader::getConfigDirs() returns configuration files');

$plugins = sfConfig::get('sf_plugins');
sfConfig::set('sf_plugins', array());

$t->is(get_config_dirs('config/settings.yml'), array(
  'SIFT/config/settings.yml',
  'PROJECT/config/settings.yml',
  'PROJECT/apps/front/config/settings.yml',
), 'sfLoader::getConfigDirs() returns  for configuration files');

// put the config back
sfConfig::set('sf_plugins', $plugins);

$t->is(get_model_dirs(), array(
  'PROJECT/lib/model',
  'PROJECT/plugins/myFooBarPlugin/lib/model',
), 'sfLoader::getModelDirs() returns directories for model files');

$t->is(get_controllers_dirs('foobar'), array(
  'PROJECT/apps/front/modules/foobar/actions',
  'PROJECT/plugins/myFooBarPlugin/modules/foobar/actions',
), 'sfLoader::getControllersDirs() returns directories for controllers');

// sf_module_dir
$t->diag('sf_module_dirs');

// we specify another module directory to look for
sfConfig::set('sf_module_dirs', array(
  $rootDir . '/data/customized/modules'
));

$t->is(get_controllers_dirs('foobar'), array(
  'PROJECT/apps/front/modules/foobar/actions',
  'PROJECT/data/customized/modules/foobar/actions',
  'PROJECT/plugins/myFooBarPlugin/modules/foobar/actions',
), 'sfLoader::getControllersDirs() returns directories for controllers with sf_module_dirs setting');

// with dimension

sfConfig::set('sf_dimension_dirs', array(
  'corporate'
));

$t->is(get_controllers_dirs('foobar'), array(
  'PROJECT/apps/front/modules/foobar/actions/corporate',
  'PROJECT/apps/front/modules/foobar/actions',
  'PROJECT/data/customized/modules/foobar/actions/corporate',
  'PROJECT/data/customized/modules/foobar/actions',
  'PROJECT/plugins/myFooBarPlugin/modules/foobar/actions/corporate',
  'PROJECT/plugins/myFooBarPlugin/modules/foobar/actions'
), 'sfLoader::getControllersDirs() returns directories for controllers with dimensions set');

// reset the setting
sfConfig::set('sf_module_dirs', array());

$t->is(get_template_dirs('foobar'), array(
  'PROJECT/apps/front/modules/foobar/templates/corporate',
  'PROJECT/apps/front/modules/foobar/templates',
  'PROJECT/plugins/myFooBarPlugin/modules/foobar/templates',
  'PROJECT/dev/modules/autoFoobar/templates',
), 'sfLoader::getTemplateDirs() returns directories for controllers');

$t->is(strip_paths(sfLoader::getTemplateDir('foobar', 'indexSuccess.php')),
        'PROJECT/apps/front/modules/foobar/templates/corporate', 'sfLoader::getTemplateDir() works ok with dimensions set');

// reset dimensions
sfConfig::set('sf_dimension_dirs', array());

sfLoader::resetCache();
$t->is(strip_paths(sfLoader::getTemplatePath('foobar', 'indexSuccess.php')), 'PROJECT/plugins/myFooBarPlugin/modules/foobar/templates/indexSuccess.php', 'sfLoader::getTemplateDir() works ok without dimensions');

$t->is(strip_paths(sfLoader::getI18NDir('foobar')), 'PROJECT/apps/front/modules/foobar/i18n',
        'sfLoader::getI18NDir() works ok');

$t->is(get_generator_template_dirs('sfGenerator', 'default'), array(
    'PROJECT/data/generator/sfGenerator/default/template'
), 'sfLoader::getGeneratorTemplateDirs() works ok');

$t->is(get_generator_skeleton_dirs('sfGenerator', 'default'), array(
  'PROJECT/data/generator/sfGenerator/default/skeleton'
), 'sfLoader::getGeneratorTemplateDirs() works ok');

try {
  sfLoader::getGeneratorTemplate('sfGenerator', 'default', 'invalid.php');
  $t->fail('sfLoader::getGeneratorTemplate() throws exception if template does not exist');
}
catch(sfException $e)
{
  $t->pass('sfLoader::getGeneratorTemplate() throws exception if template does not exist');
}

try {
  $tpl = sfLoader::getGeneratorTemplate('sfGenerator', 'default', 'indexSuccess.php');
  $t->pass('sfLoader::getGeneratorTemplate() throws exception if template does not exist');
  $t->is(strip_paths($tpl), 'PROJECT/data/generator/sfGenerator/default/template/indexSuccess.php', 'sfLoader::getGeneratorTemplate() returns path to a template');
}
catch(sfException $e)
{
  throw $e;
  $t->fail('sfLoader::getGeneratorTemplate() throws exception if template does not exist');
  $t->skip('', 1);
}

$t->is(get_helper_dirs(), array(
  'PROJECT/lib/helper',
  'SIFT_LIB/helper'
), 'sfLoader::getHelperDirs() works ok');

$t->is(get_helper_dirs('foobar'), array(
  'PROJECT/apps/front/modules/foobar/lib/helper',
  'PROJECT/lib/helper',
  'SIFT_LIB/helper'
), 'sfLoader::getHelperDirs() works ok');

//
sfLoader::loadHelpers('Foobar');
$t->is(function_exists('foobar_helper'), true, 'loadHelpers works ok');

function get_lib_dirs($module)
{
  sfLoader::resetCache();
  $dirs = sfLoader::getLibDirs($module);
  return array_map('strip_paths', $dirs);
}

function get_model_dirs()
{
  sfLoader::resetCache();
  $dirs = sfLoader::getModelDirs();
  return array_map('strip_paths', $dirs);
}

function get_config_dirs($configPath)
{
  sfLoader::resetCache();
  $dirs = sfLoader::getConfigPaths($configPath);
  return array_map('strip_paths', $dirs);
}

function get_template_dirs($module)
{
  sfLoader::resetCache();
  $dirs = sfLoader::getTemplateDirs($module);
  return array_map('strip_paths', $dirs);
}

function get_generator_template_dirs($class, $theme)
{
  sfLoader::resetCache();
  $dirs = sfLoader::getGeneratorTemplateDirs($class, $theme);
  return array_map('strip_paths', $dirs);
}

function get_generator_skeleton_dirs($class, $theme)
{
  sfLoader::resetCache();
  $dirs = sfLoader::getGeneratorSkeletonDirs($class, $theme);
  return array_map('strip_paths', $dirs);
}

function get_controllers_dirs($module)
{
  sfLoader::resetCache();
  $dirs = array();
  foreach (sfLoader::getControllerDirs($module) as $dir => $flag)
  {
    $dirs[] = $dir;
  }
  return array_map('strip_paths', $dirs);
}

function get_helper_dirs($module = '')
{
  sfLoader::resetCache();
  $dirs = sfLoader::getHelperDirs($module);
  return array_map('strip_paths', $dirs);
}

function strip_paths($f)
{
  $f = str_replace(
    array(sfConfig::get('sf_sift_lib_dir'), sfConfig::get('sf_sift_data_dir'), sfConfig::get('sf_root_dir'), DIRECTORY_SEPARATOR),
    array('SIFT_LIB', 'SIFT', 'PROJECT', '/'),
    $f);
  return $f;
}
