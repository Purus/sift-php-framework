<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Antivirus driver for Clamav antivirus. Binary executable implementation.
 *
 * @package Sift
 * @subpackage antivirus
 * @link http://phpmaster.com/zf-clamav/
 * @link http://sourceforge.net/p/clamwin/discussion/363174/thread/a5c5aeaa
 */
class sfAntivirusDriverClamav extends sfAntivirus {

  /**
   * Nothing found regexp
   */
  const NOTHING_FOUND_REGEXP = '/\bOK$/';

  /**
   * Something found regexp
   */
  const SOMETHING_FOUND_REGEXP = '/\bFOUND$/';

  /**
   * Infection regexp
   */
  const INFECTIONS_REGEXP = '/^.*?: (?!Infected Archive)(.*) FOUND$/';

  /**
   * Default options
   * @var array
   */
  protected $defaultOptions = array(
    'database' => false
  );

  /**
   * Required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'executable'
  );

  /**
   * Setup an driver instance
   *
   */
  protected function setup()
  {
    // check if we can execute command line scripts
    if(!sfToolkit::isCallable('exec'))
    {
      throw new InvalidArgumentException('The driver cannot execute command line scripts. Function "exec" is not available. Please enable it in the php.ini or use another setting/driver.');
    }
  }

  /**
   * Scans an object.
   *
   * @param string $object Path to a file or directory
   * @return array Array of
   */
  public function scan($object)
  {
    if(!is_file($object) && !is_dir($object) && !is_link($object))
    {
      throw new InvalidArgumentException('Cannot scan the object. This is not a file, symlink nor directory');
    }

    $output = array();
    $return = -1;

    if($database = $this->getOption('database'))
    {
      $cmd = sprintf('%s --database=%s --verbose %s', $this->getOption('executable'),
          escapeshellarg($database), escapeshellarg($object));
    }
    else
    {
      $cmd = sprintf('%s --verbose %s', $this->getOption('executable'), escapeshellarg($object));
    }

    exec($cmd, $output, $return);

    $status = self::STATUS_CLEAN;

    // check return code, if its not 0, we know that the status is "Infected"
    if($return != 0)
    {
      $status = self::STATUS_INFECTED;
    }

    $viruses = array();

    foreach($output as $line)
    {
      if(preg_match(self::INFECTIONS_REGEXP, $line, $matches))
      {
        $viruses[] = $matches[1];
      }
    }

    return array($status, array_unique($viruses));
  }

}