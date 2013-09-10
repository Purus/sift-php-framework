<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service container aware interface
 *
 * @package    Sift
 * @subpackage service
 */
interface sfIServiceContainerAware {

  /**
   * Sets the service container
   *
   * @param sfServiceContainer $container
   */
  public function setServiceContainer(sfServiceContainer $container = null);

  /**
   * Returns the service container
   *
   * @return sfServiceContainer|null
   */
  public function getServiceContainer();
}
