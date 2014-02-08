<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Server side library for syntax highlighting code snippets. Based on the Nijikodo
 * library by Craig Campbell.
 *
 * @package Sift
 * @subpackage debug_highlighter
 * @link http://www.craigiam.com/nijikodo
 * @link http://www.phoboslab.org/log/2007/08/generic-syntax-highlighting-with-regular-expressions
 */
abstract class sfSyntaxHighlighter {

  /**
   * Default css prefix
   *
   */
  const DEFAULT_CSS_PREFIX = '';

  /**
   * Default charset
   *
   */
  const DEFAULT_CHARSET = 'UTF-8';

  /**
   * Css prefix
   *
   * @var string
   */
  protected $cssPrefix = self::DEFAULT_CSS_PREFIX;

  /**
   * The code to highlight
   *
   * @var string
   */
  protected $code;

  /**
   * The highlighted code
   *
   * @var string
   */
  protected $html = '';

  /**
   * Processed flag
   *
   * @var boolean
   */
  protected $processed = false;

  /**
   * Instantiates code highlighting class
   *
   * @param string $code The code to highlight
   * @param string $charset The charset
   * @param string $cssPrefix The css prefix
   * @return void
   */
  public function __construct($code, $charset = self::DEFAULT_CHARSET, $cssPrefix = self::DEFAULT_CSS_PREFIX)
  {
    $this->setCode($code, $charset);
    $this->cssPrefix = $cssPrefix;
  }

  /**
   * Highlighter factory
   *
   * @param string $language The language
   * @param string $cssPrefix Css prefix for the classes
   * @return sfSyntaxHighlighter
   */
  public static function factory($language = null, $code = '', $charset = self::DEFAULT_CHARSET, $cssPrefix = self::DEFAULT_CSS_PREFIX)
  {
    switch(strtolower($language))
    {
      case 'php':
        $highlighter = new sfSyntaxHighlighterPhp($code, $charset, $cssPrefix);
      break;

      case 'html':
        $highlighter = new sfSyntaxHighlighterHtml($code, $charset, $cssPrefix);
      break;

      case 'sql':
        $highlighter = new sfSyntaxHighlighterSql($code, $charset, $cssPrefix);
      break;

      default:
        $highlighter = new sfSyntaxHighlighterGeneric($code, $charset, $cssPrefix);
      break;
    }

    return $highlighter;
  }

  /**
   * Sets the code
   *
   * @param string $code The code to highlight
   * @param string $charset The charset
   * @return sfSyntaxHighlighter
   */
  public function setCode($code, $charset = self::DEFAULT_CHARSET)
  {
    $this->code = htmlspecialchars((string)$code, ENT_COMPAT, $charset, false);
    $this->processed = false;

    return $this;
  }

  /**
   * Sets class name to prepend to css classes
   *
   * Classes will look like {$class}_int or {$class}_keyword or ${class}_variable
   *
   * @param string
   * @return sfSyntaxHighlighter
   */
  public function setCssPrefix($prefix)
  {
    $this->cssPrefix = $prefix;

    return $this;
  }

  /**
   * Returns the css prefix
   *
   * @return string
   */
  public function getCssPrefix()
  {
    return $this->cssPrefix;
  }

  /**
   * Converts specific code block to html
   *
   * @return void
   */
  abstract protected function process();

  /**
   * Reset the highlighter
   *
   * @return sfSyntaxHighlighter
   */
  public function reset()
  {
    $this->processed = false;
    $this->html = '';

    return $this;
  }

  /**
   * Gets html output
   *
   * @param string Number lines?
   * @return string
   */
  public function getHtml($numberLines = false)
  {
    if($this->processed === false)
    {
      $this->process();
      $this->processed = true;
    }

    return $numberLines ? $this->getHtmlWithLineNumbers($this->html) : $this->html;
  }

  /**
   * Returns the html code with line numbers assigned
   *
   * @param string $html
   */
  protected function getHtmlWithLineNumbers($html, $line = -1)
  {
    $source = explode("\n", str_replace(array("\r\n", "\r"), "\n", $html));
    end($source);
    $lineWidth = strlen((string)key($source));
    $out = '';
    foreach($source as $lineNumber => $lineContent)
    {
      $lineNumber++;
      $lineContent = str_replace(array("\r", "\n", "\t"), array('', '', '  '), $lineContent);
      preg_match_all('#<[^>]+>#', $lineContent, $tags);
      // this is the current line
      if($lineNumber == $line)
      {
        $out .= sprintf("<span class=\"line current\">%{$lineWidth}s: %s\n</span>%s",
          $lineNumber,
          strip_tags($lineContent),
          implode('', $tags[0])
        );
      }
      else
      {
        $out .= sprintf("<span class=\"line\">%{$lineWidth}s:</span> %s\n",
                        $lineNumber, $lineContent);
      }
    }

    return $out;
  }

  /**
   * Returns an excerpt from the highlighted code
   *
   * @param integer $limit Maximum number of lines to return
   * @param integer $line The line number to highlight
   */
  public function getExcerpt($limitLines, $line = -1)
  {
    $source = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->getHtml()));
    // we need to prepend empty item so the line numbers matches
    // because of <?php is not taken as a line
    array_unshift($source, null);
    $out = '';
    $spans = 0;
    if($limitLines > 0)
    {
      $start = $i = max(0, $line - floor($limitLines * 2/3));
      // find last highlighted block
      while(--$i >= 1)
      {
        if(preg_match('#.*(</?span[^>]*>)#', $source[$i], $m))
        {
          if($m[1] !== '</span>')
          {
            $spans++;
            $out .= $m[1];
          }
          break;
        }
      }
      $source = array_slice($source, $start, $limitLines, true);
      end($source);
    }

    $lineWidth = strlen((string)key($source));
    foreach($source as $lineNumber => $lineContent)
    {
      $spans += substr_count($lineContent, '<span') - substr_count($lineContent, '</span');
      $lineContent = str_replace(array("\r", "\n", "\t"), array('', '', '  '), $lineContent);
      preg_match_all('#<[^>]+>#', $lineContent, $tags);
      // this is the current line
      if($lineNumber == $line)
      {
        $out .= sprintf("<span class=\"line current\">%{$lineWidth}s: %s\n</span>%s",
          $lineNumber,
          strip_tags($lineContent),
          implode('', $tags[0])
        );
      }
      else
      {
        $out .= sprintf("<span class=\"line\">%{$lineWidth}s:</span> %s\n",
                        $lineNumber, $lineContent);
      }
    }
    // fix endings
    if($spans !== false)
    {
      $out .= str_repeat('</span>', $spans);
    }

    return $out;
  }

  /**
   * output the class as a string
   *
   * @return string
   */
  final public function __toString()
  {
    return $this->getHtml();
  }

}
