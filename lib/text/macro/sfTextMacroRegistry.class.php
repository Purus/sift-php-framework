<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextMacro class is based on shortcodes functionality from Wordpress.
 * It behaves the same, but is wrapped into this class. Macros or shortcodes
 * is wordpress speak are configured in text_macros.yml file inside plugins.
 *
 * @package    Sift
 * @subpackage text
 */
class sfTextMacroRegistry {

  /**
   * Holder for macros
   *
   * @var array
   */
  protected static $macros = array();

  /**
   * Search content for macros and filter macros through their hooks.
   *
   * If there are no macro tags defined, then the content will be returned
   * without any filtering. This might cause issues when plugins are disabled but
   * the macro will still show up in the post or content.
   *
   * @uses getMacroRegex() Gets the search pattern for searching macros.
   *
   * @param string $content Content to search for macros
   * @return string Content with macros filtered out.
   */
  static public function parse($content)
  {
    if(empty(self::$macros) || !is_array(self::$macros))
    {
      return $content;
    }
    $pattern = self::getMacrosRegex();
    return preg_replace_callback('/' . $pattern . '/s', array(__CLASS__, 'doMacroTag'), $content);
  }

  /**
   * Add hook for macro tag.
   *
   * There can only be one hook for each macro. Which means that if another
   * plugin has a similar macro, it will override yours or yours will override
   * theirs depending on which order the plugins are included and/or ran.
   *
   * @param string $tag macro tag to be searched in post content.
   * @param callable $func Hook to run when macro is found.
   * @throws sfException If callable is invalid
   */
  public static function add($tag, $func)
  {
    if(!sfToolkit::isCallable($func, false, $callableName))
    {
      throw new sfException(sprintf('Invalid callable "%s" given. Cannot add text macro for tag "%s"', $callableName, $tag));
    }
    self::$macros[$tag] = $func;
  }

  /**
   * Removes hook for macro
   *
   * @param string $tag macro tag to remove hook for.
   */
  public static function remove($tag)
  {
    unset(self::$macros[$tag]);
  }

  /**
   * Clear all macros.
   *
   */
  public static function clear()
  {
    self::$macros = array();
  }

  /**
   * Regular Expression callable for parse() for calling macro hook.
   * 
   * @see sfTextMacroRegistry::getMacroRegex for details of the match array contents.
   * @param array $m Regular expression match array
   * @return mixed False on failure.
   */
  static protected function doMacroTag($m)
  {
    // allow [[foo]] syntax for escaping a tag
    if($m[1] == '[' && $m[6] == ']')
    {
      return substr($m[0], 1, -1);
    }

    $tag = $m[2];
    $attr = self::parseAttributes($m[3]);

    if(isset($m[5]))
    {
      // enclosing tag - extra parameter
      return $m[1] . call_user_func(self::$macros[$tag], $attr, $m[5], $tag) . $m[6];
    }
    else
    {
      // self-closing tag
      return $m[1] . call_user_func(self::$macros[$tag], $attr, null, $tag) . $m[6];
    }
  }

  /**
   * Retrieve all attributes from the macro tag.
   *
   * The attributes list has the attribute name as the key and the value of the
   * attribute as the value in the key/value pair. This allows for easier
   * retrieval of the attributes, since all attributes have to be known.
   *
   * @param string $text
   * @return array List of attributes and their value.
   */
  static protected function parseAttributes($text)
  {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if(preg_match_all($pattern, $text, $match, PREG_SET_ORDER))
    {
      foreach($match as $m)
      {
        if(!empty($m[1]))
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        elseif(!empty($m[3]))
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        elseif(!empty($m[5]))
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        elseif(isset($m[7]) and strlen($m[7]))
          $atts[] = stripcslashes($m[7]);
        elseif(isset($m[8]))
          $atts[] = stripcslashes($m[8]);
      }
    } else
    {
      $atts = ltrim($text);
    }
    return $atts;
  }

  /**
   * Remove all macro tags from the given content.
   *
   * @since 2.5
   *
   * @param string $content Content to remove macro tags.
   * @return string Content without macro tags.
   */
  static public function stripMacros($content)
  {
    if(empty(self::$macros) || !is_array(self::$macros))
    {
      return $content;
    }

    $pattern = self::getMacrosRegex();

    return preg_replace('/' . $pattern . '/s', '$1$6', $content);
  }

  /**
   * Retrieve the macros regular expression for searching.
   *
   * The regular expression combines the macro tags in the regular expression
   * in a regex class.
   *
   * The regular expresion contains 6 different sub matches to help with parsing.
   *
   * 1/6 - An extra [ or ] to allow for escaping macros with double [[]]
   * 2 - The macro name
   * 3 - The macro argument list
   * 4 - The self closing /
   * 5 - The content of a macro when it wraps some content.
   *
   * @return string The macro search regular expression
   */
  static protected function getMacrosRegex()
  {
    $tagnames = array_keys(self::$macros);
    $tagregexp = join('|', array_map('preg_quote', $tagnames));

    // WARNING! Do not change this regex without changing domacroTag() and stripmacros()
    return '(.?)\[(' . $tagregexp . ')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
  }
}