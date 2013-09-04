<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Enables a plugin.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliPluginEnableTask extends sfCliPluginBaseTask {

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
    $this->name = 'enable';

    $this->briefDescription = 'Enables a plugin';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:enable|INFO] task enables a plugin:

  [{$scriptName} plugin:enable myGuardPlugin|INFO]
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
      throw new sfCliCommandException('Unable to enable the plugin. Plugin is not installed.');
    }

    $this->logSection($this->getFullName(), sprintf('Enabling plugin "%s"', $arguments['name']));

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
      $config[$environment][$pluginName] = null;
    }
    else
    {
      if(is_array($config[$environment][$pluginName]) &&
          isset($config[$environment][$pluginName]['disabled'])
          && $config[$environment][$pluginName]['disabled'])
      {
        $config[$environment][$pluginName] = array_merge((array)$config[$environment][$pluginName], array(
          'disabled' => false
        ));
      }
      else
      {
        throw new InvalidArgumentException('Plugin is already enabled');
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
