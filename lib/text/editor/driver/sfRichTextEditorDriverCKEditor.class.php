<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Rich editor for CKEditor.
 *
 * @package    Sift
 * @subpackage text_editor
 * @see        http://ckeditor.com/
 */
class sfRichTextEditorDriverCKEditor extends sfRichTextEditor
{
  /**
   * Returns the rich text editor as HTML.
   *
   * @param string $name Field name
   * @param string $content Text content
   * @param array $options Array of options
   * @return string Rich text editor HTML representation
   */
  public function toHTML($name, $content, $options = array())
  {
    // not implemented yet
  }

}
