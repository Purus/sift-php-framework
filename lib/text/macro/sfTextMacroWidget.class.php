<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextMacroWidgetBase class - base class for all macros widgets, your
 * macros widget should extend this class
 *
 * @package    Sift
 * @subpackage text
 */
abstract class sfTextMacroWidget implements sfITextMacroWidget {

  /**
   * Combine user attributes with known attributes and fill in defaults when needed.
   *
   * The pairs should be considered to be all of the attributes which are
   * supported by the caller and given as a list. The returned attributes will
   * only contain the attributes in the $pairs list.
   *
   * If the $atts list has unsupported attributes, then they will be ignored and
   * removed from the final returned list.
   *
   *
   * @param array $defaults Entire list of supported attributes and their defaults.
   * @param array $attributes User defined attributes in macro tag.
   * @return array Combined and filtered attribute list.
   */
  public function getAttributes($defaults, $attributes)
  {
    $attributes = (array) $attributes;
    $out = array();
    foreach($defaults as $name => $default)
    {
      if(array_key_exists($name, $attributes))
      {
        $out[$name] = $attributes[$name];
      }
      else
      {
        $out[$name] = $default;
      }
    }
    return $out;
  }

  /**
   * @see sfITextMacroWidget
   */
  public function getStylesheets()
  {
    return array();
  }

  /**
   * @see sfITextMacroWidget
   */
  public function getJavascripts()
  {
    return array();
  }

}
