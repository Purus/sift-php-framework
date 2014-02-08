<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Google closure compiler for javascripts.
 *
 * @package    Sift
 * @subpackage minifier
 */
class sfMinifierDriverGoogleClosure extends sfMinifier {

  /**
   * Array of default options
   */
  protected $defaultOptions = array(
    'compiler_path' => '/usr/local/bin/compiler.jar'
  );

  /**
   * Setups the minifier object
   *
   */
  public function setup()
  {
    if(!sfToolkit::isCallable('shell_exec'))
    {
      throw new sfConfigurationException('Shell scripts cannot be executed. Enabled function "shell_exec" in your php.ini or use another driver.');
    }

    $compilerJar = $this->getOption('compiler_path');

    if(!file_exists($compilerJar))
    {
      throw new sfConfigurationException(sprintf('Compiler path "%s" does not point to a compiler jar.', $compilerJar));
    }
  }

  /**
   * Processes the file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the existing file?
   */
  public function doProcessFile($file, $replace = false)
  {
    $command = sprintf('java -jar %s --js %s', escapeshellarg($this->getOption('compiler_path')), escapeshellarg($file));
    $result = shell_exec($command);
    if($replace)
    {
      $this->replaceFile($file, $result);
    }

    return $result;
  }

  /**
   * Processes the string
   *
   * @param string $string
   * @return string Processed string
   */
  public function processString($string)
  {
    // create temporary file
    $tmp = tempnam(sys_get_temp_dir(), 'compiler');
    file_put_contents($tmp, $string);
    $result = $this->doProcessFile($tmp);
    unlink($tmp);

    return $result;
  }

}
