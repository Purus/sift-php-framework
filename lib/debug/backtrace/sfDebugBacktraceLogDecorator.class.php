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
    'max_lines' => -1,
    'with_arguments' => true,
    'line_separator' => "\t",
    'arguments_separator' => "\t"
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
    $withArguments = $this->getOption('with_arguments');
    $i = 0;
    foreach($this->getBacktrace()->get() as $t)
    {
      ++$i;
      if($maxLines > 0 && $i > $maxLines)
      {
        break;
      }
      $arguments = '';
      if($withArguments && count($t['arguments']))
      {
        $arguments = sprintf("%s arguments: [%s]", $this->getOption('arguments_separator'), $this->formatArguments($t['arguments']));
      }
      $trace[] = sprintf('#%s %s in %s (%s)%s', $i, $t['function'], $t['file'], $t['line'], $arguments);
    }

    return join($this->getOption('line_separator'), $trace);
  }

  /**
   * Format arguments
   *
   * @param array $arguments
   * @return string
   */
  protected function formatArguments($arguments)
  {
    $output = array();
    $i = 0;
    foreach($arguments as $argument)
    {
      $output[] = sprintf('#%s %s|%s', ++$i, $argument['value'], $argument['type']);
    }

    return count($output) ? join(',', $output) : '';
  }

}
