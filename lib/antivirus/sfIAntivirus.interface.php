<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Antivirus interface
 *
 * @package    Sift
 * @subpackage antivirus
 */
interface sfIAntivirus
{
    /**
     * File is clean
     */
    const STATUS_CLEAN = 'OK';

    /**
     * File is infected
     */
    const STATUS_INFECTED = 'INFECTED';

    /**
     * Scans object
     *
     * @param string $object
     */
    public function scan($object);

}
