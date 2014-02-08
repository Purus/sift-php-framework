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
class sfCliPluginRunInstallerTask extends sfCliPluginBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('install', 'i', sfCliCommandOption::PARAMETER_NONE, 'Install direction', null),
        new sfCliCommandOption('uninstall', 'u', sfCliCommandOption::PARAMETER_NONE, 'The preferred version', null),
        new sfCliCommandOption('connection', 'c', sfCliCommandOption::PARAMETER_OPTIONAL, 'Database connection', 'default'),
        new sfCliCommandOption('previous-release', 'p', sfCliCommandOption::PARAMETER_OPTIONAL, 'Previous installed release. Used for migrations.'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'run-installer';

    $this->briefDescription = 'Runs plugin installer';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:run-installer|INFO] task runs plugin installer for a plugin:

  [{$scriptName} plugin:run-installer myGuardPlugin --install|INFO]

  [{$scriptName} plugin:run-installer myGuardPlugin --uninstall|INFO]

EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugin = $arguments['name'];
    $this->checkPluginExists($plugin);

    $install = true;
    if (!$options['install'] && !$options['uninstall']) {
      $install = true;
    } elseif ($options['uninstall']) {
      $install = false;
    }

    unset($options['install']);
    unset($options['uninstall']);

    // force
    $this->reloadAutoload();

    $installer = $this->getInstaller($plugin, $options);

    if ($install) {
      $this->logSection($this->getFullName(), sprintf('Installing plugin "%s"', $arguments['name']));

      $result = $installer->install();
    } else {
      $this->logSection($this->getFullName(), sprintf('Uninstalling plugin "%s"', $arguments['name']));

      $result = $installer->uninstall();
    }

    // after uninstalling, cleanup autoloading cache
    $this->reloadAutoload();

    $this->logSection($this->getFullName(), 'Done.');
  }

  /**
   * Returns an instance of plugin installer
   *
   * @param string $plugin
   * @param array $options Array of options for the installer
   * @return sfPluginInstaller
   * @throws sfException
   * @throws LogicException
   */
  protected function getInstaller($plugin, $options = array())
  {
    $installerClass = sprintf('%sInstaller', $plugin);

    $installer = $this->environment->get('sf_plugins_dir') . '/' .
            $plugin . '/' .
            $this->environment->get('sf_lib_dir_name') . '/'
            . 'install' . '/' . $installerClass . '.class.php';

    // options for the installer
    $options['plugin_dir'] = $this->environment->get('sf_plugins_dir') . '/' . $plugin;
    $options['plugin'] = $plugin;

    // pass to installer, which support options with underscores
    // FIXME: maybe convert all simply by replacing - with _
    if ($options['previous-release']) {
      $options['previous_release'] = $options['previous-release'];
      unset($options['previous-release']);
    }

    if (is_readable($installer)) {
      require_once $installer;

      if (!class_exists($installerClass, false)) {
        throw new sfException(sprintf('Installer file does not contain plugin installer class "%s"', $installerClass));
      }

      $installer = new $installerClass($this, $options);

      if (!$installer instanceof sfIPluginInstaller) {
        throw new LogicException(sprintf('Plugin installer class "%s" is invalid. It should implement sfIPluginInstaller interface.', get_class($installer)));
      }
    } else {
      $installer = new sfPluginInstaller($this, $options);
    }

    return $installer;
  }

}
