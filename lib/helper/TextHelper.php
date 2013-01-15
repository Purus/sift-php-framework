<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * TextHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

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
function truncate_text($text, $length = 30, $ending = '&hellip;', $exact = false, $considerHtml = true)
{
  return myText::truncate($text, $length, $ending, $exact, $considerHtml);
}

/**
 * Highlights the +phrase+ where it is found in the +text+ by surrounding it like
 * <strong class="highlight">I'm a highlight phrase</strong>. The highlighter can be specialized by
 * passing +highlighter+ as single-quoted string with \1 where the phrase is supposed to be inserted.
 * N.B.: The +phrase+ is sanitized to include only letters, digits, and spaces before use.
 */
function highlight_text($text, $phrase, $highlighter = '<strong class="highlight">\1</strong>')
{
  return myText::highlight($text, $phrase, $highlighter);
}

/**
 * Extracts an excerpt from the +text+ surrounding the +phrase+ with a number of characters on each side determined
 * by +radius+. If the phrase isn't found, nil is returned. Ex:
 *   excerpt("hello my world", "my", 3) => "...lo my wo..."
 * If +excerpt_space+ is true the text will only be truncated on whitespace, never inbetween words.
 * This might return a smaller radius than specified.
 *   excerpt("hello my world", "my", 3, "...", true) => "... my ..."
 */
function excerpt_text($text, $phrase, $radius = 100, $excerpt_string = '...', $excerpt_space = false)
{
  return myText::excerpt($text, $phrase, $radius, $excerpt_string, $excerpt_space);
}

/**
 * Word wrap long lines to line_width.
 *
 * @param string $text
 * @param integer $line_width
 */
function wrap_text($text, $line_width = 80)
{
  return myText::wrap($text, $line_width);
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
function simple_format_text($text, $options = array())
{
  return myText::simpleFormat($text, $options);
}

/**
 * Turns all urls and email addresses into clickable links. The +link+ parameter can limit what should be linked.
 * Options are :all (default), :email_addresses, and :urls.
 *
 * Example:
 *   auto_link("Go to http://www.symfony-project.com and say hello to fabien.potencier@example.com") =>
 *     Go to <a href="http://www.symfony-project.com">http://www.symfony-project.com</a> and
 *     say hello to <a href="mailto:fabien.potencier@example.com">fabien.potencier@example.com</a>
 *
 * @param string $text
 * @param string $link One of "all", "email_addresses" or "urls"
 * @param array $href_options
 * @return string
 */
function auto_link_text($text, $link = 'all', $href_options = array())
{
  return myText::autoLink($text, $link, $href_options);
}

/**
 * Turns all links into words, like "<a href="something">else</a>" to "else".
 *
 * @param string $text
 * @return string
 */
function strip_links_text($text)
{
  return myText::stripLinks($text);
}

/**
 * Formats filesize
 *
 * @param integer $size Size of the file in bytes
 * @param integer $round Precision
 * @return string Formatted filesize (100 kB)
 * @deprecated since version 1.5.8
 */
function format_file_size($size, $round = 1)
{
  trigger_error('Deprecated usage of format_file_size(). Use file_format_size() is FileHelper.');
  use_helper('File');
  
  return file_format_size($size, $round);
}

/**
 * Formats zip code (currently supports only czech zip codes)
 *
 * @param string $zip_code
 * @return string Formatted zipcode
 */
function format_zip_code($zip_code)
{
  $zip_code = preg_replace("~[^0-9]~", '', $zip_code);
  $first_part = substr($zip_code, 0, 3);
  $second_part = substr($zip_code, 3, 5);
  return sprintf('%s %s', $first_part, $second_part);
}

/**
 * Formats address
 *
 * @param string $address
 * @return string Formatted address
 */
function format_address($address)
{
  $address = preg_replace('/,/', ',<br />', $address);
  return $address;
}

/**
 * Formats phone number
 *
 * @param string $phone_number
 * @return string Formatted phone number
 */
function format_phone_number($phone_number)
{
  $phone_number = preg_replace("[^0-9+]", '', $phone_number);
  $length = strlen($phone_number);
  if($length == 9 && $phone_number[0] != '+')
  {
    $phone_number = sprintf('%s %s %s',
                    substr($phone_number, 0, 3),
                    substr($phone_number, 3, 3),
                    substr($phone_number, 6, 3));
  }
  // we have number like this: +420774868002
  else if($length == 13 && $phone_number[0] == '+')
  {
    $phone_number = sprintf('%s %s %s %s',
                    substr($phone_number, 0, 4),
                    substr($phone_number, 4, 3),
                    substr($phone_number, 7, 3),
                    substr($phone_number, 10, 3));
  }
  return $phone_number;
}

/**
 * Apply filters to given variable
 *
 * @param string $content
 * @return sring string
 */
function get_content($content, $filter = 'content')
{
  return sfCore::applyFilter($filter, $content);
}

/**
 *
 * Toggle between $one and $two
 *
 * @access public
 * @param mixed $one Any variable/object
 * @param mixed $two Any variable/object
 * @return mixed Any variable or object, either representing one or two.
 */
function toggle($one, $two, $section = 'a')
{
  static $toggle;
  if(!isset($toggle[$section]))
  {
    $toggle[$section] = array();
  }
  
  $toggle[$section] = ($toggle[$section] == $one) ? $two : $one;
  return $toggle[$section];
}

function get_words_count($text)
{
  return myText::getWordsCount($text);
}

/**
 * Hyphenates the text using sfTypography
 * 
 * @param string $text
 * @param string $language
 * @param array $options
 * @return string 
 */
function hyphenate_text($text, $options = array())
{
  if(sfConfig::get('sf_logging_enabled'))
  {
    sfContext::getInstance()->getLogger()->warning('hyphenate_text() is deprecated. Use typography_text() instead');    
  }
  
  return typography_text($text, $options);
}

/**
 * Hyphenates the text using sfTypography
 * 
 * @param string $text
 * @param string $language
 * @param array $options
 * @return string 
 */
function typography_text($text, $options = array())
{
  $options  = _parse_attributes($options);
  
  $language = _get_option($options, 'culture', sfConfig::get('sf_i18n_default_culture'));
  
  // hyphenate text, true by default
  $hyphenate = _get_option($options, 'hyphenate', true);

  if(!isset($options['hyphen']))
  {
    // @see http://www.utf8-chartable.de/unicode-utf8-table.pl?utf8=dec
    $options['hyphen'] = chr(194).chr(173);
  }
  
  // convert options to method names
  // what is this? a joke?
  $options = array_flip(
              array_map('lcfirst',   
                array_map('sfInflector::camelize', array_flip($options)
            )));

  if(sfConfig::get('sf_debug'))
  {
    sfTimerManager::getTimer('{sfTypography} typography_text');
  }
  
  // correct the text before hyphenating?
  $correct = _get_option($options, 'correct', true);
  
  $typography = sfTypography::getInstance($language);
  $typography->setOptions($options);

  // correct the text before hyphenating?
  $wrapWords = _get_option($options, 'wrap_words', true);

  if($hyphenate)
  {
    $text = $typography->hyphenate($text);
  }
  
  if($correct)
  {
    // $text = $typography->correct($text, $options);    
  }
  
  if($wrapWords)
  {
    // $text = $typography->wrapWords($text, $options);
  }
  
  if(sfConfig::get('sf_debug'))
  {
    sfTimerManager::getTimer('{sfTypography} typography_text')->addTime();
  }
  
  return $text;  
}