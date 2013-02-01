<?php
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginInstaller allows you to perform custom installs.
 *
 * @package    Sift
 * @subpackage plugin
 */
abstract class sfPluginInstaller extends sfConfigurable {

  /**
   * Project instance
   * @var sfProject 
   */
  protected $project;

  /**
   * Constructs the installer
   *
   * @param sfDispatcher $dispatcher
   * @param sfILogger $logger
   * @param type $options
   */
  public function __construct(sfProject $project, $options = array())
  {
    parent::__construct($options);

    $this->project = $project;

    $this->configure();
  }

  public function configure()
  {
  }

  /**
   * Install the plugin
   * 
   */
  public function install()
  {
    $this->preInstall();
    $this->doInstall();
    $this->postInstall();
  }
  
  public function Uninstall()
  {
    $this->preInstall();
    $this->doUninstall();
    $this->postInstall();    
  }

  /**
   * Should be implemented in subclasses
   */
  abstract function doInstall();

  abstract function doUninstall();
  
  public function preInstall()
  {
  }

  public function postInstall()
  {
  }

  public function preUninstall()
  {
  }

  public function postUninstall()
  {
  }
  
}
