<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new plugin.
 * 
 * @package     Sift
 * @subpackage  cli_task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 */
class sfCliGeneratePluginTask extends sfCliGeneratorBaseTask {

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('plugin', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('module', null, sfCliCommandOption::PARAMETER_REQUIRED | sfCliCommandOption::IS_ARRAY, 'Add a module'),
        new sfCliCommandOption('test-application', null, sfCliCommandOption::PARAMETER_REQUIRED, 'A name for the initial test application', 'frontend'),
        new sfCliCommandOption('skip-test-dir', null, sfCliCommandOption::PARAMETER_NONE, 'Skip generation of the plugin test directory'),
    ));

    $this->namespace = 'generate';
    $this->name = 'plugin';

    $this->briefDescription = 'Generates a new plugin';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [generate:plugin|INFO] task creates the basic directory structure for a
new plugin in the current project:

  [{$scriptName} generate:plugin sfExamplePlugin|INFO]

You can customize the default skeleton used by the task by creating a
[%SF_DATA_DIR%/skeleton/plugin|COMMENT] directory.

You can also specify one or more modules you would like included in this
plugin using the [--module|COMMENT] option:

  [{$scriptName} generate:plugin sfExamplePlugin --module=sfExampleFoo --module=sfExampleBar|INFO]

This task automatically generates all the necessary files for writing unit and
functional tests for your plugin, including an embedded project and
application in [/test/fixtures/project|COMMENT]. You can customized the name
used with the [--test-application|COMMENT] option:

  [{$scriptName} generate:plugin sfExamplePlugin --test-application=backend|INFO]

Use the [--skip-test-dir|COMMENT] to skip generation of the plugin [/test|COMMENT]
directory entirely:

  [{$scriptName} generate:plugin sfExamplePlugin --skip-test-dir|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin = $arguments['plugin'];
    $modules = $options['module'];

    // validate the plugin name
    if('Plugin' != substr($plugin, -6) || !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $plugin))
    {
      throw new sfCliCommandException(sprintf('The plugin name "%s" is invalid.', $plugin));
    }

    // validate the test application name
    if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $options['test-application']))
    {
      throw new sfCliCommandException(sprintf('The application name "%s" is invalid.', $options['test-application']));
    }
    
    // plugin does not exist
    if($this->checkPluginExists($plugin, false))
    {
      throw new sfException(sprintf('Plugin "%s" already exists', $plugin));
    }

    echo "check prosel";
    
    if(is_readable($this->environment->get('sf_data_dir') . '/skeleton/plugin'))
    {
      $skeletonDir = $this->environment->get('sf_data_dir') . '/skeleton/plugin';
    }
    else
    {
      $skeletonDir = $this->environment->get('sf_sift_data_dir') . '/skeleton/plugin';
    }

    $pluginDir = $this->environment->get('sf_plugins_dir') . '/' . $plugin;
    $testProject = $pluginDir . '/test/fixtures/project';
    $testApp = $testProject . '/apps/' . $options['test-application'];
    
    $constants = array(
      'PLUGIN_NAME' => $plugin,
      'AUTHOR_NAME' => $this->getProjectProperty('author', 'Your name here'),
      'APP_NAME' => $options['test-application'],
    );

    // plugin
    $finder = sfFinder::type('any')->discard('.sf');
    
    $this->getFilesystem()->mirror($skeletonDir, $pluginDir, $finder);

    // PluginConfiguration
    $this->getFilesystem()->rename($pluginDir . '/lib/Plugin.class.php', 
                                   $pluginDir . '/lib/' . $plugin . '.class.php');

    // tokens
    $finder = sfFinder::type('file')->name('*.php', '*.yml', 'package.xml.tmpl');
    $this->getFilesystem()->replaceTokens($finder->in($pluginDir), '##', '##', $constants);

    if($options['skip-test-dir'])
    {
      sfToolkit::clearDirectory($pluginDir . '/test');
      $this->getFilesystem()->remove($pluginDir . '/test');
    }
    else
    {
      // test project and app
      $finder = sfFinder::type('any')->discard('.sf');
      $this->getFilesystem()->mirror($this->environment->get('sf_sift_lib_dir') . '/task/generator/skeleton/project', $testProject, $finder);
      $this->getFilesystem()->mirror($this->environment->get('sf_sift_lib_dir') . '/task/generator/skeleton/app/app', $testApp, $finder);

      // ProjectConfiguration
      // $this->getFilesystem()->copy($skeletonDir . '/project/ProjectConfiguration.class.php', $testProject . '/config/ProjectConfiguration.class.php', array('override' => true));
      // $this->getFileSystem()->replaceTokens($testProject . '/config/ProjectConfiguration.class.php', '##', '##', $constants);

      // ApplicationConfiguration
      // $this->getFilesystem()->rename($testApp . '/config/ApplicationConfiguration.class.php', $testApp . '/config/' . $options['test-application'] . 'Configuration.class.php');
      // $this->getFilesystem()->replaceTokens($testApp . '/config/' . $options['test-application'] . 'Configuration.class.php', '##', '##', $constants);

      // settings.yml
      // $this->getFilesystem()->replaceTokens($testApp . '/config/settings.yml', '##', '##', array('NO_SCRIPT_NAME' => 'false', 'CSRF_SECRET' => $plugin, 'ESCAPING_STRATEGY' => 'on'));
    }

    // modules
    foreach($modules as $module)
    {
      $moduleTask = new sfCliGeneratePluginModuleTask($this->environment, 
                      $this->dispatcher, $this->formatter, $this->logger);
      $moduleTask->setCommandApplication($this->commandApplication);
      $moduleTask->run(array($plugin, $module));
    }
    
  }

}
