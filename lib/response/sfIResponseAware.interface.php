<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIResponseAware interface
 *
 * @package Sift
 * @subpackage response
 */
interface sfIResponseAware {

  /**
   * Sets the response
   *
   * @param sfResponse $response
   */
  public function setResponse(sfResponse $response = null);

  /**
   * Get the response
   *
   * @return sfResponse
   */
  public function getResponse();

}
