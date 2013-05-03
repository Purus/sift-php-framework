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
abstract class sfTextMacroWidgetBase implements sfTextMacroWidget {

  /**
   * Display the widget as HTML, if available
   *
   * @param $attributes Array
   * @param $value String[optional]
   * @param $class String[optional]
   * @return $html String HTML shortcode equivalent
   */
  public static function getHtml($attributes, $value = null)
  {
    throw new sfException('{sfTextMacroWidgetBase} You should create your own getHtml() method.');
  }

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
  public static function getAttributes($defaults, $attributes)
  {
    $attributes = (array) $attributes;
    $out        = array();
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
   * Adds stylesheet to response (params are the same for use_stylesheet()
   * helper function
   *
   * @param string $stylesheet
   * @param string $position
   * @param array $options
   */
  public static function addStylesheetToResponse($stylesheet, $position = '', $options = array())
  {
    sfLoader::loadHelpers(array('Asset'));
    use_stylesheet($stylesheet, $position, $options);    
  }

  /**
   * Adss javascript to response  (params are the same for use_javascript()
   * helper function
   *
   * @param string $javascript
   * @param string $position
   * @param array $options
   */
  public static function addJavascriptToResponse($javascript, $position = '', $options = array())
  {
    sfLoader::loadHelpers(array('Asset'));
    use_javascript($javascript, $position, $options);
  }

}
