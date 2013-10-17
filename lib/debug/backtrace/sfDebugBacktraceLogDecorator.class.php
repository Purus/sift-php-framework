<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebugBacktraceLogDecorator renders the backtrace suitable for logging to log files
 *
 * @package Sift
 * @subpackage debug
 */
class sfDebugBacktraceLogDecorator extends sfDebugBacktraceDecorator {

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'max_lines' => -1
  );

  /**
   * Output the backtrace suitable for logging
   *
   * @return string
   */
  public function toString()
  {
    $trace = array();
    $maxLines = $this->getOption('max_lines');
    $i = 0;
    foreach($this->getBacktrace()->get() as $t)
    {
      ++$i;
      if($maxLines > 0 && $i > $maxLines)
      {
        break;
      }
      $trace[] = sprintf('#%s %s in %s (%s)', $i, $t['function'], $t['file'], $t['line']);
    }
    return join(PHP_EOL, $trace);
  }

}