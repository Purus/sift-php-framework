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
class sfCliPluginInstallTask extends sfCliPluginBaseTask {

  /**
   * @see sfTask
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
        new sfCliCommandOption('install-deps', 'd', sfCliCommandOption::PARAMETER_NONE, 'Whether to force installation of required dependencies', null),
        new sfCliCommandOption('force-license', null, sfCliCommandOption::PARAMETER_NONE, 'Whether to force installation even if the license is not MIT like'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'install';

    $this->briefDescription = 'Installs a plugin';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [plugin:install|INFO] task installs a plugin:

  [{$scriptName} plugin:install myGuardPlugin|INFO]

By default, it installs the latest [stable|COMMENT] release.

If you want to install a plugin that is not stable yet,
use the [stability|COMMENT] option:

  [{$scriptName} plugin:install --stability=beta myGuardPlugin|INFO]
  [{$scriptName} plugin:install -s beta myGuardPlugin|INFO]

You can also force the installation of a specific version:

  [{$scriptName} plugin:install --release=1.0.0 myGuardPlugin|INFO]
  [{$scriptName} plugin:install -r 1.0.0 myGuardPlugin|INFO]

To force installation of all required dependencies, use the [install_deps|INFO] flag:

  [{$scriptName} plugin:install --install-deps myGuardPlugin|INFO]
  [{$scriptName} plugin:install -d myGuardPlugin|INFO]

By default, the PEAR channel used is [symfony-plugins|INFO]
(plugins.symfony-project.org).

You can specify another channel with the [channel|COMMENT] option:

  [{$scriptName} plugin:install --channel=mypearchannel myGuardPlugin|INFO]
  [{$scriptName} plugin:install -c mypearchannel myGuardPlugin|INFO]

You can also install PEAR packages hosted on a website:

  [{$scriptName} plugin:install http://www.example.com/SomePackage-1.0.0.tgz|INFO]

Or local PEAR packages:

  [{$scriptName} plugin:install /home/myname/plugins/myGuardPlugin-1.0.0.tgz|INFO]

If the plugin contains some web content (images, stylesheets or javascripts),
the task creates a [%name%|COMMENT] symbolic link for those assets under [web/|COMMENT].
On Windows, the task copy all the files to the [web/%name%|COMMENT] directory.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), sprintf('Installing plugin "%s"', $arguments['name']));

    $options['version'] = $options['release'];
    unset($options['release']);

    // license compatible?
    if(!$options['force-license'])
    {
      try
      {
        $license = $this->getPluginManager()->getPluginLicense($arguments['name'], $options);
      }
      catch(Exception $e)
      {
        throw new sfCliCommandException(sprintf('%s (use --force-license to force installation)', $e->getMessage()));
      }

      if(false !== $license)
      {
        $temp = trim(str_replace('license', '', strtolower($license)));
        if(null !== $license && !in_array($temp, array('mit', 'bsd', 'lgpl', 'php', 'apache')))
        {
          throw new sfCliCommandException(sprintf('The license of this plugin "%s" is not MIT like (use --force-license to force installation).', $license));
        }
      }
    }
    
    $this->getPluginManager()->installPlugin($arguments['name'], $options);
    
    $this->logSection($this->getFullName(), 'Done.');
  }

}
