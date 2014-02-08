<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extractor interface
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
interface sfII18nExtractor
{
    /**
     * Extract i18n strings for the given content.
     *
     * @param  string The content
     *
     * @return array An array of i18n strings
     */
    public function extract($content);

}
