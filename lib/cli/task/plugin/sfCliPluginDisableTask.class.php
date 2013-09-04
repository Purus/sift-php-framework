<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Disables a plugin.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliPluginDisableTask extends sfCliPluginBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('environment', 'e', sfCliCommandOption::PARAMETER_REQUIRED, 'The environment', 'all'),
        new sfCliCommandOption('channel', 'c', sfCliCommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null),
    ));

    $this->namespace = 'plugin';
    $this->name = 'disable';

    $this->briefDescription = 'Disables a plugin';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:disable|INFO] task disables a plugin:

  [{$scriptName} plugin:disable myGuardPlugin|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $pluginName = $arguments['name'];

    if(!$this->checkPluginExists($pluginName, false))
    {
      throw new sfCliCommandException('Unable to disable the plugin. Plugin is not installed.');
    }

    $this->logSection($this->getFullName(), sprintf('Disabling plugin "%s"', $arguments['name']));

    $pluginYaml = $this->environment->get('sf_config_dir') . '/plugins.yml';

    if(!is_readable($pluginYaml))
    {
      throw new LogicException(sprintf('Project plugin configuration file "%s" is missing.', $pluginYaml));
    }

    $environment = $options['environment'];

    $parser = new sfYamlParser();
    $config = $parser->parse(file_get_contents($pluginYaml));

    // the plugin entry does not exist
    if(!isset($config[$environment])
        || !array_key_exists($pluginName, $config[$environment]))
    {
      $config[$environment][$pluginName] = array(
        'disabled' => true
      );
    }
    else
    {
      if(isset($config[$environment][$pluginName]['disabled'])
          && !$config[$environment][$pluginName]['disabled'])
      {
        $config[$environment][$pluginName] = array_merge((array)$config[$environment][$pluginName], array(
          'disabled' => true
        ));
      }
      else
      {
        throw new InvalidArgumentException('Plugin is already disabled');
      }
    }

    $dumper = new sfYamlDumper();
    $yaml = $dumper->dump($config, 4);

    // make some cleanup
    $yaml = preg_replace('/ null/', ' ~', $yaml);

    // take the skeleton
    $skeleton = file_get_contents($this->environment->get('sf_sift_data_dir') . '/skeleton/project/config/plugins.yml');

    $yaml = $skeleton . "\n" . $yaml;

    file_put_contents($pluginYaml, $yaml);

    $this->logSection($this->getFullName(), 'Done.');
  }

}
