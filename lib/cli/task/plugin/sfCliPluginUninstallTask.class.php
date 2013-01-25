<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uninstall a plugin.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliPluginUninstallTask extends sfCliPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('channel', 'c', sfCliCommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', null),
      new sfCliCommandOption('install_deps', 'd', sfCliCommandOption::PARAMETER_NONE, 'Whether to force installation of dependencies', null),
    ));

    $this->namespace = 'plugin';
    $this->name = 'uninstall';

    $scriptName = $this->environment->get('script_name');
    
    $this->briefDescription = 'Uninstalls a plugin';

    $this->detailedDescription = <<<EOF
The [plugin:uninstall|INFO] task uninstalls a plugin:

  [{$scriptName} plugin:uninstall sfGuardPlugin|INFO]

The default channel is [symfony|INFO].

You can also uninstall a plugin which has a different channel:

  [{$scriptName} plugin:uninstall --channel=mypearchannel sfGuardPlugin|INFO]

  [{$scriptName} plugin:uninstall -c mypearchannel sfGuardPlugin|INFO]

Or you can use the [channel/package|INFO] notation:

  [{$scriptName} plugin:uninstall mypearchannel/sfGuardPlugin|INFO]

You can get the PEAR channel name of a plugin by launching the
[plugin:list] task.

If the plugin contains some web content (images, stylesheets or javascripts),
the task also removes the [web/%name%|COMMENT] symbolic link (on *nix)
or directory (on Windows).
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('plugin', sprintf('Uninstalling plugin "%s"', $arguments['name']));

    $this->getPluginManager()->uninstallPlugin($arguments['name'], $options['channel']);
  }
}
