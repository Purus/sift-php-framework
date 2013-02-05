<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPlugin represents a plugin
 *
 * @package    Sift
 * @subpackage project
 */
abstract class sfPlugin extends sfConfigurable {

  protected
    $configuration = null,
    $dispatcher    = null,
    $name          = null;

  /**
   * 
   * @param array $options
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
    
    $this->setup();
    
    if(PHP_SAPI == 'cli')
    {
      $this->setupCli();
    }
        
    $this->configure();
    
    $this->initializeAutoload();
    $this->initialize();
  }

  public function setup()
  {
  }

  public function setupCli()
  {    
  }

  public function configure()
  {    
  }
  
  public function initialize()
  {    
  }  
  
  /**
   * Initializes manual autoloading for the plugin.
   * 
   * This method is called when a plugin is initialized in a project.
   * Otherwise, autoload is handled in {@link sfApplication} 
   * using {@link sfAutoload}.
   * 
   * @see sfSimpleAutoload
   */
  public function initializeAutoload()
  {
  }
  
  
  public function getRootDir()
  {
    return $this->getOption('root_dir');
  }

}
