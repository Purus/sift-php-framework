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
class sfCliPluginAddChannelTask extends sfCliPluginBaseTask {

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('name', sfCliCommandArgument::REQUIRED, 'The channel name'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'add-channel';

    $this->briefDescription = 'Add a new PEAR channel';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:add-channel|INFO] task adds a new PEAR channel:

  [{$scriptName} plugin:add-channel pear.example.com|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection($this->getFullName(), sprintf('Adding channel "%s"', $arguments['name']));

    $this->getPluginManager()->getEnvironment()->addChannel($arguments['name']);
    
    $this->logSection($this->getFullName(), 'Done.');    
  }

}
