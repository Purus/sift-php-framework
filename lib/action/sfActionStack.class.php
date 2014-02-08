<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActionStack keeps a list of all requested actions and provides accessor
 * methods for retrieving individual entries.
 *
 * @package    Sift
 * @subpackage action
 */
class sfActionStack
{
  protected $stack = array();

  /**
   * Entry count
   *
   * @var boolean
   */
  protected $count = 0;

  /**
   * Adds an entry to the action stack.
   *
   * @param string   A module name
   * @param string   An action name
   * @param sfAction An sfAction implementation instance
   *
   * @return sfActionStackEntry sfActionStackEntry instance
   */
  public function addEntry($moduleName, $actionName, $actionInstance)
  {
    // create our action stack entry and add it to our stack
    $actionEntry = new sfActionStackEntry($moduleName, $actionName, $actionInstance);

    $this->stack[] = $actionEntry;
    $this->count = count($this->stack);

    return $actionEntry;
  }

  /**
   * Retrieves the entry at a specific index.
   *
   * @param int An entry index
   *
   * @return sfActionStackEntry An action stack entry implementation.
   */
  public function getEntry($index)
  {
    $retval = null;

    if ($index > -1 && $index < $this->count) {
      $retval = $this->stack[$index];
    }

    return $retval;
  }

  /**
   * Removes the entry at a specific index.
   *
   * @param int An entry index
   *
   * @return sfActionStackEntry An action stack entry implementation.
   */
  public function popEntry()
  {
    $result = array_pop($this->stack);
    $this->count = count($this->stack);

    return $result;
  }

  /**
   * Retrieves the first entry.
   *
   * @return mixed An action stack entry implementation or null if there is no sfAction instance in the stack
   */
  public function getFirstEntry()
  {
    $retval = null;

    if (isset($this->stack[0])) {
      $retval = $this->stack[0];
    }

    return $retval;
  }

  /**
   * Retrieves the last entry.
   *
   * @return mixed An action stack entry implementation or null if there is no sfAction instance in the stack
   */
  public function getLastEntry()
  {
    $retval = null;

    if (isset($this->stack[0])) {
      $retval = $this->stack[$this->count - 1];
    }

    return $retval;
  }

  /**
   * Retrieves the size of this stack.
   *
   * @return int The size of this stack.
   */
  public function getSize()
  {
    return $this->count;
  }
}
