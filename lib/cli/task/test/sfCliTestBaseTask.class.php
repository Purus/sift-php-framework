<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base test task
 *
 * @package Sift
 * @subpackage cli_task
 */
abstract class sfCliTestBaseTask extends sfCliBaseTask
{
  /**
   * Filters tests through the "task.test.filter_test_files" event.
   *
   * @param  array $tests     An array of absolute test file paths
   * @param  array $arguments Current task arguments
   * @param  array $options   Current task options
   *
   * @return array The filtered array of test files
   */
  protected function filterTestFiles($tests, $arguments, $options)
  {
    $event = new sfEvent('task.test.filter_test_files', array(
        'task' => $this,
        'arguments' => $arguments,
        'options' => $options)
    );

    $this->dispatcher->filter($event, $tests);
    return $event->getReturnValue();
  }
}
