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

  /**
   * Project instance
   *
   * @var sfProject
   */
  protected $project;

  /**
   * Plugin name
   *
   * @var string
   */
  protected $name;

  /**
   * Plugin's root directory
   *
   * @var string
   */
  protected $rootDir;

  /**
   * Constructs the plugin
   *
   * @param array $options
   */
  public function __construct(sfProject $project, $name, $rootDir, $options = array())
  {
    $this->project = $project;
    $this->name = $name;
    $this->rootDir = $rootDir;

    parent::__construct($options);

    $this->configure();
  }

  public function setup()
  {
  }

  public function configure()
  {
  }

  public function initialize()
  {
  }

  public function shutdown()
  {
  }

  /**
   * Initializes manual autoloading for the plugin.
   *
   * This method is called when a plugin is initialized in a project.
   * Otherwise, autoload is handled in {@link sfApplication}
   * using {@link sfAutoload}.
   *
   * @param sfClassLoader $classLoader Class loader
   * @see sfSimpleAutoload
   */
  public function initializeAutoload(sfClassLoader $classLoader)
  {
  }

  /**
   * Returns plugin name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns plugin root directory
   *
   * @return string
   */
  public function getRootDir()
  {
    return $this->rootDir;
  }

  /**
   * Magic to string method
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Returns parent object
   *
   * @return sfProject|sfApplication
   */
  public function getParent()
  {
    return $this->project;
  }

  /**
   * Returns plugin version
   *
   * @return string
   */
  public function getVersion()
  {
    if(is_readable(($version = $this->getRootDir() . '/VERSION')))
    {
      return file_get_contents($version);
    }
    return 'UNKNOWN';
  }

}
