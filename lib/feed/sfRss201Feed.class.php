<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRss201Feed.
 *
 * @package    Sift
 * @subpackage feed
 */
class sfRss201Feed extends sfRssFeed {

  // the 2.0.1 spec says: "version attribute must be 2.0"
  protected $version = '2.0';

}
