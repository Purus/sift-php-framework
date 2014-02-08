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
class sfCliPluginRemoveChannelTask extends sfCliPluginBaseTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The channel name'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'remove-channel';

    $this->briefDescription = 'Removes PEAR channel';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:remove-channel|INFO] task remove existing PEAR channel:

  [{$scriptName} plugin:remove-channel pear.example.com|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), sprintf('Removing channel "%s"', $arguments['name']));

    $this->getPluginManager()->getEnvironment()->removeChannel($arguments['name']);

    $this->logSection($this->getFullName(), 'Done.');
  }

}
