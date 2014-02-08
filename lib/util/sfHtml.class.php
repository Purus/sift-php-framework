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
 * @subpackage util
 */
class sfHtml {

  protected static $xhtml = true,
    $charset = 'UTF-8';

  /**
   * Sets the XHTML generation flag.
   *
   * @param bool $boolean  true if widgets must be generated as XHTML, false otherwise
   */
  public static function setXhtml($boolean)
  {
    self::$xhtml = (boolean) $boolean;
  }

  /**
   * Returns whether to generate XHTML tags or not.
   *
   * @return bool true if widgets must be generated as XHTML, false otherwise
   */
  public static function isXhtml()
  {
    return self::$xhtml;
  }

  /**
   * Returns content tag
   *
   * @param string $tag Tag name like li, strong...
   * @param string $content Content
   * @param array $attributes Array of attributes
   * @return string
   */
  public static function contentTag($tag, $content = null, $attributes = array())
  {
    if(empty($tag))
    {
      return '';
    }

    return sprintf('<%s%s>%s</%s>', $tag, self::attributesToHtml($attributes), $content, $tag);
  }

  /**
   * Returns tag
   *
   * @param string $tag Tag name like div, span...
   * @param array $attributes
   * @param boolean $open Leave the tag opened?
   * @return string
   */
  public static function tag($tag, $attributes = array(), $open = false)
  {
    if(empty($tag))
    {
      return '';
    }

    return sprintf('<%s%s%s', $tag, self::attributesToHtml($attributes), $open ?
                    '>' : ((self::$xhtml ? ' />' : '>')));
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
    // this is a data attribute, leave it here!
    if(strpos($k, 'data-') === 0)
    {
      return sprintf(' %s="%s"', $k, self::escapeOnce($v));
    }
    return false === $v || null === $v || ('' === $v && 'value' != $k) ? '' : sprintf(' %s="%s"', $k, self::escapeOnce($v));
  }

  /**
   * Escapes a string.
   *
   * @param  string $value  string to escape
   * @return string escaped string
   */
  public static function escapeOnce($value)
  {
    return self::fixDoubleEscape(htmlspecialchars(!is_array($value) ? (string) $value : null, ENT_COMPAT, self::getCharset()));
  }

  /**
   * Sets the charset to use when rendering widgets.
   *
   * @param string $charset  The charset
   */
  public static function setCharset($charset)
  {
    self::$charset = $charset;
  }

  /**
   * Returns the charset to use when rendering widgets.
   *
   * @return string The charset (defaults to UTF-8)
   */
  public static function getCharset()
  {
    return self::$charset;
  }

  /**
   * Fixes double escaped strings.
   *
   * @param  string $escaped  string to fix
   * @return string single escaped string
   */
  public static function fixDoubleEscape($escaped)
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
  public static function ieConditionalComment($condition, $content)
  {
    return sprintf('<!--[if %s]>%s<![endif]-->', $condition, $content);
  }

  /**
   * Adds another CSS class to the list of CSS classes
   *
   * @param string|array $class The string or array of CSS classes to add the $another
   * @param string $another
   * @return string
   */
  public static function addCssClass($class, $another)
  {
    if(is_string($class))
    {
      $class = explode(' ', $class);
    }
    elseif(!is_array($class))
    {
      $class = array((string)$class);
    }
    return trim(join(' ', array_unique(array_merge($class, explode(' ', $another)))));
  }

  /**
   * Returns CDATA section with the given content
   *
   * @param string $content
   * @return string
   */
  public static function cdataSection($content)
  {
    return "<![CDATA[$content]]>";
  }

  /**
   * Returns the CDATA section with given $javascript. For usage in XML.
   *
   * @param string $javascript The javascript
   * @return string
   */
  public static function javascriptCdataSection($javascript)
  {
    return "\n//".self::cdataSection("\n$javascript\n//")."\n";
  }

  /**
   * Returns the javascript tag
   *
   * @param string $javascript The javascript
   * @param string $type The type
   */
  public static function javascriptTag($javascript, $type = 'text/javascript')
  {
    return self::contentTag('script',
            self::isXhtml() ? self::javascriptCdataSection($javascript) : ("\n" . $javascript . "\n"),
              array('type' => $type));
  }

}
