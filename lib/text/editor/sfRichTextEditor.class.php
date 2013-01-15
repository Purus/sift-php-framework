<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRichTextEditor is an abstract class for rich text editor classes.
 *
 * @package    Sift
 * @subpackage helper
 */
abstract class sfRichTextEditor
{
  protected
    $name = '',
    $content = '',
    $options = array();

  /**
   * Initializes this rich text editor.
   *
   * @param string The tag name
   * @param string The rich text editor content
   * @param array  An array of options
   */
  public function initialize($name, $content, $options = array())
  {
    $this->name = $name;
    $this->content = $content;
    $this->options = $options;
  }

  /**
   * Returns the rich text editor as HTML.
   *
   * @return string Rich text editor HTML representation
   */
  abstract public function toHTML();
}
