<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Installs a plugin.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliPluginUpgradeTask extends sfCliPluginInstallTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('stability', 's', sfCliCommandOption::PARAMETER_REQUIRED, 'The preferred stability (stable, beta, alpha)', null),
        new sfCliCommandOption('release', 'r', sfCliCommandOption::PARAMETER_REQUIRED, 'The preferred version', null),
        new sfCliCommandOption('channel', 'c', sfCliCommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null),
    ));

    $this->aliases = array('plugin-upgrade');
    $this->namespace = 'plugin';
    $this->name = 'upgrade';

    $this->briefDescription = 'Upgrades a plugin';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:install|INFO] task upgrades a plugin:

  [{$scriptName} plugin:upgrade myExamplePlugin|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), sprintf('Upgrading plugin "%s"', $arguments['name']));

    $manager = $this->getPluginManager();

    if(!$manager->isPluginInstalled($arguments['name']))
    {
      throw new sfException(sprintf('Plugin is not installed. Try plugin:install %s', $arguments['name']));
    }

    $options['version'] = $options['release'];
    unset($options['release']);

    if($manager->upgradePlugin($arguments['name'], $options))
    {
      $this->logSection($this->getFullName(), 'Done.');
    }
    else
    {
      throw new sfException('Error upgrading plugin.');
    }

  }

}
