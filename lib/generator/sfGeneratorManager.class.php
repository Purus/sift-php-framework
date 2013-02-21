<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @package    Sift
 * @subpackage generator
 */
class sfGeneratorManager
{
  /**
   * Save path
   *
   * @var string
   */
  protected $savePath;

  /**
   * Constructs the manager
   * 
   * @param string $savePath Save path 
   */
  public function __construct($savePath)
  {
    $this->savePath = $savePath;
  }

  /**
   * Saves content to the target file
   * 
   * @param string $path The relative path to $savePath
   * @param string $content  The content
   * @return integer Number of bytes that were written to the file
   * @throws sfFileException If something wrong happens when writing file or creating the directory
   */
  public function save($path, $content)
  {
    $path = $this->getSavePath() . DIRECTORY_SEPARATOR . $path;

    if(!is_dir(dirname($path)))
    {
      $current_umask = umask(0000);
      if(false === @mkdir(dirname($path), 0777, true))
      {
        throw new sfFileException(sprintf('Failed to make directory "%s".', dirname($path)));
      }
      umask($current_umask);
    }

    if(false === $ret = @file_put_contents($path, $content))
    {
      throw new sfFileException(sprintf('Failed to write file "%s".', $path));
    }

    return $ret;
  }

  /**
   * Generates classes and templates for a given generator class.
   *
   * @param string $generatorClass The generator class name
   * @param array  $params          An array of parameters
   * @return string The cache for the configuration file
   */
  public function generate($generatorClass, $params = array())
  {
    if(!class_exists($generatorClass))
    {
      throw new InvalidArgumentException(sprintf('Generator class "%s" does not exist.', $generatorClass));
    }

    // does it implement sfIGenerator?
    if(!in_array('sfIGenerator', class_implements($generatorClass)))
    {
      throw new InvalidArgumentException(sprintf('Generator class "%s" does not implement sfIGenerator interface.', $generatorClass));
    }
    
    $generator = new $generatorClass($this);
    
    return $generator->generate($params);
  }

  /**
   * Gets the base path to use when generating files.
   *
   * @return string The base path
   */
  public function getSavePath()
  {
    return $this->savePath;
  }

  /**
   * Sets the base path to use when generating files.
   *
   * @param string $savePath The save path
   */
  public function setBasePath($savePath)
  {
    $this->savePath = $savePath;
  }

}
