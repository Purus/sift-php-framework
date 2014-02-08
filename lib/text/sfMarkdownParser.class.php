<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMarkdownParser - parses markdown syntax to HTML.
 *
 * Markdown syntax copyrights:
 *
 * PHP Markdown & Extra Copyright (c) 2004-2009 Michel Fortin
 * <http://michelf.com/> All rights reserved.
 *
 * Copyright (c) 2003-2006 John Gruber <http://daringfireball.net/>
 *
 * @package    Sift
 * @subpackage text
 */
class sfMarkdownParser {

  protected static $xhtml = true;

  /**
   * Array of options
   *
   * @var array
   */
  protected $options = array(
    'no_entities' => false,
    'tab_width' => 4,
    'no_markup' => false, // Enable to disallow markup or entities.
    'charset' => 'utf-8'
  );

  protected $documentGamut = array(
    'stripLinkDefinitions' => 20,
    'runBasicBlockGamut' => 30,
    'doFencedCodeBlocks' => 5,
    // 'stripFootnotes'     => 15, // TODO from MarkdownExtra
    // 'stripAbbreviations' => 25, // TODO from MarkdownExtra
    // 'appendFootnotes'    => 50, // TODO from MarkdownExtra
  );

  /**
   * Transformations that occur *within* block-level
   * These are all the tags like paragraphs, headers, and list items
   *
   * @var array
   */
  protected $spanGamut = array(
    // Process character escapes, code spans, and inline HTML
    // in one shot.
    "parseSpan" => -30,
    // Process anchor and image tags. Images must come first,
    // because ![foo][f] looks like an anchor.
    "doImages" => 10,
    "doAnchors" => 20,
    // Make links out of things like `<http://example.com/>`
    // Must come after doAnchors, because you can use < and >
    // delimiters in inline links like [this](<url>).
    "doAutoLinks" => 30,
    "encodeAmpsAndAngles" => 40,
    "doItalicsAndBold" => 50,
    "doHardBreaks" => 60,
    // "doFootnotes"        => 5,  // TODO from MarkdownExtra
    // "doAbbreviations"    => 70, // TODO from MarkdownExtra
  );

  /**
   * Transformations that form block-level
   * tags like paragraphs, headers, and list items.
   *
   * @var array
   */
  protected $blockGamut = array(
    "doHeaders" => 10,
    "doHorizontalRules" => 20,
    "doLists" => 40,
    "doCodeBlocks" => 50,
    'doBlockQuotes' => 60,
    "doFencedCodeBlocks" => 5,
    // "doTables"           => 15,  // TODO from MarkdownExtra
    // "doDefLists"         => 45,  // TODO from MarkdownExtra
  );

  /**
   *
   * @var integer
   */
  protected $listLevel = 0;

  protected $em_relist = array(
      '' => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![\.,:;]\s)',
      '*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
      '_' => '(?<=\S|^)(?<!_)_(?!_)',
  );

  protected $strong_relist = array(
      '' => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![\.,:;]\s)',
      '**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
      '__' => '(?<=\S|^)(?<!_)__(?!_)',
  );

  protected $em_strong_relist = array(
      '' => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![\.,:;]\s)',
      '***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
      '___' => '(?<=\S|^)(?<!_)___(?!_)',
  );

  protected $em_strong_prepared_relist = array();

  /**
   *
   * @var integer
   */
  protected $nestedBracketsDepth = 6;

  /**
   * Regex to match balanced [brackets].
   * Needed to insert a maximum bracked depth while converting to PHP.
   *
   * @var string
   */
  protected $nestedBracketsRe;
  protected $nestedUrlParenthesisDepth = 4;
  protected $nestedUrlParenthesisRe;

  /**
   * Table of hash values for escaped characters
   * @var string
   */
  protected $escapeChars = '\`*_{}[]()>#+-.!';
  protected $escapeCharsRe;

  /**
   * Predefined urls for reference links and images.
   * @var array
   */
  protected $predefinedUrls = array();

  /**
   * Predefined titles for reference links and images.
   *
   * @var array
   */
  protected $predefinedTitles = array();

  /**
   * Internal hashes used during transformation.
   *
   * @var array
   */
  protected $urls = array();
  protected $titles = array();
  protected $html_hashes = array();

  /**
   * Status flag to avoid invalid nesting.
   *
   * @var boolean
   */
  protected $in_anchor = false;

  /**
   * Instances holder
   *
   * @var array
   */
  protected static $instances = array();

  /**
   * Constructs the parser
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    // merge options
    $this->options = array_merge($this->options, $options);

    $this->prepareItalicsAndBold();

    $this->nestedBracketsRe =
            str_repeat('(?>[^\[\]]+|\[', $this->nestedBracketsDepth) .
            str_repeat('\])*', $this->nestedBracketsDepth);

    $this->nestedUrlParenthesisRe =
            str_repeat('(?>[^()\s]+|\(', $this->nestedUrlParenthesisDepth) .
            str_repeat('(?>\)))*', $this->nestedUrlParenthesisDepth);

    $this->escapeCharsRe = '[' . preg_quote($this->escapeChars) . ']';

    // Sort document, block, and span gamut in ascendent priority order.
    asort($this->documentGamut);
    asort($this->blockGamut);
    asort($this->spanGamut);
  }

  /**
   * Returns singleton instance of the class
   *
   * @param array $options Array of options for the parser
   * @return sfMarkdownParser
   */
  public static function getInstance($options = array())
  {
    $key = md5(serialize($options));
    if(!isset(self::$instances[$key]))
    {
      self::$instances[$key] = new self($options);
    }
    return self::$instances[$key];
  }

  /**
   * Called before the transformation process starts to setup parser states.
   *
   */
  protected function setup()
  {
    $this->urls = $this->predefinedUrls;
    $this->titles = $this->predefinedTitles;
    $this->html_hashes = array();
    $this->in_anchor = false;
  }

  /**
   * Called after the transformation process to clear any variable
   * which may be taking up memory unnecessarly.
   *
   */
  protected function teardown()
  {
    $this->urls = array();
    $this->titles = array();
    $this->html_hashes = array();
  }

  /**
   * Transforms the text to HTML
   *
   * @param string $text
   * @return string
   */
  public function transform($text)
  {
    if(empty($text))
    {
      return $text;
    }

    $this->setup();

    // Remove UTF-8 BOM and marker character in input, if present.
    $text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

    // Standardize line endings:
    //   DOS to Unix and Mac to Unix
    $text = preg_replace('{\r\n?}', "\n", $text);

    // Make sure $text ends with a couple of newlines:
    $text .= "\n\n";

    // Convert all tabs to spaces.
    $text = $this->detab($text);

    // Turn block-level HTML blocks into hash entries
    $text = $this->hashHTMLBlocks($text);

    // Strip any lines consisting only of spaces and tabs.
    // This makes subsequent regexen easier to write, because we can
    // match consecutive blank lines with /\n+/ instead of something
    // contorted like /[ ]*\n+/ .
    $text = preg_replace('/^[ ]+$/m', '', $text);

    // Run document gamut methods.
    foreach($this->documentGamut as $method => $priority)
    {
      if(!is_callable(array($this, $method)))
      {
        throw new Exception(sprintf('Invalid document gamut "%s".', $method));
      }
      $text = $this->$method($text);
    }

    $this->teardown();

    return $text . "\n";
  }

  /**
   * Strips link definitions from text, stores the URLs and titles in
   * hash references.
   *
   * @param string $text
   * @return stirng
   */
  protected function stripLinkDefinitions($text)
  {
    $less_than_tab = $this->options['tab_width'] - 1;
    // Link defs are in the form: ^[id]: url "optional title"
    $text = preg_replace_callback('{
              ^[ ]{0,' . $less_than_tab . '}\[(.+)\][ ]?:  # id = $1
                [ ]*
                \n?        # maybe *one* newline
                [ ]*
              (?:
                <(.+?)>      # url = $2
              |
                (\S+?)      # url = $3
              )
                [ ]*
                \n?        # maybe one newline
                [ ]*
              (?:
                (?<=\s)      # lookbehind for whitespace
                ["(]
                (.*?)      # title = $4
                [")]
                [ ]*
              )?  # title is optional
              (?:\n+|\Z)
      }xm', array(&$this, 'stripLinkDefinitionsCallback'), $text);
    return $text;
  }

  /**
   * Callback method for stripLinkDefinitions()
   *
   * @param array $matches
   * @return string
   */
  protected function stripLinkDefinitionsCallback($matches)
  {
    $link_id = strtolower($matches[1]);
    $url = $matches[2] == '' ? $matches[3] : $matches[2];
    $this->urls[$link_id] = $url;
    $this->titles[$link_id] = &$matches[4];
    // String that will replace the block
    return '';
  }

  /**
   * Hashify HTML blocks.
   * We only want to do this for block-level HTML tags, such as headers,
   * lists, and tables. That's because we still want to wrap <p>s around
   * "paragraphs" that are wrapped in non-block-level tags, such as anchors,
   * phrase emphasis, and spans. The list of tags we're looking for is
   * hard-coded:
   *
   *  *  List "a" is made of tags which can be both inline or block-level.
   *     These will be treated block-level when the start tag is alone on
   *     its line, otherwise they're not matched here and will be taken as
   *     inline later.
   *  *  List "b" is made of tags which are always block-level;
   *
   * @param string $text
   * @return string
   */
  protected function hashHTMLBlocks($text)
  {
    if($this->options['no_markup'])
    {
      return $text;
    }

    $less_than_tab = $this->options['tab_width'] - 1;
    $block_tags_a_re = 'ins|del';
    $block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|' .
            'script|noscript|form|fieldset|iframe|math';
    // Regular expression for the content of a block tag.
    $nested_tags_level = 4;
    $attr = '
      (?>        # optional tag attributes
        \s      # starts with whitespace
        (?>
        [^>"/]+    # text outside quotes
        |
        /+(?!>)    # slash not followed by ">"
        |
        "[^"]*"    # text inside double quotes (tolerate ">")
        |
        \'[^\']*\'  # text inside single quotes (tolerate ">")
        )*
      )?
      ';
    $content =
            str_repeat('
        (?>
          [^<]+      # content without tag
        |
          <\2      # nested opening tag
          ' . $attr . '  # attributes
          (?>
            />
          |
            >', $nested_tags_level) . # end of opening tag
            '.*?' . # last level nested tag content
            str_repeat('
            </\2\s*>  # closing nested tag
          )
          |
          <(?!/\2\s*>  # other tags with a different name
          )
        )*', $nested_tags_level);
    $content2 = str_replace('\2', '\3', $content);

    //  First, look for nested blocks, e.g.:
    //   <div>
    //     <div>
    //     tags for inner block must be indented.
    //     </div>
    //   </div>
    //
    //  The outermost tags must start at the left margin for this to match, and
    //  the inner nested divs must be indented.
    //  We need to do this before the next, more liberal match, because the next
    //  match will start at the first `<div>` and stop at the first `</div>`.
    $text = preg_replace_callback('{(?>
      (?>
        (?<=\n\n)    # Starting after a blank line
        |        # or
        \A\n?      # the beginning of the doc
      )
      (            # save in $1

        # Match from `\n<tag>` to `</tag>\n`, handling nested tags
        # in between.

            [ ]{0,' . $less_than_tab . '}
            <(' . $block_tags_b_re . ')# start tag = $2
            ' . $attr . '>      # attributes followed by > and \n
            ' . $content . '    # content, support nesting
            </\2>        # the matching end tag
            [ ]*        # trailing spaces/tabs
            (?=\n+|\Z)  # followed by a newline or end of document

      | # Special version for tags of group a.

            [ ]{0,' . $less_than_tab . '}
            <(' . $block_tags_a_re . ')# start tag = $3
            ' . $attr . '>[ ]*\n  # attributes followed by >
            ' . $content2 . '    # content, support nesting
            </\3>        # the matching end tag
            [ ]*        # trailing spaces/tabs
            (?=\n+|\Z)  # followed by a newline or end of document

      | # Special case just for <hr />. It was easier to make a special
        # case than to make the other regex more complicated.

            [ ]{0,' . $less_than_tab . '}
            <(hr)        # start tag = $2
            ' . $attr . '      # attributes
            /?>          # the matching end tag
            [ ]*
            (?=\n{2,}|\Z)    # followed by a blank line or end of document

      | # Special case for standalone HTML comments:

          [ ]{0,' . $less_than_tab . '}
          (?s:
            <!-- .*? -->
          )
          [ ]*
          (?=\n{2,}|\Z)    # followed by a blank line or end of document

      | # PHP and ASP-style processor instructions (<? and <%)

          [ ]{0,' . $less_than_tab . '}
          (?s:
            <([?%])      # $2
            .*?
            \2>
          )
          [ ]*
          (?=\n{2,}|\Z)    # followed by a blank line or end of document

      )
      )}Sxmi', array(&$this, 'hashHTMLBlocksCallback'), $text);

    return $text;
  }

  /**
   * Callback method for hashHTMLBlocks()
   *
   * @param array $matches
   * @return string
   */
  protected function hashHTMLBlocksCallback($matches)
  {
    $text = $matches[1];
    $key = $this->hashBlock($text);
    return "\n\n$key\n\n";
  }

  /**
   *
   * Called whenever a tag must be hashed when a function insert an atomic
   * element in the text stream. Passing $text to through this function gives
   * a unique text-token which will be reverted back when calling unhash.
   *
   * The $boundary argument specify what character should be used to surround
   * the token. By convension, "B" is used for block elements that needs not
   * to be wrapped into paragraph tags at the end, ":" is used for elements
   * that are word separators and "X" is used in the general case.
   *
   * Swap back any tag hash found in $text so we do not have to `unhash`
   * multiple times at the end.
   *
   * @staticvar int $i
   * @param string $text
   * @param string $boundary What character should be used to surround the token?
   * @return string
   */
  protected function hashPart($text, $boundary = 'X')
  {
    $text = $this->unhash($text);
    // Then hash the block.
    static $i = 0;
    $key = "$boundary\x1A" . ++$i . $boundary;
    $this->html_hashes[$key] = $text;
    // String that will replace the tag.
    return $key;
  }

  /**
   * Shortcut method for hashPart with block-level boundaries.
   *
   * @param string $text
   * @return string
   */
  protected function hashBlock($text)
  {
    return $this->hashPart($text, 'B');
  }

  /**
   * Runs block gamut tranformations.
   *
   * We need to escape raw HTML in Markdown source before doing anything
   * else. This need to be done for each block, and not only at the
   * begining in the Markdown function since hashed blocks can be part of
   * list items and could have been indented. Indented blocks would have
   * been seen as a code block in a previous pass of hashHTMLBlocks.
   *
   * @param string $text
   * @return string
   */
  protected function runBlockGamut($text)
  {
    return $this->runBasicBlockGamut($this->hashHTMLBlocks($text));
  }

  /**
   * Runs block gamut tranformations, without hashing HTML blocks. This is
   * useful when HTML blocks are known to be already hashed, like in the first
   * whole-document pass.
   *
   * @param string $text
   * @return string
   * @throws Exception
   */
  protected function runBasicBlockGamut($text)
  {
    foreach($this->blockGamut as $method => $priority)
    {
      if(!is_callable(array($this, $method)))
      {
        throw new Exception(sprintf('Invalid block gamut "%s".', $method));
      }
      $text = $this->$method($text);
    }

    // finally form paragraph and restore hashed blocks.
    return $this->formParagraphs($text);
  }

  /**
   * Do horizontal rules
   *
   * @param string $text
   * @return string
   */
  protected function doHorizontalRules($text)
  {
    return preg_replace('{
        ^[ ]{0,3}  # Leading space
        ([-*_])    # $1: First marker
        (?>      # Repeated marker group
          [ ]{0,2}  # Zero, one, or two spaces.
          \1      # Marker character
        ){2,}    # Group repeated at least twice
        [ ]*    # Tailing spaces
        $      # End of line.
      }mx', "\n" . $this->hashBlock(sprintf(
              "<hr%s\n", self::$xhtml ? ' />' : '>')), $text);
  }

  /**
   * Runs span gamut tranformations.
   *
   * @param string $text
   * @return string
   * @throws Exception
   */
  protected function runSpanGamut($text)
  {
    foreach($this->spanGamut as $method => $priority)
    {
      if(!is_callable(array($this, $method)))
      {
        throw new Exception(sprintf('Invalid span gamut "%s".', $method));
      }
      $text = $this->$method($text);
    }
    return $text;
  }

  /**
   * Do hard breaks
   * @param string $text
   * @return string
   */
  protected function doHardBreaks($text)
  {
    return preg_replace_callback('/ {2,}\n/', array(&$this, 'doHardBreaksCallback'), $text);
  }

  /**
   * Callback method for doHardBreaks()
   *
   * @param array $matches
   * @return string
   */
  protected function doHardBreaksCallback($matches)
  {
    return $this->hashPart(sprintf("<br%s\n", self::$xhtml ? ' />' : '>'));
  }

  /**
   * Turns Markdown link shortcuts into X)HTML <a> tags.
   *
   * @param string $text
   * @return string
   */
  protected function doAnchors($text)
  {
    if($this->in_anchor)
    {
      return $text;
    }

    $this->in_anchor = true;

    // First, handle reference-style links: [link text] [id]
    $text = preg_replace_callback('{
      (          # wrap whole match in $1
        \[
        (' . $this->nestedBracketsRe . ')  # link text = $2
        \]

        [ ]?        # one optional space
        (?:\n[ ]*)?    # one optional newline followed by spaces

        \[
        (.*?)    # id = $3
        \]
      )
      }xs', array(&$this, 'doAnchorsReferenceCallback'), $text);

    #
    # Next, inline-style links: [link text](url "optional title")
    #
    $text = preg_replace_callback('{
      (        # wrap whole match in $1
        \[
        (' . $this->nestedBracketsRe . ')  # link text = $2
        \]
        \(      # literal paren
        [ \n]*
        (?:
          <(.+?)>  # href = $3
        |
          (' . $this->nestedUrlParenthesisRe . ')  # href = $4
        )
        [ \n]*
        (      # $5
          ([\'"])  # quote char = $6
          (.*?)    # Title = $7
          \6    # matching quote
          [ \n]*  # ignore any spaces/tabs between closing quote and )
        )?      # title is optional
        \)
      )
      }xs', array(&$this, 'doAnchorsInlineCallback'), $text);

    // Last, handle reference-style shortcuts: [link text]
    // These must come last in case you've also got [link text][1]
    // or [link text](/foo)
    $text = preg_replace_callback('{
      (          # wrap whole match in $1
        \[
        ([^\[\]]+)    # link text = $2; can\'t contain [ or ]
        \]
      )
      }xs', array(&$this, 'doAnchorsReferenceCallback'), $text);

    $this->in_anchor = false;
    return $text;
  }

  /**
   * Callback method for doAnchors()
   *
   * @param array $matches
   * @return string
   */
  protected function doAnchorsReferenceCallback($matches)
  {
    $whole_match = $matches[1];
    $link_text = $matches[2];
    $link_id = &$matches[3];

    if($link_id == '')
    {
      // for shortcut links like [this][] or [this].
      $link_id = $link_text;
    }

    // lower-case and turn embedded newlines into spaces
    $link_id = preg_replace('{[ ]?\n}', ' ', strtolower($link_id));

    if(isset($this->urls[$link_id]))
    {
      $result = sprintf('<a href="%s"',
                  $this->encodeAttribute($this->urls[$link_id]));

      if(isset($this->titles[$link_id]))
      {
        $title = $this->encodeAttribute($this->titles[$link_id]);
        $result .= sprintf(' title="%s"', $title);
      }

      $link_text = $this->runSpanGamut($link_text);
      $result .= sprintf('>%s</a>', $link_text);

      $result = $this->hashPart($result);
    }
    else
    {
      $result = $whole_match;
    }
    return $result;
  }

  /**
   * Callback method for doAnchors()
   *
   * @param array $matches
   * @return string
   */
  protected function doAnchorsInlineCallback($matches)
  {
    $whole_match = $matches[1];
    $link_text = $this->runSpanGamut($matches[2]);
    $url = $matches[3] == '' ? $matches[4] : $matches[3];
    $title = & $matches[7];

    $url = $this->encodeAttribute($url);

    $result = sprintf('<a href="%s"', $url);
    if(isset($title))
    {
      $title = $this->encodeAttribute($title);
      $result .= sprintf(' title="%s"', $title);
    }

    $link_text = $this->runSpanGamut($link_text);
    $result .= sprintf('>%s</a>', $link_text);
    return $this->hashPart($result);
  }

  /**
   * Turn Markdown image shortcuts into <img> tags.
   *
   * @param string $text
   * @return string
   */
  protected function doImages($text)
  {
    // First, handle reference-style labeled images: ![alt text][id]
    $text = preg_replace_callback('{
      (        # wrap whole match in $1
        !\[
        (' . $this->nestedBracketsRe . ')    # alt text = $2
        \]

        [ ]?        # one optional space
        (?:\n[ ]*)?    # one optional newline followed by spaces

        \[
        (.*?)    # id = $3
        \]

      )
      }xs', array(&$this, 'doImagesReferenceCallback'), $text);

    // Next, handle inline images:  ![alt text](url "optional title")
    // Don't forget: encode * and _
    $text = preg_replace_callback('{
      (        # wrap whole match in $1
        !\[
        (' . $this->nestedBracketsRe . ')    # alt text = $2
        \]
        \s?      # One optional whitespace character
        \(      # literal paren
        [ \n]*
        (?:
          <(\S*)>  # src url = $3
        |
          (' . $this->nestedUrlParenthesisRe . ')  # src url = $4
        )
        [ \n]*
        (      # $5
          ([\'"])  # quote char = $6
          (.*?)    # title = $7
          \6    # matching quote
          [ \n]*
        )?      # title is optional
        \)
      )
      }xs', array(&$this, 'doImagesInlineCallback'), $text);

    return $text;
  }

  /**
   * Callback method for doImages()
   *
   * @param array $matches
   * @return string
   */
  protected function doImagesReferenceCallback($matches)
  {
    $whole_match = $matches[1];
    $alt_text = $matches[2];
    $link_id = strtolower($matches[3]);

    if($link_id == '')
    {
      // for shortcut links like ![this][].
      $link_id = strtolower($alt_text);
    }

    $alt_text = $this->encodeAttribute($alt_text);
    if(isset($this->urls[$link_id]))
    {
      $url = $this->encodeAttribute($this->urls[$link_id]);
      $result = sprintf('<img src="%s" alt="%s"', $url, $alt_text);

      if(isset($this->titles[$link_id]))
      {
        $title = $this->encodeAttribute($this->titles[$link_id]);
        $result .= sprintf(' title="%s"', $title);
      }

      $result .= self::$xhtml ? ' />' : '>';
      $result = $this->hashPart($result);
    }
    else
    {
      // If there's no such link ID, leave intact:
      $result = $whole_match;
    }

    return $result;
  }

  /**
   * Callback method for doImages()
   *
   * @param array $matches
   * @return string
   */
  protected function doImagesInlineCallback($matches)
  {
    $whole_match = $matches[1];
    $alt_text = $matches[2];
    $url = $matches[3] == '' ? $matches[4] : $matches[3];
    $title = & $matches[7];

    $alt_text = $this->encodeAttribute($alt_text);
    $url = $this->encodeAttribute($url);
    $result = sprintf('<img src="%s" alt="%s"', $url, $alt_text);
    if(isset($title))
    {
      $title = $this->encodeAttribute($title);
      // $title already quoted
      $result .= sprintf(' title="%s"', $title);
    }
    $result .= self::$xhtml ? ' />' : '>';
    return $this->hashPart($result);
  }

  /**
   * Do headers
   *
   * @param string $text
   * @return string
   */
  protected function doHeaders($text)
  {
    //  setext style headers:
    //  Header 1
    //  ========
    //
    //  Header 2
    //  --------
    $text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
            array(&$this, 'doHeadersCallbackSetext'), $text);

    // atx-style headers:
    //  # Header 1
    //  ## Header 2
    //  ## Header 2 with closing hashes ##
    //  ...
    //  ###### Header 6
    $text = preg_replace_callback('{
        ^(\#{1,6})  # $1 = string of #\'s
        [ ]*
        (.+?)    # $2 = Header text
        [ ]*
        \#*      # optional closing #\'s (not counted)
        \n+
      }xm', array(&$this, 'doHeadersCallbackAtx'), $text);

    return $text;
  }

  /**
   * Callback method for doHeaders()
   *
   * @param array $matches
   * @return string
   */
  protected function doHeadersCallbackSetext($matches)
  {
    // Terrible hack to check we haven't found an empty list item.
    if($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
    {
      return $matches[0];
    }

    $level = $matches[2]{0} == '=' ? 1 : 2;
    $block = "<h$level>" . $this->runSpanGamut($matches[1]) . "</h$level>";
    return "\n" . $this->hashBlock($block) . "\n\n";
  }

  /**
   * Callback method for doHeaders()
   *
   * @param array $matches
   * @return string
   */
  protected function doHeadersCallbackAtx($matches)
  {
    $level = strlen($matches[1]);
    $block = "<h$level>" . $this->runSpanGamut($matches[2]) . "</h$level>";
    return "\n" . $this->hashBlock($block) . "\n\n";
  }

  /**
   * Form HTML ordered (numbered) and unordered (bulleted) lists.
   *
   * @param string $text
   * @return string
   */
  protected function doLists($text)
  {
    $less_than_tab = $this->options['tab_width'] - 1;
    // Re-usable patterns to match list item bullets and number markers:
    $marker_ul_re = '[*+-]';
    $marker_ol_re = '\d+[\.]';
    $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

    $markers_relist = array(
        $marker_ul_re => $marker_ol_re,
        $marker_ol_re => $marker_ul_re,
    );

    foreach($markers_relist as $marker_re => $other_marker_re)
    {
      # Re-usable pattern to match any entirel ul or ol list:
      $whole_list_re = '
        (                # $1 = whole list
          (                # $2
          ([ ]{0,' . $less_than_tab . '})  # $3 = number of spaces
          (' . $marker_re . ')      # $4 = first list item marker
          [ ]+
          )
          (?s:.+?)
          (                # $5
            \z
          |
            \n{2,}
            (?=\S)
            (?!            # Negative lookahead for another list item marker
            [ ]*
            ' . $marker_re . '[ ]+
            )
          |
            (?=            # Lookahead for another kind of list
              \n
            \3            # Must have the same indentation
            ' . $other_marker_re . '[ ]+
            )
          )
        )
      '; // mx
      // We use a different prefix before nested lists than top-level lists.
      // See extended comment in _ProcessListItems().

      if($this->listLevel)
      {
        $text = preg_replace_callback('{
            ^
            ' . $whole_list_re . '
          }mx', array(&$this, 'doListsCallback'), $text);
      }
      else
      {
        $text = preg_replace_callback('{
            (?:(?<=\n)\n|\A\n?) # Must eat the newline
            ' . $whole_list_re . '
          }mx', array(&$this, 'doListsCallback'), $text);
      }
    }
    return $text;
  }

  /**
   * Callback method for doLists()
   *
   * @param array $matches
   * @return string
   */
  protected function doListsCallback($matches)
  {
    // Re-usable patterns to match list item bullets and number markers:
    $marker_ul_re = '[*+-]';
    $marker_ol_re = '\d+[\.]';
    $marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

    $list = $matches[1];
    $list_type = preg_match("/$marker_ul_re/", $matches[4]) ? 'ul' : 'ol';

    $marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );
    $list .= "\n";
    $result = $this->processListItems($list, $marker_any_re);
    $result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
    return "\n" . $result . "\n\n";
  }

  /**
   * Process the contents of a single ordered or unordered list, splitting it
   * into individual list items.
   *
   * @param type $list_str
   * @param type $marker_any_re
   * @return type
   */
  protected function processListItems($list_str, $marker_any_re)
  {
    // The $this->listLevel global keeps track of when we're inside a list.
    // Each time we enter a list, we increment it; when we leave a list,
    // we decrement. If it's zero, we're not in a list anymore.

    // We do this because when we're not inside a list, we want to treat
    // something like this:

    //  I recommend upgrading to version
    //  8. Oops, now this line is treated
    //  as a sub-list.

    // As a single paragraph, despite the fact that the second line starts
    // with a digit-period-space sequence.

    // Whereas when we're inside a list (or sub-list), that line will be
    // treated as the start of a sub-list. What a kludge, huh? This is
    // an aspect of Markdown's syntax that's hard to parse perfectly
    // without resorting to mind-reading. Perhaps the solution is to
    // change the syntax rules such that sub-lists must start with a
    // starting cardinal number; e.g. "1." or "a.".

    $this->listLevel++;

    // trim trailing blank lines:
    $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

    $list_str = preg_replace_callback('{
      (\n)?              # leading line = $1
      (^[ ]*)              # leading whitespace = $2
      (' . $marker_any_re . '        # list marker and space = $3
        (?:[ ]+|(?=\n))  # space only required if item is not empty
      )
      ((?s:.*?))            # list item text   = $4
      (?:(\n+(?=\n))|\n)        # tailing blank line = $5
      (?= \n* (\z | \2 (' . $marker_any_re . ') (?:[ ]+|(?=\n))))
      }xm', array(&$this, 'processListItemsCallback'), $list_str);

    $this->listLevel--;
    return $list_str;
  }

  /**
   * Callback method for processListItems()
   *
   * @param array $matches
   * @return string
   */
  protected function processListItemsCallback($matches)
  {
    $item = $matches[4];
    $leading_line = & $matches[1];
    $leading_space = & $matches[2];
    $marker_space = $matches[3];
    $tailing_blank_line = & $matches[5];

    if($leading_line || $tailing_blank_line ||
            preg_match('/\n{2,}/', $item))
    {
      // Replace marker with the appropriate whitespace indentation
      $item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
      $item = $this->runBlockGamut($this->outdent($item) . "\n");
    }
    else
    {
      // Recursion for sub-lists:
      $item = $this->doLists($this->outdent($item));
      $item = preg_replace('/\n+$/', '', $item);
      $item = $this->runSpanGamut($item);
    }

    return sprintf("<li>%s</li>\n", $item);
  }

  /**
   * Process Markdown `<pre><code>` blocks.
   *
   * @param string $text
   * @return string
   */
  protected function doCodeBlocks($text)
  {
    $text = preg_replace_callback('{
        (?:\n\n|\A\n?)
        (              # $1 = the code block -- one or more lines, starting with a space/tab
          (?>
          [ ]{' . $this->options['tab_width'] . '}  # Lines must start with a tab or a tab-width of spaces
          .*\n+
          )+
        )
        ((?=^[ ]{0,' . $this->options['tab_width'] . '}\S)|\Z)  # Lookahead for non-space at line-start, or end of doc
      }xm', array(&$this, 'doCodeBlocksCallback'), $text);

    return $text;
  }

  /**
   * Callback method for doCodeBlocks()
   *
   * @param array $matches
   * @return string
   */
  protected function doCodeBlocksCallback($matches)
  {
    $codeblock = $matches[1];
    $codeblock = $this->outdent($codeblock);
    $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES, $this->options['charset'], false);

    // trim leading newlines and trailing newlines
    $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

    $codeblock = "<pre><code>$codeblock\n</code></pre>";
    return "\n\n" . $this->hashBlock($codeblock) . "\n\n";
  }

  /**
   * Create a code span markup for $code. Called from handleSpanToken.
   *
   * @param string $code
   * @return string
   */
  protected function makeCodeSpan($code)
  {
    $code = htmlspecialchars(trim($code), ENT_NOQUOTES, $this->options['charset'], false);
    return $this->hashPart(sprintf('<code>%s</code>', $code));
  }

  /**
   * Prepare regular expressions for searching emphasis tokens in any context
   *
   */
  protected function prepareItalicsAndBold()
  {
    foreach($this->em_relist as $em => $em_re)
    {
      foreach($this->strong_relist as $strong => $strong_re)
      {
        // Construct list of allowed token expressions.
        $token_relist = array();
        if(isset($this->em_strong_relist["$em$strong"]))
        {
          $token_relist[] = $this->em_strong_relist["$em$strong"];
        }
        $token_relist[] = $em_re;
        $token_relist[] = $strong_re;
        // Construct master expression from list.
        $token_re = '{(' . implode('|', $token_relist) . ')}';
        $this->em_strong_prepared_relist["$em$strong"] = $token_re;
      }
    }
  }

  /**
   *
   * @param string $text
   * @return string
   */
  protected function doItalicsAndBold($text)
  {
    $token_stack = array('');
    $text_stack = array('');
    $em = '';
    $strong = '';
    $tree_char_em = false;

    while(1)
    {
      // Get prepared regular expression for seraching emphasis tokens
      // in current context.
      $token_re = $this->em_strong_prepared_relist["$em$strong"];

      // Each loop iteration search for the next emphasis token.
      // Each token is then passed to handleSpanToken.
      $parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
      $text_stack[0] .= $parts[0];
      $token = & $parts[1];
      $text = & $parts[2];

      if(empty($token))
      {
        // Reached end of text span: empty stack without emitting.
        // any more emphasis.
        while($token_stack[0])
        {
          $text_stack[1] .= array_shift($token_stack);
          $text_stack[0] .= array_shift($text_stack);
        }
        break;
      }

      $token_len = strlen($token);
      if($tree_char_em)
      {
        // Reached closing marker while inside a three-char emphasis.
        if($token_len == 3)
        {
          // Three-char closing marker, close em and strong.
          array_shift($token_stack);
          $span = array_shift($text_stack);
          $span = $this->runSpanGamut($span);
          $span = "<strong><em>$span</em></strong>";
          $text_stack[0] .= $this->hashPart($span);
          $em = '';
          $strong = '';
        }
        else
        {
          // Other closing marker: close one em or strong and
          // change current token state to match the other
          $token_stack[0] = str_repeat($token{0}, 3 - $token_len);
          $tag = $token_len == 2 ? "strong" : "em";
          $span = $text_stack[0];
          $span = $this->runSpanGamut($span);
          $span = "<$tag>$span</$tag>";
          $text_stack[0] = $this->hashPart($span);
          $$tag = ''; // $$tag stands for $em or $strong
        }
        $tree_char_em = false;
      }
      else if($token_len == 3)
      {
        if($em)
        {
          // Reached closing marker for both em and strong.
          // Closing strong marker:
          for($i = 0; $i < 2; ++$i)
          {
            $shifted_token = array_shift($token_stack);
            $tag = strlen($shifted_token) == 2 ? "strong" : "em";
            $span = array_shift($text_stack);
            $span = $this->runSpanGamut($span);
            $span = "<$tag>$span</$tag>";
            $text_stack[0] .= $this->hashPart($span);
            $$tag = ''; // $$tag stands for $em or $strong
          }
        }
        else
        {
          // Reached opening three-char emphasis marker. Push on token
          // stack; will be handled by the special condition above.
          $em = $token{0};
          $strong = "$em$em";
          array_unshift($token_stack, $token);
          array_unshift($text_stack, '');
          $tree_char_em = true;
        }
      }
      else if($token_len == 2)
      {
        if($strong)
        {
          // Unwind any dangling emphasis marker:
          if(strlen($token_stack[0]) == 1)
          {
            $text_stack[1] .= array_shift($token_stack);
            $text_stack[0] .= array_shift($text_stack);
          }
          // Closing strong marker:
          array_shift($token_stack);
          $span = array_shift($text_stack);
          $span = $this->runSpanGamut($span);
          $span = "<strong>$span</strong>";
          $text_stack[0] .= $this->hashPart($span);
          $strong = '';
        }
        else
        {
          array_unshift($token_stack, $token);
          array_unshift($text_stack, '');
          $strong = $token;
        }
      }
      else
      {
        // Here $token_len == 1
        if($em)
        {
          if(strlen($token_stack[0]) == 1)
          {
            // Closing emphasis marker:
            array_shift($token_stack);
            $span = array_shift($text_stack);
            $span = $this->runSpanGamut($span);
            $span = "<em>$span</em>";
            $text_stack[0] .= $this->hashPart($span);
            $em = '';
          }
          else
          {
            $text_stack[0] .= $token;
          }
        }
        else
        {
          array_unshift($token_stack, $token);
          array_unshift($text_stack, '');
          $em = $token;
        }
      }
    }
    return $text_stack[0];
  }

  protected function doBlockQuotes($text)
  {
    $text = preg_replace_callback('/
        (                # Wrap whole match in $1
        (?>
          ^[ ]*>[ ]?      # ">" at the start of a line
          .+\n          # rest of the first line
          (.+\n)*          # subsequent consecutive lines
          \n*            # blanks
        )+
        )
      /xm', array(&$this, 'doBlockQuotesCallback'), $text);

    return $text;
  }

  protected function doBlockQuotesCallback($matches)
  {
    $bq = $matches[1];
    // trim one level of quoting - trim whitespace-only lines
    $bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
    $bq = $this->runBlockGamut($bq);  # recurse

    $bq = preg_replace('/^/m', "  ", $bq);
    // These leading spaces cause problem with <pre> content,
    // so we need to fix that:
    $bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, 'doBlockQuotesCallback2'), $bq);

    return "\n" . $this->hashBlock("<blockquote>\n$bq\n</blockquote>") . "\n\n";
  }

  protected function doBlockQuotesCallback2($matches)
  {
    $pre = $matches[1];
    $pre = preg_replace('/^  /m', '', $pre);
    return $pre;
  }

  /**
   * Formats paragraphs
   *
   * @param type $text
   * @return type
   */
  protected function formParagraphs($text)
  {
    // Strip leading and trailing lines:
    $text = preg_replace('/\A\n+|\n+\z/', '', $text);
    $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

    // Wrap <p> tags and unhashify HTML blocks
    foreach($grafs as $key => $value)
    {
      if(!preg_match('/^B\x1A[0-9]+B$/', $value))
      {
        # Is a paragraph.
        $value = $this->runSpanGamut($value);
        $value = preg_replace('/^([ ]*)/', "<p>", $value);
        $value .= "</p>";
        $grafs[$key] = $this->unhash($value);
      }
      else
      {
        # Is a block.
        # Modify elements of @grafs in-place...
        $graf = $value;
        $block = $this->html_hashes[$graf];
        $graf = $block;
//        if (preg_match('{
//          \A
//          (              # $1 = <div> tag
//            <div  \s+
//            [^>]*
//            \b
//            markdown\s*=\s*  ([\'"])  #  $2 = attr quote char
//            1
//            \2
//            [^>]*
//            >
//          )
//          (              # $3 = contents
//          .*
//          )
//          (</div>)          # $4 = closing tag
//          \z
//          }xs', $block, $matches))
//        {
//          list(, $div_open, , $div_content, $div_close) = $matches;
//
//          # We can't call Markdown(), because that resets the hash;
//          # that initialization code should be pulled into its own sub, though.
//          $div_content = $this->hashHTMLBlocks($div_content);
//
//          # Run document gamut methods on the content.
//          foreach ($this->documentGamut as $method => $priority) {
//            $div_content = $this->$method($div_content);
//          }
//
//          $div_open = preg_replace(
//            '{\smarkdown\s*=\s*([\'"]).+?\1}', '', $div_open);
//
//          $graf = $div_open . "\n" . $div_content . "\n" . $div_close;
//        }
        $grafs[$key] = $graf;
      }
    }

    return implode("\n\n", $grafs);
  }

  /**
   * Encode text for a double-quoted HTML attribute. This function
   * is *not* suitable for attributes enclosed in single quotes.
   *
   * @param string $text
   * @return string
   */
  protected function encodeAttribute($text)
  {
    $text = $this->encodeAmpsAndAngles($text);
    $text = str_replace('"', '&quot;', $text);
    return $text;
  }

  /**
   * Smart processing for ampersands and angle brackets that need to
   * be encoded. Valid character entities are left alone unless the
   * no-entities mode is set.
   *
   * @param string $text
   * @return string
   */
  protected function encodeAmpsAndAngles($text)
  {
    if($this->options['no_entities'])
    {
      $text = str_replace('&', '&amp;', $text);
    }
    else
    {
      # Ampersand-encoding based entirely on Nat Irons's Amputator
      # MT plugin: <http://bumppo.net/projects/amputator/>
      $text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);
    }
    # Encode remaining <'s
    $text = str_replace('<', '&lt;', $text);

    return $text;
  }

  /**
   * Autolinks text
   *
   * @param string $text
   * @return string
   */
  protected function doAutoLinks($text)
  {
    $text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i',
            array(&$this, 'doAutoLinksUrlCallback'), $text);

    # Email addresses: <address@domain.foo>
    $text = preg_replace_callback('{
      <
      (?:mailto:)?
      (
        (?:
          [-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
        |
          ".*?"
        )
        \@
        (?:
          [-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
        |
          \[[\d.a-fA-F:]+\]  # IPv4 & IPv6
        )
      )
      >
      }xi', array(&$this, 'doAutoLinksEmailCallback'), $text);

    return $text;
  }

  /**
   * Callback for doAutoLinks()
   *
   * @param array $matches
   * @return string
   */
  protected function doAutoLinksUrlCallback($matches)
  {
    $url = $this->encodeAttribute($matches[1]);
    $link = "<a href=\"$url\">$url</a>";
    return $this->hashPart($link);
  }

  /**
   *
   * @param array $matches
   * @return string
   */
  protected function doAutoLinksEmailCallback($matches)
  {
    $address = $matches[1];
    $link = $this->encodeEmailAddress($address);
    return $this->hashPart($link);
  }

  /**
   * Input: an email address, e.g. "foo@example.com"
   *
   * Output: the email address as a mailto link, with each character
   * of the address encoded as either a decimal or hex entity, in
   * the hopes of foiling most address harvesting spam bots.
   *
   * Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
   * With some optimizations by Milian Wolff.
   *
   * @param string $addr
   * @return string
   */
  protected function encodeEmailAddress($addr)
  {
    $addr = sprintf('mailto:%s', $addr);
    $chars = preg_split('/(?<!^)(?!$)/', $addr);
    // Deterministic seed.
    $seed = (int)abs(crc32($addr) / strlen($addr));

    foreach($chars as $key => $char)
    {
      $ord = ord($char);
      // Ignore non-ascii chars.
      if($ord < 128)
      {
        // Pseudo-random function.
        $r = ($seed * (1 + $key)) % 100;
        // roughly 10% raw, 45% hex, 45% dec
        // '@' *must* be encoded. I insist.
        if($r > 90 && $char != '@');
         // do nothing
        else if($r < 45)
          $chars[$key] = '&#x' . dechex($ord) . ';';
        else
          $chars[$key] = '&#' . $ord . ';';
      }
    }

    $addr = implode('', $chars);
    $text = implode('', array_slice($chars, 7)); # text without `mailto:`
    $addr = "<a href=\"$addr\">$text</a>";

    return $addr;
  }

  /**
   * Take the string $str and parse it into tokens, hashing embeded HTML,
   * escaped characters and handling code spans.
   *
   * @param string $str
   * @return string
   */
  protected function parseSpan($str)
  {
    $output = '';
    $span_re = '{
        (
          \\\\' . $this->escapeCharsRe . '
        |
          (?<![`\\\\])
          `+            # code span marker
      ' . ( $this->options['no_markup'] ? '' : '
        |
          <!--    .*?     -->    # comment
        |
          <\?.*?\?> | <%.*?%>    # processing instruction
        |
          <[/!$]?[-a-zA-Z0-9:_]+  # regular tags
          (?>
            \s
            (?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
          )?
          >
      ') . '
        )
        }xs';

    while(1)
    {
      // Each loop iteration seach for either the next tag, the next
      // openning code span marker, or the next escaped character.
      // Each token is then passed to handleSpanToken.
      $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
      // Create token from text preceding tag.
      if($parts[0] != "")
      {
        $output .= $parts[0];
      }
      // Check if we reach the end.
      if(isset($parts[1]))
      {
        $output .= $this->handleSpanToken($parts[1], $parts[2]);
        $str = $parts[2];
      }
      else
      {
        break;
      }
    }

    return $output;
  }

  /**
   * Handle $token provided by parseSpan by determining its nature and
   * returning the corresponding value that should replace it.
   *
   * @param string $token
   * @param string $str
   * @return string
   */
  protected function handleSpanToken($token, &$str)
  {
    switch($token{0})
    {
      case "\\":
        return $this->hashPart("&#" . ord($token{1}) . ";");
      case "`":
        // Search for end marker in remaining text.
        if(preg_match('/^(.*?[^`])' . preg_quote($token) . '(?!`)(.*)$/sm', $str, $matches))
        {
          $str = $matches[2];
          $codespan = $this->makeCodeSpan($matches[1]);
          return $this->hashPart($codespan);
        }
        return $token; // return as text since no ending marker found.
      default:
        return $this->hashPart($token);
    }
  }

  /**
   * Remove one level of line-leading tabs or spaces
   *
   * @param string $text
   * @return string
   */
  protected function outdent($text)
  {
    return preg_replace('/^(\t|[ ]{1,' . $this->options['tab_width'] . '})/m', '', $text);
  }

  /**
   * Replace tabs with the appropriate amount of space.
   *
   * For each line we separate the line in blocks delemited by
   * tab characters. Then we reconstruct every line by adding the
   * appropriate number of space between each blocks.
   *
   * @param string $text
   * @return string
   */
  protected function detab($text)
  {
    return preg_replace_callback('/^.*\t.*$/m', array($this, 'detabCallback'), $text);
  }

  /**
   * Callback for detab() method.
   *
   * @param array $matches
   * @return string
   */
  protected function detabCallback($matches)
  {
    $line = $matches[0];
    // Split in blocks.
    $blocks = explode("\t", $line);
    // Add each blocks to the line.
    $line = $blocks[0];
    // Do not add first block twice.
    unset($blocks[0]);
    foreach($blocks as $block)
    {
      // Calculate amount of space, insert spaces, insert block.
      $amount = $this->options['tab_width'] -
              sfUtf8::len($line) % $this->options['tab_width'];
      $line .= str_repeat(' ', $amount) . $block;
    }
    return $line;
  }

  /**
   * Swap back in all the tags hashed by _HashHTMLBlocks.
   *
   * @param string $text
   * @return string
   */
  protected function unhash($text)
  {
    return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(&$this, 'unhashCallback'), $text);
  }

  /**
   * Callback for unhash()
   *
   * @param array $matches
   * @return string
   */
  protected function unhashCallback($matches)
  {
    return $this->html_hashes[$matches[0]];
  }

  /**
   * Adding the fenced code block syntax to regular Markdown.
   *
   * ~~~
   * Code block
   * ~~~
   *
   * ~~~ php
   *
   * <?php
   * // Code block
   * ~~~
   *
   * @param type $text
   * @return type
   */
  protected function doFencedCodeBlocks($text)
  {
    $text = preg_replace_callback('{
        (?:\n|\A)
        # 1: Opening marker
        (
        ~{3,} # Marker: three tilde or more.
        )
        # 2: CSS classes
        \s?([\s-_\w\d]+)?
        [ ]* \n # Whitespace and newline following marker.
        # 3: Content
        (
        (?>
        (?!\1 [ ]* \n) # Not a closing marker.
        .*\n+
        )+
        )
        # Closing marker.
        \1 [ ]* \n
      }xm',
      array(&$this, 'doFencedCodeBlocksCallback'), $text);

    return $text;
  }

  protected function doFencedCodeBlocksCallback($matches)
  {
    $codeblock = $matches[3];
    $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES, $this->options['charset'], false);

    $codeblock = preg_replace_callback('/^\n+/',
      array(&$this, 'doFencedCodeBlocksNewlines'), $codeblock);

    $class = empty($matches[2]) ? '' : trim($matches[2]);

    $codeblock = sprintf('<pre><code%s>%s</code></pre>',
            ($class ? sprintf(' class="%s"', $class) : ''), $codeblock);

    return "\n\n".$this->hashBlock($codeblock)."\n\n";
  }

  protected function doFencedCodeBlocksNewlines($matches)
  {
    return str_repeat(sprintf('<br%s', self::$xhtml ? ' />' : '>'), strlen($matches[0]));
  }

}
