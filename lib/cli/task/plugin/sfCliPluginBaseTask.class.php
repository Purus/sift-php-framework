<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all plugin tasks.
 *
 * @package    Sift
 * @subpackage cli_task
 */
abstract class sfCliPluginBaseTask extends sfCliBaseTask
{
  protected $pluginManager = null;

  /**
   * Returns a plugin manager instance.
   *
   * @return sfPluginManager A sfSymfonyPluginManager instance
   */
  protected function getPluginManager()
  {
    if (null === $this->pluginManager) {
      $environment = new sfPearEnvironment($this->dispatcher, array(
        'plugin_dir' => $this->environment->get('sf_plugins_dir'),
        'cache_dir'  => $this->environment->get('sf_root_cache_dir'). '/.pear',
        'web_dir'    => $this->environment->get('sf_web_dir'),
        'config_dir' => $this->environment->get('sf_config_dir'),
      ));

      $this->pluginManager = new sfPluginManager($this->dispatcher, $environment, $this->logger, array(
          'web_dir' => $this->environment->get('sf_web_dir'),
          'sift_version' => $this->environment->get('sf_sift_version'),
          'sift_pear_channel' => $this->environment->get('sift_pear_channel', 'pear.lab')
      ));

    }

    return $this->pluginManager;
  }

}
