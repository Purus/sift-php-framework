<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextMacroWidget interface
 *
 * @package    Sift
 * @subpackage text_macro
 */
interface sfITextMacroWidget
{
    /**
     * Return the HTML code
     *
     * @param array  $attributes Array of attributes
     * @param string $value      The value
     */
    public function getHtml($attributes, $value = null);

    /**
     * Returns an array of stylesheets
     *
     * @return array
     */
    public function getStylesheets();

    /**
     * Returns an array of javascripts
     *
     * @return array
     */
    public function getJavascripts();

}
