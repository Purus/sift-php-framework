<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Wraps the generate module task to create a plugin module
 *
 * @package     Sift
 * @subpackage  cli_task
 */
class sfCliGeneratePluginModuleTask extends sfCliGeneratorBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('plugin', sfCliCommandArgument::REQUIRED, 'The plugin name'),
        new sfCliCommandArgument('module', sfCliCommandArgument::REQUIRED, 'The module name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('secured', null, sfCliCommandOption::PARAMETER_NONE, 'Secure the module?', null),
      new sfCliCommandOption('internal', null, sfCliCommandOption::PARAMETER_NONE, 'IS the module internal only? (Not accessible via web)', null),
      new sfCliCommandOption('credentials', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'User credentials for accessing the module', ''),
    ));

    $this->namespace = 'generate';
    $this->name = 'plugin-module';
    $this->briefDescription = 'Generates a new module in a plugin';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [generate:plugin-module|INFO] task creates the basic directory structure
for a new module in an existing plugin:

  [{$scriptName} generate:plugin-module sfExamplePlugin article|INFO]

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/plugin_module|COMMENT] directory.

The task also creates a functional test stub in your plugin's
[/test/functional|COMMENT] directory.

If a module with the same name already exists in the plugin, a
[sfCliCommandException|COMMENT] is thrown.
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin = $arguments['plugin'];
    $module = $arguments['module'];

    $this->checkPluginExists($plugin);

    // validate the module name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $module)) {
      throw new sfCliCommandException(sprintf('The module name "%s" is invalid.', $module));
    }

    $pluginDir = $this->environment->get('sf_plugins_dir') . '/' . $plugin;
    $moduleDir = $pluginDir . '/modules/' . $module;
    $testDir = $pluginDir . '/test';

    if (is_dir($moduleDir)) {
      throw new sfCliCommandException(sprintf('The module "%s" already exists in the "%s" plugin.', $moduleDir, $plugin));
    }

    if (is_readable($this->environment->get('sf_data_dir') . '/skeleton/plugin_module')) {
      $skeletonDir = $this->environment->get('sf_data_dir') . '/skeleton/plugin_module';
    } else {
      $skeletonDir = $this->environment->get('sf_sift_data_dir') . '/skeleton/plugin_module';
    }

    // module credentials
    $credentials = isset($options['credentials']) ? (array) $options['credentials'] : array();

    $constants = array(
      'PLUGIN_NAME' => $plugin,
      'MODULE_NAME' => $module,
      'AUTHOR_NAME' => $this->getProjectProperty('author', 'Your name here'),
      'CREDENTIALS'  => 'credentials: ' . sfYamlInline::dump($credentials)
    );

    // create basic module structure
    $finder = sfFinder::type('any')->discard('.sf');
    $this->getFilesystem()->mirror($skeletonDir . '/module', $moduleDir, $finder);

    // rename base actions class
    $this->getFilesystem()->rename($moduleDir . '/lib/BaseActions.class.php', $moduleDir . '/lib/Base' . $module . 'Actions.class.php');

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);

    if (!$options['secured']) {
      $this->getFilesystem()->remove($moduleDir . '/config/security.yml');
      // FIXME: check if there are any files left, if yes, discard the dir!
    }

    $moduleYaml = array();

    if ($options['internal']) {
      $moduleYaml[] = 'all:';
      $moduleYaml[] = '  is_internal: true';
    }

    if (count($moduleYaml)) {
      file_put_contents($moduleDir . '/config/module.yml', join("\n", $moduleYaml));
    }

    if (file_exists($testDir . '/fixtures/project/app')) {
      // create functional test
      $this->getFilesystem()->copy($skeletonDir . '/test/actionsTest.php', $testDir . '/functional/' . $module . 'ActionsTest.php');
      $this->getFilesystem()->replaceTokens($testDir . '/functional/' . $module . 'ActionsTest.php', '##', '##', $constants);

      // enable module in test project
      $file = $pluginDir . '/test/fixtures/project/config/settings.yml';
      $config = file_exists($file) ? sfYaml::load($file) : array();

      if (!isset($config['all'])) {
        $config['all'] = array();
      }
      if (!isset($config['all']['enabled_modules'])) {
        $config['all']['enabled_modules'] = array();
      }
      $config['all']['enabled_modules'][] = $module;

      $this->getFilesystem()->touch($file);
      file_put_contents($file, sfYaml::dump($config, 2));
    }
  }

}
