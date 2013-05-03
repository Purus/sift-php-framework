<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP partial view for javascript (.pjs)
 *
 * @package    Sift
 * @subpackage view
 */
class sfJavascriptPartialView extends sfPartialView
{
  protected $extension = '.pjs';

  public function configure()
  {
    parent::configure();
    // disable escaping
    $this->setEscaping(false);
  }
}
