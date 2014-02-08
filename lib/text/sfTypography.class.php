<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTypography provides utility class for web typography. Based on the work of:
 * Andreas Heigl (Org_Heigl_Hyphenator version 1).
 *
 * @package    Sift
 * @subpackage text
 * @see http://www.smashingmagazine.com/2011/08/15/mind-your-en-and-em-dashes-typographic-etiquette/
 * @see http://kevin.deldycke.com/2007/03/ultimate-regular-expression-for-html-tag-parsing-with-php/
 */
class sfTypography {

  const HYPHENATE_QUALITY_HIGHEST = 9;
  const HYPHENATE_QUALITY_HIGH = 7;
  const HYPHENATE_QUALITY_NORMAL = 5;
  const HYPHENATE_QUALITY_LOW = 3;
  const HYPHENATE_QUALITY_LOWEST = 1;

  /**
   * Options. Valid options are:
   *
   *  - no_hyphenate_string: The string that marks a word not to hyphenate
   *  - hyphen: Default hyphenation-character. Default is Soft-Hyphen-Character (ASCII 173)
   *  - hyphenate_left_min: How many characters need to stay to the left side of a hyphenation. (Default is 2)
   *  - hyphenate_right_min: How many characters need to stay to the right side of a hyphenation.
   *  - hyphenate_quality: The currently set quality for hyphenation
   *  - hyphenate_word_min: How long a word that can be hyphenated needs to be.
   *  - hyphenate_shortest_pattern: The shortest pattern length to use for hyphenating
   *  - hyphenate_longest_pattern: The longest pattern length to use for hyphenating (default is 10).
   *  - custom_hyphen: The String that shall be searched for as a custom hyphen
   *  - customized_marker:  When customizations shall be used, what string shall be prepend to the word that contains customizations.
   *  - mark_cuszomized: Whether to mark Customized Hyphenations or not
   *
   * @var array
   */
  protected $options = array(
    'no_hyphenate_string' => null,
    'hyphen' => null,
    'hyphenate_left_min' => 2,
    'hyphenate_right_min' => 2,
    'hyphenate_quality' => 9,
    'hyphenate_shortest_pattern' => 2,
    'hyphenate_longest_pattern' => 10,
    'custom_hyphen' => '--',
    'customized_marker' => '<!--cm//-->',
    'hyphenate_word_min' => 6,
    'mark_customized' => false,

    // general typography
    'reduce_linebreaks' => true,
    'protect_braced_quotes' => true,

  );

  /**
   * This is the default language to use.
   *
   * @var string $defaultLanguage
   */
  private static $defaultLanguage = 'en';

  /**
   * This property stores an instance of the hyphenator for each language
   *
   * @var array $store
   */
  private static $store = array();

  /**
   * This property defines some spechial Characters for a language that need
   * to be taken into account for the definition of a word.
   *
   * @var string $specialChars
   */
  private $specialChars = '';

  /**
   * This property contains the pattern-array for a specific language
   *
   * @var array|null $pattern
   */
  private $patterns = array();

  /**
   * The special strings to parse as hyphenations
   *
   * @var array $specialStrings
   */
  private $specialStrings = array('-/-', '-');

  /**
   * Last parsed block element
   *
   * @var string
   */
  protected $lastBlockElement = '';

  /**
   * Tags to completly skip when treating text as HTML
   *
   * @var array $skipTags
   */
  private $skipTags = array('head', 'script', 'style', 'code', 'pre');

  /**
   * Text macros to skip when parsing text
   *
   * @var array
   * @see sfTextMacro
   */
  private $skipMacros = array('code');

  /**
   * This is the constructor, that initialises the hyphenator for the given
   * language <var>$language</var>
   *
   * This constructor is  declared private to ensure, that it is only called
   * via the getInstance() method, so we only initialize the stuff only once
   * for each language.
   *
   * @param string $language The language to use for hyphenating
   *
   * @throws Exception
   */
  public function __construct($language = null, $options = array())
  {
    if(!$language)
    {
      $language = self::getDefaultLanguage();
    }

    $lang = array($language);

    $pos = strpos($language, '_');
    if(false !== $pos)
    {
      $lang[] = sfUtf8::sub($language, 0, $pos);
    }

    $found = false;

    foreach($lang as $_language)
    {
      $languageFile = sprintf('%s/i18n/typo/%s.dat',
                        sfConfig::get('sf_sift_data_dir'), $_language);

      if(!is_readable($languageFile))
      {
        continue;
      }

      $found = true;

      $this->language = $_language;

      $data = unserialize(file_get_contents($languageFile));

      // extract to object
      $this->patterns       = $data['patterns'];
      $this->hyphenation    = $data['hyphenation'];
      $this->abbreviations  = $data['abbreviations'];
      $this->conjunctions   = $data['conjunctions'];
      $this->prepositions   = $data['prepositions'];
    }

    if(!$found)
    {
      throw new sfException(sprintf('Data file for language "%s" could not be found.', $language));
    }

    if(null === $this->options['hyphen'])
    {
      $this->options['hyphen'] = chr(194).chr(173);
    }

  }

  /**
   * This method gets the sfTypography for the language <var>$language</var>
   *
   * If no instance exists, it is created and stored.
   *
   * @param string $language The language
   *
   * @return sfTypography sfTypography object
   */
  public static function getInstance($language = null, $options = array())
  {
    if(!$language)
    {
      $language = self::getDefaultLanguage();
    }

    if((count(self::$store) <= 0 ) ||
            (!array_key_exists($language, self::$store) ) ||
            (!is_object(self::$store[$language]) ) ||
            (!self::$store[$language] instanceof sfTypography))
    {
      self::$store[$language] = new self($language, $options);
    }

    return self::$store[$language];
  }

  /**
   * Sets options
   *
   * @param array $options
   * @return sfTypography
   */
  public function setOptions(array $options)
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * Correct the string for common typography issues
   *
   * @param string $string
   * @param boolean $isHtml Is string html?
   * @return String
   */
  public function correct($string)
  {
    // empty?
    if(empty($string))
    {
      return '';
    }

    // standardize newlines to make matching easier
    if(strpos($string, "\r") !== false)
    {
      $string = str_replace(array("\r\n", "\r"), "\n", $string);
    }

    // Reduce line breaks.  If there are more than two consecutive linebreaks
    // we'll compress them down to a maximum of two since there's no benefit to more.
    if($this->options['reduce_linebreaks'])
    {
      $string = preg_replace("/\n\n+/", "\n\n", $string);
    }

    // HTML detection
    $isHtml = $this->isStringHtml($string);

    // HTML comment tags don't conform to patterns of normal tags,
    // so pull them out separately, only if needed
    $html_comments = array();
    if(strpos($string, '<!--') !== false)
    {
      if(preg_match_all("#(<!\-\-.*?\-\->)#s", $string, $matches))
      {
        for($i = 0, $total = count($matches[0]); $i < $total; $i++)
        {
          $html_comments[] = $matches[0][$i];
          $string = str_replace($matches[0][$i], '{@HC' . $i . '}', $string);
        }
      }
    }

    // lets do the dirty work
    if($isHtml)
    {
      // match and yank <pre> tags if they exist.
      $string = preg_replace_callback("#<pre.*?>.*?</pre>#si",
              array($this, 'protectCharacters'), $string);

      // convert quotes within tags to temporary markers.
      $string = preg_replace_callback("#<.+?>#si", array($this, 'protectCharacters'), $string);

      // Do the same with braces if necessary
      if($this->options['protect_braced_quotes'])
      {
        $string = preg_replace_callback("#\{.+?\}#si", array($this, 'protectCharacters'), $string);
      }

      // tags we want the parser to completely ignore when splitting the string.
      $inlineElements = 'a|abbr|acronym|b|bdo|big|br|button|cite|code|del|dfn|em|i|img|ins|input|label|map|kbd|q|samp|select|small|span|strong|sub|sup|textarea|tt|var';

      // Convert "ignore" tags to temporary marker.  The parser splits out the string at every tag
      // it encounters.  Certain inline tags, like image tags, links, span tags, etc. will be
      // adversely affected if they are split out so we'll convert the opening bracket < temporarily to: {@TAG}
      $string = preg_replace("#<(/*)(".$inlineElements.")([ >])#i", "{@TAG}\\1\\2\\3", $string);

      // Split the string at every tag.  This expression creates an array with this prototype:
      //
      //  [array]
      //  {
      //    [0] = <opening tag>
      //    [1] = Content...
      //    [2] = <closing tag>
      //    Etc...
      //  }
      $chunks = preg_split('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

      // Build our finalized string.
      // We cycle through the array, skipping tags, and processing the contained text
      $string = '';
      $process = true;
      $current_chunk = 0;
      $total_chunks = count($chunks);

      $blockElements = 'address|blockquote|div|dl|fieldset|form|h\d|hr|noscript|object|ol|p|pre|script|table|ul';
      $skipElements   = 'p|pre|ol|ul|dl|object|table|h\d';

      foreach($chunks as $chunk)
      {
        $current_chunk++;
        // Are we dealing with a tag? If so, we'll skip the processing for this cycle.
        // Well also set the "process" flag which allows us to skip <pre> tags and a few other things.
        if(preg_match("#<(/*)(".$blockElements.").*?>#", $chunk, $match))
        {
          if(preg_match("#".$skipElements."#", $match[2]))
          {
            $process = ($match[1] == '/') ? true : false;
          }

          if($match[1] == '')
          {
            $this->lastBlockElement = $match[2];
          }

          $string .= $chunk;
          continue;
        }

        if($process == false)
        {
          $string .= $chunk;
          continue;
        }

        // Force a newline to make sure end tags get processed by _format_newlines()
        if($current_chunk == $total_chunks)
        {
          $chunk .= "\n";
        }

        // Convert Newlines into <p> and <br /> tags
        $string .= $this->formatNewlines($chunk);
      }

      // No opening block level tag? Add it if needed.
      if(!preg_match("/^\s*<(?:" . $blockElements . ")/i", $string))
      {
        $string = preg_replace("/^(.*?)<(" . $blockElements . ")/i", '<p>$1</p><$2', $string);
      }

    }

    $string = $this->formatCharacters($string, $isHtml);


    // restore HTML comments
    for($i = 0, $total = count($html_comments); $i < $total; $i++)
    {
      // remove surrounding paragraph tags, but only if there's an opening paragraph tag
      // otherwise HTML comments at the ends of paragraphs will have the closing tag removed
      // if '<p>{@HC1}' then replace <p>{@HC1}</p> with the comment, else replace only {@HC1} with the comment
      $string = preg_replace('#(?(?=<p>\{@HC' . $i . '\})<p>\{@HC' . $i . '\}(\s*</p>)|\{@HC' . $i . '\})#s', $html_comments[$i], $string);
    }

    if($isHtml)
    {
      // Final clean up
      $table = array(
        // If the user submitted their own paragraph tags within the text
        // we will retain them instead of using our tags.
        '/(<p[^>*?]>)<p>/'  => '$1',

        // Reduce multiple instances of opening/closing paragraph tags to a single one
        '#(</p>)+#'      => '</p>',
        '/(<p>\W*<p>)+/'  => '<p>',

        // Clean up stray paragraph tags that appear before block level elements
        '#<p></p><('.$blockElements.')#'  => '<$1',

        // Clean up stray non-breaking spaces preceeding block elements
        '#(&nbsp;\s*)+<('.$blockElements.')#'  => '  <$2',

        // Replace the temporary markers we added earlier
        '/\{@TAG\}/'    => '<',
        '/\{@DQ\}/'      => '"',
        '/\{@SQ\}/'      => "'",
        '/\{@DD\}/'      => '--',
        '/\{@NBS\}/'    => '  ',

        // An unintended consequence of the _format_newlines function is that
        // some of the newlines get truncated, resulting in <p> tags
        // starting immediately after <block> tags on the same line.
        // This forces a newline after such occurrences, which looks much nicer.
        "/><p>\n/"      => ">\n<p>",

        // Similarly, there might be cases where a closing </block> will follow
        // a closing </p> tag, so we'll correct it by adding a newline in between
        "#</p></#"      => "</p>\n</"
      );

      // Do we need to reduce empty lines?
      if($this->options['reduce_linebreaks'])
      {
        $table['#<p>\n*</p>#'] = '';
      }
      else
      {
        // If we have empty paragraph tags we add a non-breaking space
        // otherwise most browsers won't treat them as true paragraphs
        $table['#<p></p>#'] = '<p>&nbsp;</p>';
      }

      $string = preg_replace(array_keys($table), $table, $string);
    }
    return trim($string);
  }

  /**
   * Corrects the string for typography mistakes and hyphenates the words
   *
   * @param string $string
   * @return string
   */
  public function correctAndHyphenate($string)
  {
    return $this->hyphenate($this->correct($string));
  }

  /**
   * This is the static way of correcting a string.
   *
   * This method gets the appropriate sfTypography-object and calls the method
   * correct() on it.
   *
   * @param string $string  The String
   * @param array $options The Options to use
   * @param array $applyFilters What filters to apply? Default is all filters
   *
   * @return string The hyphenated string
   */
  public static function correctStatic($string,
          $options = null, $apllyFilters = null)
  {
    if(null === $options)
    {
      $options = array();
    }

    if(null === $apllyFilters)
    {
      $apllyFilters = array();
    }

    if(!isset($options ['language']))
    {
      $options['language'] = self::getDefaultLanguage();
    }

    // Get the instance for the language.
    $typo = self::getInstance($options['language']);

    unset($options['language']);
    $typo->setOptions($options);

    return $typo->correct($string, $apllyFilters);
  }

  /**
   * Set the default Language
   *
   * @param string $language The language to set.
   *
   * @return void
   */
  public static function setDefaultLanguage($language)
  {
    self::$defaultLanguage = $language;
  }

  /**
   * Get the default language
   *
   * @return string
   */
  public static function getDefaultLanguage()
  {
    return self::$defaultLanguage;
  }

  /**
   * This method does the actual hyphenation.
   *
   * The given <var>$string</var> is splitted into chunks (i.e. Words) at
   * every blank.
   *
   * After that every chunk is hyphenated and the array of chunks is merged
   * into a single string using blanks again.
   *
   * This method does not take into account other word-delimiters than blanks
   * (eg. returns or tabstops) and it will fail with texts containing markup
   * in any way.
   *
   * @param string $string The string to hyphenate
   * @return string The hyphenated string
   */
  public function hyphenate($string)
  {
    $html = $this->isStringHtml($string);

    if($html)
    {
      $array = preg_split('/([\s<>])/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }
    else
    {
      $array = preg_split('/([\s])/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    $size = count($array);

    // HTML
    if($html)
    {
      $inTag    = $inSkip = false;
      $skip     = $this->getSkipTags();
      $skipEnd  = $this->getSkipTagsEnd();

      for($i = 0; $i < $size; $i++)
      {
        if(!$inTag && sfUtf8::sub($array[$i], 0, 1) == '<')
        {
          $inTag = true;
        }

        if(!$inSkip && $i + 2 < $size && in_array($array[$i].$array[$i + 1], $skip))
        {
          $inSkip = true;
        }

        if(!$inTag && !$inSkip && !((sfUtf8::sub($array[$i], 0, 1) == '&'
                && sfUtf8::sub($array[$i], -1, 1) == ';')))
        {
          $array[$i] = $this->hyphenateWord($array[$i]);
        }

        if(sfUtf8::sub($array[$i], -1, 1) == '>')
        {
          $inTag = false;
        }

        if($i + 2 < $size && in_array($array[$i] . $array[$i + 1] . $array[$i + 2], $skipEnd))
        {
          $inSkip = false;
        }
      }
    }
    // Plain text
    else
    {
      for($i = 0; $i < $size; $i++)
      {
        $array[$i] = $this->hyphenateWord($array[$i]);
      }
    }

    $hyphenatedString = implode('', $array);

    // Return the hyphenated string.
    return $hyphenatedString;
  }

  /**
   * This method hyphenates a single word
   *
   * @param string $word The Word to hyphenate
   *
   * @return string the hyphenated word
   */
  public function hyphenateWord($word)
  {
    // If the Word is empty, return an empty string.
    if('' === trim($word))
    {
      return $word;
    }

    // Check whether the word shall be hyphenated.
    $result = $this->isNotToBeHyphenated($word);
    if(false !== $result)
    {
      return $result;
    }

    // If the length of the word is smaller than the minimum word-size,
    // return the word.
    if($this->options['hyphenate_word_min'] > sfUtf8::len($word))
    {
      return $word;
    }

    // Character 173 is the unicode char 'Soft Hyphen' wich may  not be
    // visible in some editors!
    // HTML-Entity for soft hyphenation is &shy;!
    if(false !== strpos($word, '&shy;'))
    {
      return str_replace('&shy;', $this->options['hyphen'], $word);
    }

    // Replace a custom hyphenate-string with the hyphen.
    $result = $this->replaceCustomHyphen($word);
    if(false !== $result)
    {
      return $result;
    }

    // If the word already contains a hyphen-character, we assume it is
    // already hyphenated and return the word 'as is'.
    if(false !== strpos($word, $this->options['hyphen']))
    {
      return $word;
    }

    // Hyphenate words containing special strings for further processing, so
    // put a zerowidthspace after it and hyphenate the parts separated by
    // the special string.
    $result = $this->handleSpecialStrings($word);
    if(false !== $result)
    {
      return $result;
    }

    // Is it a pre-hyphenated word?
    // we have this block here twice (once again in doHyphenateWord)
    if(isset($this->hyphenation[sfUtf8::lower($word)]))
    {
      // split word to individual characters
      $chars            = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
      $replaceWith      = $this->hyphenation[sfUtf8::lower($word)];
      // split replacement word into characters
      $replaceWithChars = preg_split('//u', $replaceWith, -1, PREG_SPLIT_NO_EMPTY);
      for($i = 0, $count = count($replaceWithChars); $i < $count; $i++)
      {
        if($replaceWithChars[$i] == '-')
        {
          array_splice($chars, $i, 0, '-');
        }
      }
      // put the word back
      return str_replace('-', $this->options['hyphen'], join('', $chars));
    }

    return $this->doHyphenateWord($word);
  }

  /**
   * Hyphenate a single word
   *
   * @param string $word The word to hyphenate
   *
   * @return string The hyphenated word
   */
  private function doHyphenateWord($word)
  {
    $prepend = '';
    $append  = '';

    $specials = '\.\:\-\,\;\!\?\/\\\(\)\[\]\{\}\"\'\+\*\#\Â§\$\%\&\=\@';
    // If a special character occurs in the middle of the word, simply
    // return the word AS IS as the word can not really be hyphenated
    // automaticaly.
    if(preg_match('/[^' . $specials . '][' . $specials . '][^' . $specials . ']/u', $word))
    {
      return $word;
    }

    // If one or more special characters appear before or after a word
    // we take the word in between and hyphenate that asn append and prepend
    // the special characters later on.
    if(preg_match('/([' . $specials . ']*)([^' . $specials . ']+)([' . $specials . ']*)/u', $word, $result))
    {
      $prepend = $result[1];
      $word = $result[2];
      $append = $result[3];
    }

    // Is it a pre-hyphenated word?
    if(isset($this->hyphenation[sfUtf8::lower($word)]))
    {
      // split word to individual characters
      $chars            = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
      $replaceWith      = $this->hyphenation[sfUtf8::lower($word)];
      // split replacement word into characters
      $replaceWithChars = preg_split('//u', $replaceWith, -1, PREG_SPLIT_NO_EMPTY);
      for($i = 0, $count = count($replaceWithChars); $i < $count; $i++)
      {
        if($replaceWithChars[$i] == '-')
        {
          array_splice($chars, $i, 0, '-');
        }
      }
      // put the word back
      return $prepend . str_replace('-', $this->options['hyphen'], join('', $chars)) . $append;
    }

    $result = array();

    $positions = $this->getHyphenationPositions($word);

    $wl = sfUtf8::len($word);
    $lastOne = 0;

    for($i = 1; $i < $wl; $i++)
    {
      // If the integer on position $i is higher than 0 and is odd,
      // we can hyphenate at that position if the integer is lower or
      // equal than the set quality-level.
      // Additionaly we check whether the left and right margins are met.
      if(( 0 !== $positions[$i] ) &&
              ( 1 === ( $positions[$i] % 2 ) ) &&
              ( $positions[$i] <= $this->options['hyphenate_quality'] ) &&
              ( $i >= $this->options['hyphenate_left_min'] ) &&
              ( $i <= ( sfUtf8::len($word) - $this->options['hyphenate_right_min'] ) ))
      {
        // Begin IF.
        $sylable = sfUtf8::sub($word, $lastOne, $i - $lastOne);

        $lastOne = $i;
        $result[] = $sylable;
      }
    }

    $result[] = sfUtf8::sub($word, $lastOne);
    $return = $prepend . trim(implode($this->options['hyphen'], $result)) . $append;

    return $return;
  }

  /**
   * Get the positions, where a hyphenation might occur and where not.
   *
   * @param string $word The word to hyphenate
   *
   * @return array The numerical positions-array
   */
  private function getHyphenationPositions($word)
  {
    $positions = array();
    $w = '_' . sfUtf8::lower($word) . '_';
    $wl = sfUtf8::len($w);
    // Initialize an array of length of the word with 0-values.
    for($i = 0; $i < $wl; $i++)
    {
      $positions[$i] = 0;
    }
    for($s = 0; $s < $wl - 1; $s++)
    {
      $maxl = $wl - $s;
      $window = sfUtf8::sub($w, $s);
      for($l = $this->options['hyphenate_shortest_pattern'];
      $l <= $maxl && $l <= $this->options['hyphenate_longest_pattern']; $l++)
      {
        $part = sfUtf8::sub($window, 0, $l);
        $values = null;
        if(isset($this->patterns[$part]))
        {
          // We found a pattern for this part.
          $values = (string) $this->patterns [$part];
          $i = $s;
          $v = null;
          $m = sfUtf8::len($values);
          $corrector = 1;
          for($p = 0; $p < $m; $p++)
          {
            $v = sfUtf8::sub($values, $p, 1);
            $arrayKey = $i + $p - $corrector;
            if(array_key_exists($arrayKey, $positions) && ( ( (int) $v > $positions[$arrayKey] ) ))
            {
              $positions[$arrayKey] = (int) $v;
            }
            if($v > 0)
            {
              $corrector++;
            }
          }
        }
      }
    }
    return $positions;
  }

  /**
   * Check whether this string shall not be hyphenated
   *
   * If so, replace a string that marks strings not to be hyphenated with an
   * empty string. Also replace all custom hyphenations, as the word shall
   * not be hyphenated.
   * Finaly return the word 'as is'.
   *
   * If the word can be hyphenated, return false
   *
   * @param string $word The word to be hyphenated
   *
   * @return string|false
   */
  private function isNotToBeHyphenated($word)
  {
    if((null === $this->options['no_hyphenate_string']) ||
            (0 !== strpos($word, $this->options['no_hyphenate_string'])))
    {
      return false;
    }

    $string = str_replace($this->options['no_hyphenate_string'], '', $word);
    $string = str_replace($this->options['custom_hyphen'], '', $string);
    if(null !== $this->options['customized_marker'] && true === $this->options['mark_customized'])
    {
      $string = $this->getCustomizationMarker() . $string;
    }
    return $string;
  }

  /**
   * Replace a custom hyphen
   *
   * @param string $word The word to parse
   *
   * @return string|false
   */
  private function replaceCustomHyphen($word)
  {
    if((null === $this->options['custom_hyphen']) ||
            (false === strpos($word, $this->options['custom_hyphen'])))
    {
      return false;
    }

    $string = str_replace($this->options['custom_hyphen'], $this->options['hyphen'], $word);
    if(null !== $this->options['customized_marker'] && true === $this->options['mark_customized'])
    {
      $string = $this->getCustomizationMarker() . $string;
    }
    return $string;
  }

  /**
   * Handle special strings
   *
   * Hyphenate words containing special strings for further processing, so
   * put a zerowidthspace after it and hyphenate the parts separated by
   * the special string.
   *
   * @param string $word The Word to hyphenate
   *
   * @return string|false
   */
  public function handleSpecialStrings($word)
  {
    foreach($this->specialStrings as $specialString)
    {
      if(false === strpos($word, $specialString))
      {
        continue;
      }
      // Word contains a special string so put a zerowidthspace after
      // it and hyphenate the parts separated with the special string.
      $parts = explode($specialString, $word);
      $counter = count($parts);
      for($i = 0; $i < $counter; $i++)
      {
        $parts[$i] = $this->doHyphenateWord($parts[$i]);
      }
      return implode($specialString, $parts);
    }
    return false;
  }

  /**
   * Set the special strings
   *
   * These are strings that can be used for further parsing of the text.
   *
   * For instance a string to be replaced with a soft return or any other
   * symbol your application needs.
   *
   * @param array $specialStrings An array of special strings.
   *
   * @return sfTypography
   */
  public function setSpecialStrings($specialStrings = array())
  {
    $this->specialStrings = (array) $specialStrings;
    return $this;
  }

  /**
   * This method sets the Hyphenation-Character.
   *
   * @param string $char The Hyphenation Character
   *
   * @return sfTypography Provides fluent Interface
   */
  public function setHyphen($char)
  {
    $this->options['hyphen'] = (string) $char;
    return $this;
  }

  /**
   * Get the hyphenation character
   *
   * @return string
   */
  public function getHyphen()
  {
    return $this->options['hyphen'];
  }

  /**
   * This method sets the minimum Characters, that have to stay to the left of
   * a hyphenation
   *
   * @param int $count The left minimum
   *
   * @return sfTypography Provides fluent Interface
   */
  public function setHyphenateLeftMin($count)
  {
    $this->options['hyphenate_left_min'] = (int) $count;
    return $this;
  }

  /**
   * This method sets the minimum Characters, that have to stay to the right of
   * a hyphenation
   *
   * @param int $count The minimmum characters
   *
   * @return sfTypography Provides fluent Interface
   */
  public function setHyphenateRightMin($count)
  {
    $this->options['hyphenate_right_min'] = (int) $count;
    return $this;
  }

  /**
   * This method sets the minimum Characters a word has to have before being
   * hyphenated
   *
   * @param int $count The minimmum characters
   *
   * @return sfTypography Provides fluent Interface
   */
  public function setHyphenateWordMin($count)
  {
    $this->options['hyphenate_word_min'] = (int) $count;
    return $this;
  }

  /**
   * This method sets the special Characters for a specified language
   *
   * @param string $chars The spechail characters
   *
   * @return sfTypography Provides fluent Interface
   */
  public function setSpecialChars($chars)
  {
    $this->specialChars = $chars;
    return $this;
  }

  /**
   * Set the quality that the Hyphenation needs to have minimum
   *
   * The lower the number, the better is the quality
   *
   * @param int $quality The quality-level to set
   *
   * @return sfTypography
   */
  public function setHyphenateQuality($quality = self::HYPHENATE_QUALITY_NORMAL)
  {
    if(is_string($quality))
    {
      $quality = constant(sprintf('self::HYPHENATE_QUALITY_%s',
                  strtoupper($quality)));
    }
    $this->options['hyphenate_quality'] = (int) $quality;
    return $this;
  }

  /**
   * Set a string that will be replaced with the soft-hyphen before
   * Hyphenation actualy starts.
   *
   * If this string is found in a word no hyphenation will be done except for
   * the place where the custom hyphen has been found
   *
   * @param string $customHyphen The Custom Hyphen to set
   *
   * @return sfTypography
   */
  public function setCustomHyphen($customHyphen = null)
  {
    $this->options['custom_hyphen'] = $customHyphen;
    return $this;
  }

  /**
   * Get the marker for custom hyphenations
   *
   * @return string
   */
  public function getCustomHyphen()
  {
    return (string) $this->options['custom_hyphen'];
  }

  /**
   * Set a string that marks a words not to hyphenate
   *
   * @param string $marker THe Marker that marks a word
   *
   * @return sfTypography
   */
  public function setNoHyphenateMarker($marker = null)
  {
    $this->options['no_hyphenate_string'] = $marker;

    return $this;
  }

  /**
   * Get the marker for Words not to hyphenate
   *
   * @return string
   */
  public function getNoHyphenMarker()
  {
    return (string) $this->options['no_hyphenate_string'];
  }

  /**
   * Set and retrieve whether or not to mark custom hyphenations
   *
   * This method always returns the current setting, so you can set AND
   * retrieve the value with this method.
   *
   * @param null|booelan $mark Whether or not to mark
   *
   * @return boolean
   */
  public function markCustomization($mark = null)
  {
    if(null !== $mark)
    {
      $this->options['mark_customized'] = (bool) $mark;
    }
    return (bool) $this->options['mark_customized'];
  }

  /**
   * Set the string that shall be prepend to a customized word.
   *
   * @param string $marker The Marker to set
   * @return sfTypography
   */
  public function setCustomizationMarker($marker)
  {
    $this->options['customized_marker'] = (string) $marker;
    return $this;
  }

  /**
   * Set list of tags to completly skip when treating text as HTML.
   *
   * @param array $tags
   * @return sfTypography
   */
  public function setSkipTags(array $tags)
  {
    $this->skipTags = $tags;
    return $this;
  }

  /**
   * Returns an array of tags to be skipped
   *
   * @return array
   */
  public function getSkipTags()
  {
    $array = array();
    foreach($this->skipTags as $t)
    {
      $array[] = '<' . $t . '>';
      $array[] = '<' . $t;
    }
    return $array;
  }

  /**
   * Returns an array of end tags to be skipped: </tag>
   * @return array
   */
  public function getSkipTagsEnd()
  {
    $array = array();
    foreach($this->skipTags as $t)
    {
      $array[] = '</' . $t . '>';
    }
    return $array;
  }

  /**
   * Get the string that shall be prepend to a customized word.
   *
   * @return string
   */
  public function getCustomizationMarker()
  {
    return (string) $this->options['customized_marker'];
  }

  /**
   * Format Characters
   *
   * This function mainly converts double and single quotes
   * to curly entities, but it also converts em-dashes,
   * double spaces, and ampersands
   *
   * @access  public
   * @param  string $string
   * @param boolean $isHtml Is string HTML?
   * @return  string
   */
  public function formatCharacters($string, $isHtml = false)
  {
      // build language specific replacements

      $table = array(
          // nested smart quotes, opening and closing
          // note that rules for grammar (English) allow only for two levels deep
          // and that single quotes are _supposed_ to always be on the outside
          // but we'll accommodate both
          // Note that in all cases, whitespace is the primary determining factor
          // on which direction to curl, with non-word characters like punctuation
          // being a secondary factor only after whitespace is addressed.
          '/\'"(\s|$)/u' => '&#8217;&#8221;$1',
          '/(^|\s|<p>)\'"/u' => '$1&#8216;&#8220;',
          '/\'"(\W)/u' => '&#8217;&#8221;$1',
          '/(\W)\'"/u' => '$1&#8216;&#8220;',
          '/"\'(\s|$)/u' => '&#8221;&#8217;$1',
          '/(^|\s|<p>)"\'/u' => '$1&#8220;&#8216;',
          '/"\'(\W)/u' => '&#8221;&#8217;$1',
          '/(\W)"\'/u' => '$1&#8220;&#8216;',
          // single quote smart quotes
          '/\'(\s|$)/u' => '&#8217;$1',
          '/(^|\s|<p>)\'/u' => '$1&#8216;',
          '/\'(\W)/u' => '&#8217;$1',
          '/(\W)\'/u' => '$1&#8216;',
          // double quote smart quotes
          '/"(\s|$)/u' => '&#8221;$1',
          // WHAT IS THIS?
          '/(^|\s|<p>)"/u' => '$1&#8220;',
          '/"(\W)/u' => '&#8221;$1',
          '/(\W)"/u' => '$1&#8220;',
          // apostrophes
          "/(\w)'(\w)/u" => '$1&#8217;$2',
          // Em dash and ellipses dots
          '/\s?\-\-\s?/' => '&#8212;',
          // více než tři tečky za sebou zredukuje na tři
          '#\.{4,}#' => '...',

          // když po ,.;:?! rovnou následuje písmeno, pak za ní udělá mezeru
          // '#([,\.;\:\?\!])([a-zA-Záčďéíňóřšťúůýž]|\{@TAG\})#' => '\\1 \\2',

          '/(\w)\.{3}/u' => '$1&hellip;',
          // double space after sentences
          // '/(\W)  /' => '$1&nbsp; ',
          // ampersands, if not a character entity
          '/&(?!#?[a-zA-Z0-9]{2,};)/u' => '&amp;',

          // odstraní mezery před interpunkčními znaménky
          '# ([,\.;\:\?\!])#u' => '\\1',

          // odstraní opakované vykřičníky nebo otazníky
          '#\!{2,}#u' => '!',
          '#\?{2,}#u' => '?',
      );

    // $table['#\.{3}#'] = '&hellip;';

    return preg_replace(array_keys($table), $table, $string);
  }

  /**
   * Protect Characters
   *
   * Protects special characters from being formatted later
   * We don't want quotes converted within tags so we'll temporarily convert them to {@DQ} and {@SQ}
   * and we don't want double dashes converted to emdash entities, so they are marked with {@DD}
   * likewise double spaces are converted to {@NBS} to prevent entity conversion
   *
   * @access  public
   * @param  array $match
   * @return  string
   */
  public function protectCharacters($match)
  {
    return str_replace(array("'", '"', '--', '  '),
            array('{@SQ}', '{@DQ}', '{@DD}', '{@NBS}'), $match[0]);
  }

  /**
   * Format Newlines
   *
   * Converts newline characters into either <p> tags or <br />
   *
   * @access  public
   * @param  string
   * @return  string
   */
  public function formatNewlines($string)
  {
    if(empty($string))
    {
      return '';
    }

    if(strpos($string, "\n") === false
            && !in_array($this->lastBlockElement, array('blockquote')))
    {
      return $string;
    }

    return $string;

    // Convert two consecutive newlines to paragraphs
    $string = str_replace("\n\n", "</p>\n\n<p>", $string);

    // Convert single spaces to <br /> tags
    $string = preg_replace("/([^\n])(\n)([^\n])/", "\\1<br />\\2\\3", $string);

    // Wrap the whole enchilada in enclosing paragraphs
    if($string != "\n")
    {
      // We trim off the right-side new line so that the closing </p> tag
      // will be positioned immediately following the string, matching
      // the behavior of the opening <p> tag
      $string = '<p>' . rtrim($string) . '</p>';
    }

    // Remove empty paragraphs if they are on the first line, as this
    // is a potential unintended consequence of the previous code
    $string = preg_replace("/<p><\/p>(.*)/", "\\1", $string, 1);

    return $string;
  }

  /**
   * Does string contain HTML code?
   *
   * @param string $string
   * @return boolean
   */
  protected function isStringHtml($string)
  {
    // does string contain html?
    return preg_match("/([\<])([^\>]{1,})*([\>])/i", $string);
  }

  /**
   * Puts non-breakable space after one-letter Czech prepositions like 'k', 's', 'v' or 'z'.
   *
   * @param type $string String to be corrected
   * @param array $options Array of options
   * @return string
   */
  public function wrapWords($string, $options = array())
  {
    // create a list of matches
    $wordMatches = array();
    foreach(array('abbreviations', 'conjunctions', 'prepositions') as $a)
    {
      $wordMatches[] = join('|', $this->$a);
    }

    $matches = array();
    $replacements = array();

    if(count($wordMatches))
    {
      // matches
      $matches['words'] = sprintf('@(^|;| |&nbsp;|\(|\n)(%s) @iu', join('|', $wordMatches));
      $replacements['words'] = '$1$2&nbsp;';
    }

    if(isset($options['numbers']) && $options['numbers']
        || !isset($options['numbers']))
    {
      $matches['numbers'] = '@(\d) (\d)@iu';
      $replacements['numbers'] = '$1&nbsp;$2';
    }

    if(!count($matches))
    {
      return $string;
    }

    // we are dealing with HTML code
    if($this->isStringHtml($string))
    {
      $array = preg_split('/(<.*>|\[.*\])/Us', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

      $ignoreTagStack   = array();
      $ignoreMacroStack = array();
      $skipTagsExpr   = count($this->skipTags) ? '(' . implode('|', $this->skipTags) . ')' : false;
      $skipMacrosExpr = count($this->skipMacros) ? '(' . implode('|', $this->skipMacros) . ')' : false;

      for($i = 0, $stop = count($array); $i < $stop; $i++)
      {
        if(!empty($array[$i]) && '<' != $array[$i]{0} && '[' != $array[$i]{0}
          && ($skipMacrosExpr && empty($ignoreTagStack))
          && ($skipMacrosExpr && empty($ignoreMacroStack)))
        { // If it's not a tag
          $array[$i] = preg_replace($matches, $replacements, $array[$i]);
        }
        else
        {
          $this->pushAndPopElement($array[$i], $ignoreTagStack, $skipTagsExpr, '<', '>');
          $this->pushAndPopElement($array[$i], $ignoreMacroStack, $skipMacrosExpr, '[', ']');
        }
      }
      return join('', $array);
    }
    else
    {
      return preg_replace($matches, $replacements, $string);
    }
  }

   /**
    * Search for disabled element tags. Push element to stack on tag open and pop
    * on tag close. Assumes first character of $text is tag opening.
    *
    * This has been taken from Wordpress _wptexturize_pushpop_element() function.
    *
    * @param string $text Text to check. First character is assumed to be $opening
    * @param array $stack Array used as stack of opened tag elements
    * @param string $disabled_elements Tags to match against formatted as regexp sub-expression
    * @param string $opening Tag opening character, assumed to be 1 character long
    * @param string $opening Tag closing  character
    * @return void
    */
  private function pushAndPopElement($text, &$stack, $disabled_elements, $opening = '<', $closing = '>')
  {
    // Check if it is a closing tag -- otherwise assume opening tag
    if(strncmp($opening . '/', $text, 2) && $disabled_elements)
    {
      // Opening? Check $text+1 against disabled elements
      if(preg_match('/^' . $disabled_elements . '\b/', substr($text, 1), $matches))
      {
        /*
         * This disables fixing until we find a closing tag of our type
         * (e.g. <pre>) even if there was invalid nesting before that
         *
         * Example: in the case <pre>sadsadasd</code>"baba"</pre>
         *          "baba" won't be texturize
         */
        array_push($stack, $matches[1]);
      }
    }
    else
    {
      // Closing? Check $text+2 against disabled elements
      $c = preg_quote($closing, '/');
      if(preg_match('/^' . $disabled_elements . $c . '/', substr($text, 2), $matches))
      {
        $last = array_pop($stack);

        // Make sure it matches the opening tag
        if($last != $matches[1])
          array_push($stack, $last);
      }
    }
  }

}
