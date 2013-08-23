<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfComponents.
 *
 * @package    Sift
 * @subpackage action
 */
abstract class sfComponents extends sfComponent {

  public function execute()
  {
    throw new sfInitializationException('Initialization failed');
  }

}
