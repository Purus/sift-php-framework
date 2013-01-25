<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new module.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliGenerateModuleTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
      new sfCliCommandArgument('module', sfCliCommandArgument::REQUIRED, 'The module name'),
    ));
    
    $this->addOptions(array(
      new sfCliCommandOption('secured', null, sfCliCommandOption::PARAMETER_NONE, 'Secure the module?', null),
      new sfCliCommandOption('internal', null, sfCliCommandOption::PARAMETER_NONE, 'IS the module internal only? (Not accessible via web)', null),
      new sfCliCommandOption('credentials', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'User credentials for accessing the module', ''),
    ));

    $this->namespace = 'generate';
    $this->name = 'module';

    $this->briefDescription = 'Generates a new module';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [generate:module|INFO] task creates the basic directory structure
for a new module in an existing application:

  [{$scriptName} generate:module front article|INFO]

The task can also change the author name found in the [actions.class.php|COMMENT]
if you have configure it in [config/properties.ini|COMMENT]:

  [[project]
    name=myCoolWebApp
    author=my name <me@mydomain.com>|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/module|COMMENT] directory.

The task also creates a functional test stub named
[%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php|COMMENT]
that does not pass by default.

If a module with the same name already exists in the application,
it throws a [sfCliCommandException|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app    = $arguments['application'];
    $module = $arguments['module'];

    // Validate the module name
    if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $module))
    {
      throw new sfCliCommandException(sprintf('The module name "%s" is invalid.', $module));
    }

    $this->checkAppExists($app);
    
    $moduleDir = $this->environment->get('sf_apps_dir'). '/' . $app. '/' . 
                 $this->environment->get('sf_app_module_dir_name') . '/' . $module;

    if(is_dir($moduleDir))
    {
      throw new sfCliCommandException(sprintf('The module "%s" already exists in the "%s" application.', $module, $app));
    }

    $this->logSection($this->getFullName(), sprintf('Creating module "%s".', $module));
    
    $projectName = '';
    $projectAuthor = '';
    
    // load configuration
    if(is_readable($propertyFile = $this->environment->get('sf_config_dir').'/properties.ini'))
    {
      $properties = parse_ini_file($propertyFile, true);
      if(isset($properties['project']['name']))
      {
        $projectName = $properties['project']['name'];
      }
      // BC compat
      elseif(isset($properties['symfony']['name']))
      {
        $projectName = $properties['symfony']['name'];
      }
      
      if(isset($properties['project']['author']))
      {
        $projectAuthor = $properties['project']['author'];
      }
      // BC compat
      elseif(isset($properties['symfony']['author']))
      {
        $projectAuthor = $properties['symfony']['author'];
      }
    }

    // fallback for projectName
    if(!$projectName)
    {
      // base on directory name
      $projectName = ucfirst(str_replace('.', '', basename($this->environment->get('sf_root_dir'))));      
    }
    
    // module credentials
    $credentials = isset($options['credentials']) ? (array)$options['credentials'] : array();
    
    $constants = array(
      'PROJECT_NAME' => $projectName,
      'APP_NAME'     => $app,
      'MODULE_NAME'  => $module,
      'AUTHOR_NAME'  => $projectAuthor,        
      'CREDENTIALS'  => 'credentials: ' . sfYamlInline::dump($credentials) 
    );

    if (is_readable($this->environment->get('sf_data_dir').'/skeleton/module'))
    {
      $skeletonDir = $this->environment->get('sf_data_dir').'/skeleton/module';
    }
    else
    {
      // FIXME: load from sf_sift_data_dir
      $skeletonDir = $this->environment->get('sf_sift_data_dir').'/skeleton/module';
    }

    // create basic application structure
    $finder = sfFinder::type('any')->discard('.*');
    $this->getFilesystem()->mirror($skeletonDir.'/module', $moduleDir, $finder);

    // create basic test
    $this->getFilesystem()->copy($skeletonDir.'/test/actionsTest.php', sfConfig::get('sf_test_dir').'/functional/'.$app.'/'.$module.'ActionsTest.php');

    // customize test file
    $this->getFilesystem()->replaceTokens($this->environment->get('sf_test_dir').'/functional/'.$app.DIRECTORY_SEPARATOR.$module.'ActionsTest.php', '##', '##', $constants);

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
    
    if(!$options['secured'])
    {
      $this->getFilesystem()->remove($moduleDir . '/config/security.yml');
      // FIXME: check if there are any files left, if yes, discard the dir!      
    }  

    $moduleYaml = array();
    
    if($options['internal'])
    {
      $moduleYaml[] = 'all:';
      $moduleYaml[] = '  is_internal: true';
    }
    
    if(count($moduleYaml))
    {
      file_put_contents($moduleDir . '/config/module.yml', join("\n", $moduleYaml));
    }
    
    $this->logSection($this->getFullName(), 'Done.');
    
  }
  
}
