<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIRichTextEditor is an interface for rich editors
 *
 * @package    Sift
 * @subpackage text_editor
 */
interface sfIRichTextEditor {

  /**
   * Renders the field with given $name as rich editor
   *
   * @param string $name Name of the field
   * @param string $content Text content
   * @param array $options Array of options
   */
  public function toHtml($name, $content, $options = array());

  /**
   * Returns options for javascript usage
   *
   * @return array
   */
  public function getOptionsForJavascript();

}
