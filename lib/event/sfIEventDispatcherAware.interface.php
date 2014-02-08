<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event dispatcher aware interface
 *
 * @package Sift
 * @subpackage event
 */
interface sfIEventDispatcherAware
{
 /**
  * Sets the event dispatcher
  *
  * @param sfEventDispatcher
  * @return void
  */
  public function setEventDispatcher(sfEventDispatcher $dispatcher = null);

  /**
   * Returns the event dispatcher instance
   *
   * @return sfEventDispatcher|null
   */
  public function getEventDispatcher();

}
