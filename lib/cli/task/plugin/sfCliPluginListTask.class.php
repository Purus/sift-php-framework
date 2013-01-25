<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Lists installed plugins.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliPluginListTask extends sfCliPluginBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'plugin';
    $this->name = 'list';

    $this->briefDescription = 'Lists installed plugins';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [plugin:list|INFO] task lists all installed plugins:

  [{$scriptName} plugin:list|INFO]

It also gives the channel and version for each plugin.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $plugins = $this->getPluginManager()->getInstalledPlugins();
    
    if(count($plugins))
    {
      $this->logSection($this->getFullName(), sprintf('%s plugins installed.', count($plugins)));
      
      $this->log($this->formatter->format('Installed plugins:', 'COMMENT'));      
      
      foreach($plugins as $package)
      {
        $alias = $this->getPluginManager()->getEnvironment()
                  ->getRegistry()->getChannel($package->getChannel())->getAlias();

        $this->log(sprintf(' %-40s %10s-%-6s %s', 
                $this->formatter->format($package->getPackage(), 'INFO'), 
                $package->getVersion(), 
                $package->getState() ? $package->getState() : null, 
                $this->formatter->format(sprintf('# %s (%s)', $package->getChannel(), $alias), 'COMMENT')));
      }
    }
    else
    {
      $this->logSection($this->getFullName(), 'No plugins installed.');
    }
    
  }
  
}
