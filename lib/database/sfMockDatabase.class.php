<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMockDatabase is a mock database which fakes an database connection.
 *
 * @package    Sift
 * @subpackage database
 */
class sfMockDatabase extends sfDatabase
{
    public function connect()
    {
    }

    public function shutdown()
    {
    }

}
