<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Generates HTML code
 * 
 * @package    Sift
 * @subpackage text
 */
class sfHtml {
  
  protected static
    $xhtml   = true,
    $charset = 'UTF-8';
  
  /**
   * Sets the XHTML generation flag.
   *
   * @param bool $boolean  true if widgets must be generated as XHTML, false otherwise
   */
  static public function setXhtml($boolean)
  {
    self::$xhtml = (boolean) $boolean;
  }

  /**
   * Returns whether to generate XHTML tags or not.
   *
   * @return bool true if widgets must be generated as XHTML, false otherwise
   */
  static public function isXhtml()
  {
    return self::$xhtml;
  }
  
  public static function contentTag($tag, $content = null, $attributes = array())
  {
    if (empty($tag))
    {
      return '';
    }

    return sprintf('<%s%s>%s</%s>', $tag, self::attributesToHtml($attributes), $content, $tag);    
  }
  
  public static function tag($tag, $attributes = array(), $open = false)
  {
    if (empty($tag))
    {
      return '';
    }

    return sprintf('<%s%s%s', $tag, self::attributesToHtml($attributes), (self::$xhtml ? ' />' : '>'));
  }

  /**
   * Converts an array of attributes to its HTML representation.
   *
   * @param  array  $attributes An array of attributes
   *
   * @return string The HTML representation of the HTML attribute array.
   */
  public static function attributesToHtml($attributes)
  {
    return implode('', array_map(array('sfHtml', 'attributesToHtmlCallback'), array_keys($attributes), array_values($attributes)));
  }

  /**
   * Prepares an attribute key and value for HTML representation.
   *
   * It removes empty attributes, except for the value one.
   *
   * @param  string $k  The attribute key
   * @param  string $v  The attribute value
   *
   * @return string The HTML representation of the HTML key attribute pair.
   */
  protected static function attributesToHtmlCallback($k, $v)
  {
    return false === $v || null === $v || ('' === $v && 'value' != $k) ? '' : sprintf(' %s="%s"', $k, self::escapeOnce($v));
  }
  
  /**
   * Escapes a string.
   *
   * @param  string $value  string to escape
   * @return string escaped string
   */
  static public function escapeOnce($value)
  {
    return self::fixDoubleEscape(htmlspecialchars((string) $value, ENT_QUOTES, self::getCharset()));
  }
  
  /**
   * Sets the charset to use when rendering widgets.
   *
   * @param string $charset  The charset
   */
  static public function setCharset($charset)
  {
    self::$charset = $charset;
  }

  /**
   * Returns the charset to use when rendering widgets.
   *
   * @return string The charset (defaults to UTF-8)
   */
  static public function getCharset()
  {
    return self::$charset;
  }

  /**
   * Fixes double escaped strings.
   *
   * @param  string $escaped  string to fix
   * @return string single escaped string
   */
  static public function fixDoubleEscape($escaped)
  {
    return preg_replace('/&amp;([a-z]+|(#\d+)|(#x[\da-f]+));/i', '&$1;', $escaped);
  }
    
  /**
   * Wraps the content in conditional comments for Internet Explorer.
   *
   * @param  string $condition
   * @param  string $content
   * @return string
   * @see http://msdn.microsoft.com/en-us/library/ms537512(VS.85).aspx
   */  
  static public function ieConditionalComment($condition, $content)
  {
    return sprintf('<!--[if %s]>%s<![endif]-->', $condition, $content);
  }
  
}
