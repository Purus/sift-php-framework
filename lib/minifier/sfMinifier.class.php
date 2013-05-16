<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMinifier class minifies web assets like javascript, CSS and PNG images.
 *
 * @package    Sift
 * @subpackage minifier
 */
abstract class sfMinifier extends sfConfigurable implements sfIMinifier {

  protected
    $optimizedContent = null,
    $optimizedSize = null,
    $originalSize = null,
    $processed = false;

  /**
   * Returns an instance of the minifier driver
   *
   * @param string $driver Driver name
   * @param array $options Array of options for the driver
   * @return sfIMinifier
   */
  public static function factory($driver, $options = array())
  {
    $driverClass = sprintf(sprintf('sfMinifierDriver%s', ucfirst($driver)));

    if(class_exists($driverClass))
    {
      $driverObj = new $driverClass($options);
    }
    elseif(class_exists($driver))
    {
      $driverObj = new $driver($options);
    }
    else
    {
      throw new InvalidArgumentException(sprintf('Driver "%s" does not exists.', $driver));
    }

    if(!$driverObj instanceof sfIMinifier)
    {
      throw new LogicException(sprintf('Driver "%s" does not implement sfIMinifier interface.', $driver));
    }

    return $driverObj;
  }

  /**
   * Returns optimization ratio
   *
   * @return float
   */
  public function getOptimizationRatio()
  {
    return 0 !== $this->originalSize ? round($this->optimizedSize * 100 / $this->originalSize, 2) : null;
  }

  /**
   * Returns the results
   *
   * @return array
   * @throws LogicException
   */
  public function getResults()
  {
    if(!$this->processed)
    {
      throw new LogicException('Optimization has not been processed');
    }

    return array(
      'optimizedContent' => $this->optimizedContent,
      'originalSize' => $this->originalSize,
      'optimizedSize' => $this->optimizedSize,
      'ratio' => $this->getOptimizationRatio(),
    );
  }

  /**
   * Process file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the file?
   */
  abstract public function doProcessFile($file, $replace = false);

  /**
   * Processes the file
   *
   * @param string $file Absolute path to a file
   * @param boolean $replace Replace the contents?
   * @return string The result string
   * @throws sfFileException If file does not exist or is not readable
   */
  public function processFile($file, $replace = false)
  {
    if(!is_readable($file))
    {
      throw new sfFileException(sprintf('File "%s" does not exist or is not readable.', $file));
    }

    $this->originalSize = filesize($file);
    $result = $this->doProcessFile($file, $replace);

    if($replace)
    {
      clearstatcache();
      $this->optimizedSize = filesize($result);
    }
    else
    {
      $this->optimizedSize = strlen($result);
      $this->optimizedContent = $result;
    }

    $this->processed = true;

    return $result;
  }

  /**
   *
   * @param string $file Path to a file
   * @param string $content Contents
   * @return string Path to a file
   * @throws RuntimeException
   */
  protected function replaceFile($file, $content)
  {
    if(!file_put_contents($file, $content))
    {
      throw new RuntimeException(sprintf('Unable to replace file "%s" with optimized contents', $file));
    }
    return $file;
  }

  /**
   * Resets driver instance
   *
   * @return sfMinifier
   */
  public function reset()
  {
    $this->optimizedContent = null;
    $this->optimizedSize = null;
    $this->originalSize = null;
    $this->processed = false;
    return $this;
  }

}
