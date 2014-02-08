<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIResponse interface for response objects
 *
 * @package Sift
 * @subpackage response
 */
interface sfIResponse extends Serializable, sfIService, sfIConfigurable {

  /**
   * Sets the response content
   *
   * @param string Content
   * @return sfResponse
   */
  public function setContent($content);

  /**
   * Gets the current response content
   *
   * @return string Content
   */
  public function getContent();

  /**
   * Sends the response to the client
   */
  public function send();

}
