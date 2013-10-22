<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDebugBacktraceDecorator is a base class for backtrace decorators
 *
 * @package Sift
 * @subpackage debug
 */
abstract class sfDebugBacktraceDecorator extends sfConfigurable implements sfIDebugBacktraceDecorator {

  /**
   * The backtrace
   *
   * @var sfDebugBacktrace
   */
  protected $backtrace;

  /**
   * Constructor
   *
   * @param sfDebugBacktrace $backtrace
   * @param array $options
   */
  public function __construct(sfDebugBacktrace $backtrace = null, $options = array())
  {
    $this->backtrace = $backtrace;
    parent::__construct($options);
  }

  /**
   * Sets the backtrace
   *
   * @param sfDebugBacktrace $backtrace
   */
  public function setBacktrace(sfDebugBacktrace $backtrace)
  {
    $this->backtrace = $backtrace;
  }

  /**
   * Returns the backtrace
   *
   * @return sfDebugBacktrace
   */
  public function getBacktrace()
  {
    return $this->backtrace;
  }

  /**
   * Renders the file with local $vars
   *
   * @param string $file The absolute path to a file
   * @param array $vars The array of variables
   */
  protected function render($file, $vars = null)
  {
    return sfLimitedScope::render($file, $vars);
  }

  /**
   * Renders the backtrace
   *
   * @return string
   */
  public function __toString()
  {
    return $this->toString();
  }

}
