<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base task for build tasks
 *
 * @package    Sift
 * @subpackage build
 */
abstract class sfCliBaseBuildTask extends sfCliCommandApplicationTask
{

    /**
     * Return array of culture to build data for
     *
     * @return array
     */
    public function getCultures()
    {
        return array(
            'cs',
            'cs_CZ', // czech
            'sk',
            'sk_SK', // slovak
            'de',
            'de_AT',
            'de_DE', // deutsch
            'en',
            'en_GB',
            'en_US',
            'en_US_POSIX', // english
            'fr',
            'fr_FR', // french
        );
    }

    /**
     * Returns the filesystem instance.
     *
     * @return sfFilesystem A sfFilesystem instance
     */
    public function getFilesystem()
    {
        if (!isset($this->filesystem)) {
            if (null === $this->commandApplication || $this->commandApplication->isVerbose()) {
                $this->filesystem = new sfFilesystem($this->logger, $this->formatter);
            } else {
                $this->filesystem = new sfFilesystem();
            }
        }

        return $this->filesystem;
    }
}
