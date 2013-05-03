<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCompressOutputFilter filter
 *
 * Usage: configure via filters.yml in your application.
 *
 * compress:
 *   class: sfCompressOutputFilter
 *   param:
 *     tidy: false # use html tidy
 *     compress: true # compress output using Gzip
 *     compress_level: 9 # compression level (1-9)
 *     compress_min_lenght: 2048 # minimal lenght of content which will be compressed
 *     remove_whitespace: true # remove whitespace from the content?
 *
 * @package Sift
 * @subpackage filter
 */
class sfCompressOutputFilter extends sfFilter
{

  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // execute this filter only once, before rendering to client
    // and only for html content
    if($this->isFirstCall() &&
      sfConfig::get('sf_environment') != 'dev' &&
      sfConfig::get('sf_environment') != 'staging' &&
      preg_match('|text/html|', $this->getContext()->getResponse()->getContentType()))
    {
      $content = $this->getContext()->getResponse()->getContent();

      // tidy up the content
      if($this->getParameter('tidy', false))
      {
        $content = $this->tidy($content);
      }

      // remove whitespace from the content
      if($this->getParameter('remove_whitespace', true))
      {
        $content = $this->removeWhitespace($content);
      }

      // disable compression if in debug mode!
      if($this->getParameter('compress', true) && !sfConfig::get('sf_web_debug'))
      {
        $content = $this->compress($content);
      }

      // done, set content
      $this->getContext()->getResponse()->setContent($content);
    }

  }

  /**
   * Cleans up the text with Tidy
   *
   * @param string Text to tidy
   * @return string cleaned text
   */
  protected function tidy($text)
  {
    if(!$this->tidyInstalled())
    {
      return $text;
    }

    $config = array('indent'        => false,
                    'output-xhtml'  => true,
                    'enclose-block-text' => false,
                    'enclose-text' => true,
                    'indent-cdata' => true,
                    'break-before-br' => true,
                    'wrap'          => 0);

    $log_enabled = sfConfig::get('sf_logging_enabled');

    if($log_enabled)
    {
      sfContext::getInstance()->getLogger()->info('{myCompressOutputFilter} Cleaning up the response content');
    }

    $tidy = tidy_parse_string($text, $config, 'UTF8');
    $tidy->cleanRepair();

    if($log_enabled && $this->getParameter('tidy_log_errors', false))
    {
      preg_match_all('/^(?:line (\d+) column (\d+) - )?(\S+): (?:\[((?:\d+\.?){4})]:)?(.*?)$/m', $tidy->errorBuffer, $tidy_errors, PREG_SET_ORDER);
      $logger = sfContext::getInstance()->getLogger();
      foreach($tidy_errors as $error)
      {
        $logger->err(sprintf('{myCompressOutputFilter} %s', $error[0]));
      }
    }

    return $tidy;
  }

  /**
   * Removes extra white space within the text.
   *
   * Trim leading white space and blank lines from html code,
   * cleaning up code and saving bandwidth. Does not
   * affect <PRE>></PRE>, <TEXTAREA></TEXTAREA> and <SCRIPT></SCRIPT> blocks.
   *
   */
  protected function removeWhitespace($content)
  {
    // Pull out the script blocks
    preg_match_all("!<script[^>]+>.*?</script>!is", $content, $match);

    $_script_blocks = $match[0];

    $content = preg_replace("!<script[^>]+>.*?</script>!is",
    '@@@SYMFONY:TRIM:SCRIPT@@@', $content);

    // Pull out the pre blocks
    preg_match_all("!<pre[^>]*>.*?</pre>!is", $content, $match);
    $_pre_blocks = $match[0];
    $content = preg_replace("!<pre[^>]*>.*?</pre>!is",
    '@@@SYMFONY:TRIM:PRE@@@', $content);

    // Pull out the textarea blocks
    preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $content, $match);
    $_textarea_blocks = $match[0];
    $content = preg_replace("!<textarea[^>]+>.*?</textarea>!is",
    '@@@SYMFONY:TRIM:TEXTAREA@@@', $content);

    // strip html comments, cut unneeded new lines.
    $content = preg_replace(array(
                            "/[\r\n][\r\n\t]*[\r\n]/i",
                            // strip spaces between tags
                            "/(\s+)?(\<.+\>)(\s+)?/",
                            // strip comments
                            // "/<!--((?!-->).)*-->/",
                            // get rid of mutliple spaces and replace it with one
                            "/(\s){2,}/i"
                            ),
                            array(
                            chr(10),
                             " $2 ", // add spaces !
                             // '',
                             ' '
                              ),
                            $content);
    // replace script blocks
    self::replace(
      "@@@SYMFONY:TRIM:SCRIPT@@@", $_script_blocks, $content);
    // replace pre blocks
    self::replace(
      "@@@SYMFONY:TRIM:PRE@@@", $_pre_blocks, $content);
    // replace textarea blocks
    self::replace(
      "@@@SYMFONY:TRIM:TEXTAREA@@@", $_textarea_blocks, $content);

    // clean up
    unset($_script_blocks, $_pre_blocks, $_textarea_blocks);

    return $content;
  }

  /**
   * Replaces string within another string
   *
   */
  protected static function replace($search_str, $replace, &$subject)
  {
    $_len = strlen($search_str);
    $_pos = 0;
    for($_i = 0, $_count = count($replace); $_i < $_count; $_i++)
    {
      if(($_pos = strpos($subject, $search_str, $_pos)) !== false)
      {
        $subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
      }
      else
      {
        break;
      }
    }
  }

  /**
   * Compresses the content using gzip compression
   *
   * @param string text content to compress*
   * @return string, compressed content when browser does support gzip encoding
   */
  protected function compress($content)
  {
    // detect compression flag
    // do nothing when comression is enabled
    if(sfConfig::get('sf_compressed'))
    {
      if(sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->err('{myCompressOutputFilter} Compression setting sf_compressed should be turned off. With this setting the framework tries to compress everything (even images of generated by your app.).');
      }
      return $content;
    }

    if($encoding = $this->canGzip())
    {
      $min_length = (integer)$this->getParameter('compress_min_length', 2048);
      // no need to waste resources in compressing very little data
      if(mb_strlen($content, sfConfig::get('sf_charset')) >= $min_length)
      {
        // gzip the content
        $level      = (integer)$this->getParameter('compress_level', 9);
        $compressed = substr(gzcompress($content, $level), 0, -4);

        $content = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $compressed;

        // add response header
        $this->getContext()->getResponse()->setHttpHeader('Content-Encoding', $encoding);
      }
    }
    return $content;
  }

  /**
   * Checks if current browser supports gzip or x-gzip
   *
   * @return boolean|string Returns false if gzip is not supported or suppported encoding
   */
  protected function canGzip()
  {
    $acceptEncoding = $this->getContext()->getRequest()->getHttpHeader('accept_encoding');
    if(strpos($acceptEncoding, 'x-gzip') !== false)
    {
      $encoding = 'x-gzip';
    }
    else if(strpos($acceptEncoding, 'gzip') !== false)
    {
      $encoding = 'gzip';
    }
    else
    {
      $encoding = false;
    }
    return $encoding;
  }

  /**
   * Checks if tidy is installed in the system
   *
   * @return boolean true if installed, false otherwise
   */
  protected function tidyInstalled()
  {
    if(function_exists('tidy_parse_string'))
    {
      return true;
    }
    return false;
  }

}
