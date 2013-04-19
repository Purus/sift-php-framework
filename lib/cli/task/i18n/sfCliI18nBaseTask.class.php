<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base task for i18n tasks
 *
 * @package    Sift
 * @subpackage cli_task
 */
abstract class sfCliI18nBaseTask extends sfCliBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('app', sfCliCommandArgument::REQUIRED, 'The application or plugin name'),
      new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
    ));
  }

  /**
   * Returns an array with following structure:
   *
   * * application name (or plugin name)
   * * directory (directory to the application or plugin)
   * * isPlugin? (true is the application is a plugin)
   *
   * @param type $application
   * @return array ($applicationName, $plugin, $dir)
   */
  protected function getApplicationOrPlugin($application)
  {
    $isPlugin = false;

    // this is a plugin
    if(preg_match('|Plugin$|', $application))
    {
      $this->checkPluginExists($application);
      $isPlugin = true;
      $dir = $this->environment->get('sf_plugins_dir') . '/' . $application;
    }
    else
    {
      $this->checkAppExists($application);
      $dir = $this->environment->get('sf_apps_dir') . '/' . $application;
    }

    return array($application, $dir, $isPlugin);
  }

}