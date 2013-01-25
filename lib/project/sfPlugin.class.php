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
   * Constructor.
   * 
   * @param sfProjectConfiguration $configuration The project configuration
   * @param string                 $rootDir       The plugin root directory
   * @param string                 $name          The plugin name
   */
  public function __construct($options = array())
  {
    parent::__construct($options);
    
    $this->setup();
    $this->configure($options);
  }

  public function setup()
  {    
  }
  
  public function configure($options = array())
  {    
  }
  
  public function getRootDir()
  {
    return $this->getOption('root_dir');
  }

}
