<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfText class provides various text utility functions.
 *
 * @package    Sift
 * @subpackage text
 */
class sfText {

  const AUTO_LINK_RE = '~
    (                       # leading text
      <\w+.*?>|             #   leading HTML tag, or
      [^=!:\'"/]|           #   leading punctuation, or
      ^                     #   beginning of line
    )
    (
      (?:https?://)|        # protocol spec, or
      (?:www\.)             # www.*
    )
    (
      [-\w]+                   # subdomain or domain
      (?:\.[-\w]+)*            # remaining subdomains or domain
      (?::\d+)?                # port
      (?:/(?:(?:[\~\w\+%-]|(?:[,.;:][^\s$]))+)?)* # path
      (?:\?[\w\+%&=.;-]+)?     # query string
      (?:\#[\w\-]*)?           # trailing anchor
    )
    ([[:punct:]]|\s|<|$)    # trailing text
   ~x';

  /**
   * Truncates text (Taken from CakePhp framework)
   *
   * Cuts a string to the length of $length and replaces the last characters
   * with the ending if the text is longer than length.
   *
   * @param string  $text String to truncate.
   * @param integer $length Length of returned string, including ellipsis.
   * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
   * @param boolean $exact If false, $text will not be cut mid-word
   * @param boolean $considerHtml If true, HTML tags would be handled correctly
   * @return string Trimmed string.
   */
  public static function truncate($text, $length = 100,
          $ending = '&hellip;', $exact = false, $considerHtml = true)
  {
    // get the charset
    $charset = sfConfig::get('sf_charset', 'UTF-8');
    $ending  = mb_convert_encoding($ending, sfConfig::get('sf_charset'), 'HTML-ENTITIES');

    if($considerHtml)
    {
      if(mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length)
      {
        return $text;
      }

      $totalLength = mb_strlen($ending, $charset);
      $openTags = array();
      $truncate = '';

      preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);

      foreach($tags as $tag)
      {
        if(!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2]))
        {
          if(preg_match('/<[\w]+[^>]*>/s', $tag[0]))
          {
            array_unshift($openTags, $tag[2]);
          }
          else if(preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag))
          {
            $pos = array_search($closeTag[1], $openTags);
            if($pos !== false)
            {
              array_splice($openTags, $pos, 1);
            }
          }
        }
        $truncate .= $tag[1];
        $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]), $charset);
        if($contentLength + $totalLength > $length)
        {
          $left = $length - $totalLength;
          $entitiesLength = 0;
          if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE))
          {
            foreach($entities[0] as $entity)
            {
              if($entity[1] + 1 - $entitiesLength <= $left)
              {
                $left--;
                $entitiesLength += mb_strlen($entity[0], $charset);
              }
              else
              {
                break;
              }
            }
          }

          $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength, $charset);
          break;
        }
        else
        {
          $truncate .= $tag[3];
          $totalLength += $contentLength;
        }
        if($totalLength >= $length)
        {
          break;
        }
      }
    }
    else
    {
      if(mb_strlen($text, $charset) <= $length)
      {
        return $text;
      }
      else
      {
        $truncate = mb_substr($text, 0, $length - mb_strlen($ending, $charset), $charset);
      }
    }
    if(!$exact)
    {
      $spacepos = mb_strrpos($truncate, ' ', $charset);
      if($spacepos)
      {
        if($considerHtml)
        {
          $bits = mb_substr($truncate, $spacepos, 0, $charset);
          preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
          if(!empty($droppedTags))
          {
            foreach($droppedTags as $closingTag)
            {
              if(!in_array($closingTag[1], $openTags))
              {
                array_unshift($openTags, $closingTag[1]);
              }
            }
          }
        }
        $truncate = mb_substr($truncate, 0, $spacepos, $charset);
      }
    }
    $truncate .= $ending;
    if($considerHtml)
    {
      foreach($openTags as $tag)
      {
        $truncate .= '</' . $tag . '>';
      }
    }
    return $truncate;
  }

  /**
   * Highlights the +phrase+ where it is found in the +text+ by surrounding it like
   * <strong class="highlight">I'm a highlight phrase</strong>. The highlighter can be specialized by
   * passing +highlighter+ as single-quoted string with \1 where the phrase is supposed to be inserted.
   *
   * @param string $text
   * @param string|array $phrase Phrase to highlight in the text
   * @param string $highlighter default <strong class="highlight">\1</strong>
   * @return string string
   */
  public static function highlight($text, $phrase, $highlighter = '<strong class="highlight">\1</strong>')
  {
    if(empty($text))
    {
      return '';
    }

    // FIXME: cannot highlight HTML text
    if(self::isHtml($text))
    {
      return $text;
    }

    if(!is_array($phrase))
    {
      $phrase = array($phrase);
    }
    
    $highlighter = sprintf('\\1%s\\3', str_replace('\1', '\\2', $highlighter));

    foreach($phrase as $p)
    {
      if(empty($p))
      {
        continue;
      }
      $text = preg_replace('/(^|\s|,!|;)(' . preg_quote($p, '/') . ')(\s|,|!|&|$)/i', $highlighter, $text);
    }

    return $text;
  }

  /**
   * Extracts an excerpt from the +text+ surrounding the +phrase+ with a number of characters on each side determined
   * by +radius+. If the phrase isn't found, null is returned. Ex:
   *
   * sfText::excerpt("hello my world", "my", 3) => "...lo my wo..."
   *
   * If +excerpt_space+ is true the text will only be truncated on whitespace, never inbetween words.
   * This might return a smaller radius than specified.
   *
   * sfText::excerpt("hello my world", "my", 3, "...", true) => "... my ..."
   *
   */
  public static function excerpt($text, $phrase, $radius = 100, $excerpt_string = '&hellip;', $excerpt_space = false)
  {
    if($text == '' || $phrase == '')
    {
      return '';
    }

    $excerpt_string = mb_convert_encoding($excerpt_string,
            sfConfig::get('sf_charset'), 'HTML-ENTITIES');

    $found_pos = sfUtf8::pos(sfUtf8::lower($text), sfUtf8::lower($phrase));

    if($found_pos !== false)
    {
      $start_pos = max($found_pos - $radius, 0);
      $end_pos = min($found_pos + sfUtf8::len($phrase) + $radius, sfUtf8::len($text));
      $excerpt = sfUtf8::sub($text, $start_pos, $end_pos - $start_pos);

      $prefix = ($start_pos > 0) ? $excerpt_string : '';
      $postfix = $end_pos < sfUtf8::len($text) ? $excerpt_string : '';

      if($excerpt_space)
      {
        // only cut off at ends where $excerpt_string is added
        if($prefix)
        {
          $excerpt = preg_replace('/^(\S+)?\s+?/', ' ', $excerpt);
        }
        if($postfix)
        {
          $excerpt = preg_replace('/\s+?(\S+)?$/', ' ', $excerpt);
        }
      }
      return $prefix . $excerpt . $postfix;
    }
  }

  /**
   * Word wrap long lines to line_width.
   *
   * @param string $text
   * @param integer $line_width
   */
  public static function wrap($text, $line_width)
  {
    return preg_replace('/(.{1,' . $line_width . '})(\s+|$)/s', "\\1\n", preg_replace("/\n/", "\n\n", $text));
  }

  /**
   * Returns +text+ transformed into html using very simple formatting rules
   * Surrounds paragraphs with <tt>&lt;p&gt;</tt> tags, and converts line breaks into <tt>&lt;br /&gt;</tt>
   * Two consecutive newlines(<tt>\n\n</tt>) are considered as a paragraph, one newline (<tt>\n</tt>) is
   * considered a linebreak, three or more consecutive newlines are turned into two newlines
   *
   * @param string $text
   * @param array $options
   * @return string
   */
  public static function simpleFormat($text, $options = array())
  {
    $css = (isset($options['class'])) ? ' class="' . $options['class'] . '"' : '';

    $text = sfToolkit::pregtr($text, array("/(\r\n|\r)/" => "\n", // lets make them newlines crossplatform
                "/\n{3,}/" => "\n\n", // zap dupes
                "/\n\n/" => "</p>\\0<p$css>", // turn two newlines into paragraph
                "/([^\n])\n([^\n])/" => "\\1\n<br />\\2")); // turn single newline into <br/>

    return '<p' . $css . '>' . $text . '</p>'; // wrap the first and last line in paragraphs before we're done
  }

  /**
   * Turns all urls and email addresses into clickable links. The +link+ parameter can limit what should be linked.
   * Options are :all (default), :email_addresses, and :urls.
   *
   * Example:
   *   auto_link("Go to http://www.example.com and say hello to user.potencier@example.com") =>
   *     Go to <a href="http://www.example.com">http://www.example.com</a> and
   *     say hello to <a href="mailto:user@example.com">user@example.com</a>
   *
   * @param string $text
   * @param string $link One of "all", "email_addresses" or "urls"
   * @param array $href_options
   * @return string
   */
  public static function autoLink($text, $link = 'all', $href_options = array())
  {
    if($link == 'all')
    {
      return self::autoLinkUrls(self::autoLinkEmailAddresses($text), $href_options);
    }
    else if($link == 'email_addresses')
    {
      return self::autoLinkEmailAddresses($text);
    }
    else if($link == 'urls')
    {
      return self::autoLinkUrls($text, $href_options);
    }
    else
    {
      throw new sfException('Unknown option for sfText::autoLink(). Valid link options are: "all", "email_addresses", "urls"');
    }
  }

  /**
   * Turns all email addresses into clickable links.
   *
   * @param <type> $text
   * @return <type>
   */
  public static function autoLinkEmailAddresses($text)
  {
    return preg_replace('/([\w\.!#\$%\-+.]+@[A-Za-z0-9\-]+(\.[A-Za-z0-9\-]+)+)/', '<a href="mailto:\\1">\\1</a>', $text);
  }

  /**
   * Turns all website addresses into clickable links.
   *
   * @param string $text
   * @param array $href_options
   * @return string
   */
  public static function autoLinkUrls($text, $href_options = array())
  {
    sfLoader::loadHelpers('Tag');

    $href_options = _tag_options($href_options);
    return preg_replace_callback(
      self::AUTO_LINK_RE,
      create_function('$matches', '
      if(preg_match("/<a\s/i", $matches[1]))
      {
        return $matches[0];
      }
      else
      {
        return $matches[1].\'<a href="\'.($matches[2] == "www." ? "http://www." : $matches[2]).$matches[3].\'"' . $href_options . '>\'.$matches[2].$matches[3].\'</a>\'.$matches[4];
      }
    ')
    , $text);
  }

  /**
   * Turns all links into words, like "<a href="something">else</a>" to "else".
   *
   * @param string $text
   * @return string
   */
  public static function stripLinks($text)
  {
    return preg_replace('/<a.*>(.*)<\/a>/m', '\\1', $text);
  }

  /**
   * Returns count of words in given text
   *
   * @param string $text
   * @return integer
   */
  public static function getWordsCount($text)
  {
    preg_match_all("/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u", $text, $matches);
    return count($matches[0]);
  }

  /**
   * This function will strip tags from a string, split it at a defined maximum length,
   * and insert an ellipsis.
   *
   * The first parameter is the string to ellipsize, the second is the number of characters in the final string. The third parameter is where in the string the ellipsis should appear from 0 - 1, left to right. For example. a value of 1 will place the ellipsis at the right of the string, .5 in the middle, and 0 at the left.
   * An optional forth parameter is the kind of ellipsis. By default, &hellip; will be inserted.
   *
   * @param string $str
   * @param integer $max_length
   * @param flaot $position
   * @param string $ellipsis
   * @return string
   * @see http://codeigniter.com/user_guide/helpers/text_helper.html
   */
  public static function ellipsize($str, $max_length, $position = 1, $ellipsis = '&hellip;')
	{
		// Strip tags
		$str = trim(strip_tags($str));

		// Is the string long enough to ellipsize?
		if(sfUtf8::len($str) <= $max_length)
		{
			return $str;
		}

		$beg = sfUtf8::sub($str, 0, floor($max_length * $position));

		$position = ($position > 1) ? 1 : $position;

		if ($position === 1)
		{
			$end = sfUtf8::sub($str, 0, -($max_length - sfUtf8::len($beg)));
		}
		else
		{
			$end = sfUtf8::sub($str, -($max_length - sfUtf8::len($beg)));
		}

		return $beg.$ellipsis.$end;
	}

  /**
   * Does the given $string contain HTML?
   *
   * @param string $string
   * @return boolean
   */
  public static function isHtml($string)
  {
    return preg_match("/<[^<]+>/", $string) != 0;
  }

}