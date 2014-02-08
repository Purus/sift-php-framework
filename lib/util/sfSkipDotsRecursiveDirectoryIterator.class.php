<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides an interface for iterating recursively over filesystem directories.
 *
 * Manually skips '.' and '..' directories, since no existing method is
 * available in PHP 5.2.
 *
 * @todo Depreciate in favor of RecursiveDirectoryIterator::SKIP_DOTS once PHP 5.3 or later is required.
 * @package Sift
 * @subpackage util
 */
class sfSkipDotsRecursiveDirectoryIterator extends RecursiveDirectoryIterator
{
    /**
     * Constructs a SkipDotsRecursiveDirectoryIterator
     *
     * @param $path The path of the directory to be iterated over.
     */
    public function __construct($path)
    {
        parent::__construct($path);
        $this->skipDots();
    }

    public function rewind()
    {
        parent::rewind();
        $this->skipDots();
    }

    public function next()
    {
        parent::next();
        $this->skipDots();
    }

    /**
     * Skips . and ..
     */
    protected function skipDots()
    {
        while ($this->isDot()) {
            parent::next();
        }
    }
}
