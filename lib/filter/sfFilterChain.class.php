<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilterChain manages registered filters for a specific context.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfFilterChain
{
  protected
    $chain = array(),
    $index = -1;

  /**
   * Executes the next filter in this chain.
   */
  public function execute()
  {
    // skip to the next filter
    ++$this->index;

    if ($this->index < count($this->chain))
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info(sprintf('{sfFilter} executing filter "%s"', get_class($this->chain[$this->index])));
      }

      // execute the next filter
      $this->chain[$this->index]->execute($this);
    }
  }

  /**
   * Returns true if the filter chain contains a filter of a given class.
   *
   * @param string The class name of the filter
   *
   * @return boolean true if the filter exists, false otherwise
   */
  public function hasFilter($class)
  {
    foreach ($this->chain as $filter)
    {
      if ($filter instanceof $class)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Registers a filter with this chain.
   *
   * @param sfFilter A sfFilter implementation instance.
   */
  public function register($filter)
  {
    $this->chain[] = $filter;
  }
}
