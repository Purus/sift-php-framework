<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextFilter interface
 *
 * @package    Sift
 * @subpackage text_filter
 */
interface sfITextFilter
{
    /**
     * Filters given content
     *
     * @param sfTextFilterContent $content The text filter content
     */
    public function filter(sfTextFilterContent $content);

}
