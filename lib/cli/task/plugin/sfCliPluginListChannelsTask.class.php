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
class sfCliPluginListChannelsTask extends sfCliPluginBaseTask {

  /**
   * Array of hidden channels
   * 
   * @var array 
   */
  protected $hiddenChannels = array(
    'doc.php.net',
    'pear.php.net',
    '__uri',
    'pecl.php.net'      
  );
  
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->namespace = 'plugin';
    $this->name = 'list-channels';

    $this->briefDescription = 'List all PEAR channels';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:list-channels|INFO] lists PEAR channels:

  [{$scriptName} plugin:list-channels|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $channels = $this->getPluginManager()->getEnvironment()->getRegistry()->listChannels();    
    $channels = array_diff($channels, $this->hiddenChannels);

    if(count($channels))
    {
      $this->logSection($this->getFullName(), sprintf('%s registered channel(s)', count($channels)));
      $this->log('');
      foreach($channels as $channel)
      {
        $info = $this->getPluginManager()->getEnvironment()->getRegistry()->channelInfo($channel);
        
        $this->log(sprintf('  %s (%s) %s', $channel, $info['suggestedalias'], $info['summary']));
      }     
    }
    else
    {
      $this->logSection($this->getFullName(), 'There are no registered channel(s)');
    }
    
  }

}
