<?php

$app = 'frontend';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();

$b->get('/');

$t = $b->test();

// simple configuration files
$t->diag('sfLoader::getConfigDirs()');
$t->is(get_config_dirs('config/filters.yml'), array(
  'SIFT/config/filters.yml',
  'PROJECT/plugins/sfConfigPlugin/config/filters.yml',
  'PROJECT/config/filters.yml',
  'PROJECT/apps/frontend/config/filters.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

// configuration files for modules
$t->is(get_config_dirs('modules/sfConfigPlugin/config/view.yml'), array(
  'SIFT/config/view.yml',
  'PROJECT/plugins/sfConfigPlugin/config/view.yml',
  'PROJECT/config/view.yml',
  'PROJECT/apps/frontend/config/view.yml',
  'PROJECT/plugins/sfConfigPlugin/modules/sfConfigPlugin/config/view.yml',
  'PROJECT/apps/frontend/modules/sfConfigPlugin/config/view.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

// nested configuration files
$t->is(get_config_dirs('config/dirmyconfig/myconfig.yml'), array(
  'PROJECT/config/dirmyconfig/myconfig.yml',
  'PROJECT/plugins/sfConfigPlugin/config/dirmyconfig/myconfig.yml',
  'PROJECT/apps/frontend/config/dirmyconfig/myconfig.yml',
), 'sfLoader::getConfigDirs() returns directories for configuration files'
);

function get_config_dirs($configPath)
{
  $dirs = array();
  foreach (sfLoader::getConfigPaths($configPath) as $dir)
  {
    $dirs[] = $dir;
  }

  return array_map('strip_paths', $dirs);
}

function strip_paths($f)
{
  $f = str_replace(
    array(sfConfig::get('sf_sift_data_dir'), sfConfig::get('sf_root_dir'), DIRECTORY_SEPARATOR),
    array('SIFT', 'PROJECT', '/'),
    $f);

  return $f;
}
