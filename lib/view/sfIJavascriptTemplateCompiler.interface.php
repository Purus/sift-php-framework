<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfJavascriptTemplateCompiler class compiles javascript templates.
 *
 * @package    Sift
 * @subpackage view
 */
interface sfIJavascriptTemplateCompiler
{
    /**
     * Compile the string
     *
     * @param string $string  Compiles the string
     * @param array  $options Array of options for the compilation
     */
    public function compile($string, $options = array());

}
