<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Freezes the project do current Sift release
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliProjectUnfreezeTask extends sfCliProjectFreezeTask
{
  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->namespace = 'project';
    $this->name = 'unfreeze';
    $this->briefDescription = 'Unfreeze the project';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [project:unfreeze|INFO] task unfreezes already freezed project:

  [{$scriptName} project:unfreeze|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $lib_dir = $this->environment->get('sf_lib_dir');
    $data_dir = $this->environment->get('sf_data_dir');
    $web_dir = $this->environment->get('sf_web_dir');
    
    $sift_lib_dir = $this->environment->get('sf_sift_lib_dir');
    $sift_data_dir = $this->environment->get('sf_sift_data_dir');
    
    $this->logSection($this->getFullName(), sprintf('Unfreezing project from %s %s',             
            $this->commandApplication->getName(), $this->commandApplication->getVersion()));

    if(!is_dir($lib_dir.'/sift'))
    {
      throw new sfException('You can unfreeze only if you froze to Sift release before.');
    }

    $dirs = explode('#', file_get_contents($this->environment->get('sf_config_dir').'/config.php.bak'));    
    $this->changeFrameworkDirectories('\''.$dirs[0].'\'', '\''.$dirs[1].'\'');
  
    $finder = sfFinder::type('any');
    
    $this->getFilesystem()->remove($finder->in($lib_dir.'/sift'));
    $this->getFilesystem()->remove($lib_dir.'/sift');
    
    $this->getFilesystem()->remove($finder->in($data_dir.'/sift'));
    $this->getFilesystem()->remove($data_dir.'/sift');
    
    $this->getFilesystem()->remove($finder->in($web_dir.'/sf'));
    $this->getFilesystem()->remove($web_dir.'/sf');
    
    $this->getFilesystem()->remove($this->environment->get('sf_config_dir').'/config.php.bak');

    // clear all cache
    $this->clearAllCache();
    
    $this->logSection($this->getFullName(), 'Done.');
  }
  
}
