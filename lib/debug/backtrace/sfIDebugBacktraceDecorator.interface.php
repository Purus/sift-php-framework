<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIDebugBacktraceDecorator is an interface for backtrace decorators
 *
 * @package Sift
 * @subpackage debug
 */
interface sfIDebugBacktraceDecorator {

  /**
   * Renders the backtrace
   */
  public function toString();

  /**
   * Sets the backtrace
   *
   * @param sfDebugBacktrace $backtrace
   */
  public function setBacktrace(sfDebugBacktrace $backtrace);

  /**
   * Returns the backtrace
   *
   * @return sfDebugBacktrace
   */
  public function getBacktrace();

}