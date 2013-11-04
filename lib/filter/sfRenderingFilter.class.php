<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRenderingFilter is the last filter registered for each filter chain. This
 * filter does the rendering.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfRenderingFilter extends sfFilter {

  /**
   * Array of default parameters
   *
   * @var array
   */
  protected $defaultParameters = array(
    'charset' => 'utf-8',
    'compress' => true,
    'compression_level' => 9,
    'compression_min_length' => 2048,
    'whitespace_removal_condition' => true,
  );

  /**
   * Executes this filter.
   *
   * @param sfFilterChain The filter chain.
   * @throws sfInitializeException If an error occurs during view initialization
   * @throws sfViewException       If an error occurs while executing the view
   */
  public function execute(sfFilterChain $filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if(sfForm::hasToStringException())
    {
      throw sfForm::getToStringException();
    }
    elseif(sfFormField::hasToStringException())
    {
      throw sfFormField::getToStringException();
    }

    // send headers + content
    if(sfView::RENDER_VAR != $this->getContext()->getController()->getRenderMode())
    {
      $this->log('Render to client.', sfILogger::INFO);
      $this->prepare();
      $this->getContext()->getResponse()->send();
    }
  }

  /**
   * Prepares the response for rendering to client
   *
   */
  protected function prepare()
  {
    $response = $this->getContext()->getResponse();

    $this->getContext()->getEventDispatcher()->notify(new sfEvent('response.pre_send', array(
      'response' => $response
    )));

    // can only for HTML text
    if(!$response->isHeaderOnly()
        && is_string($response->getContent())
        && preg_match('~(text/html|javascript|text/xml)+~', $response->getContentType(), $matches))
    {
      // remove whitespace
      if(!sfConfig::get('sf_test') && $this->getParameter('whitespace_removal_condition') &&
          strpos($response->getContentType(), 'text/html') !== false)
      {
        $this->log('Removing whitespace from the response content.', sfILogger::INFO);
        $content = $this->removeWhitespace($response->getContent());
        $response->setContent($content);
        $response->setHttpHeader('Content-Length', strlen($content));
      }

      // compress
      if($this->canCompress() && ($encoding = $this->getClientEncoding()))
      {
        $content = $response->getContent();

        // no need to waste resources in compressing very little data
        if(mb_strlen($content, $this->getParameter('charset')) >
            $this->getParameter('compression_min_length'))
        {
          try
          {
            $length = strlen($content);

            $content = $this->compress(
              $content,
              $encoding,
              $this->getParameter('compression_level')
            );

            $this->log('Compresed the output using "{encoding}". Compressed size {saving}%.', sfILogger::INFO, array(
              'encoding' => $encoding,
              'saving' => ($total = strlen($response->getContent())) > 0 ? ((round(strlen($content) / $total, 2) * 100)) : 0
            ));

            $response->setContent($content);
            $response->setHttpHeader('X-Uncompressed-Content-Length', $length);
            $response->setHttpHeader('Content-Encoding', $encoding);
            $response->setHttpHeader('Content-Length', strlen($content));
          }
          // not implemented type of compression, see getClientEncoding()
          catch(LogicException $e)
          {
          }
        }
      }
    }
  }

  /**
   * Can the response be compressed? The output can be compress when
   * following conditions are met:
   *
   * 1) The `compress` parameter must be `true`
   * 2) No output was found in the output buffer
   *
   * @return boolean
   */
  protected function canCompress()
  {
    if(!$this->getParameter('compress'))
    {
      return false;
    }

    return ob_get_length() == 0;
  }

  /**
   * Compresses the content using gzip compression
   *
   * @param string $content The content to compress
   * @param string $encoding The encoding method
   * @return string, compressed content when browser does support gzip encoding
   */
  protected function compress($content, $encoding, $level = 9)
  {
    switch($encoding)
    {
      case 'gzip':
        $content = gzencode($content, $level);
      break;

      case 'deflate':
        $content = gzdeflate($content, $level);
      break;

      default:
        throw new LogicException(sprintf('The encoding method "%s" is not implemented', $encoding));
    }

    return $content;
  }

  /**
   * Returns current browser support for compression gzip or x-gzip
   *
   * @return boolean|string Returns false if gzip is not supported or suppported encoding
   */
  protected function getClientEncoding()
  {
    $acceptEncoding = $this->getContext()->getRequest()->getHttpHeader('Accept-Encoding');
    $encoding = false;
    if(strpos($acceptEncoding, 'gzip') !== false)
    {
      $encoding = 'gzip';
    }
    else if(strpos($acceptEncoding, 'deflate') !== false)
    {
      $encoding = 'deflate';
    }
    return $encoding;
  }

  /**
   * Removes extra white space within the text.
   *
   * Trim leading white space and blank lines from html code,
   * cleaning up code and saving bandwidth. Does not
   * affect <PRE>></PRE>, <TEXTAREA></TEXTAREA> and <SCRIPT></SCRIPT> blocks.
   *
   * @param string $content The content to trim
   */
  protected function removeWhitespace($content)
  {
    try
    {
      $scripts = $this->matchAll($content, '!<script[^>]*>.*?<\/script>!is');
      $pres = $this->matchAll($content, '!<pre[^>]*>.*?</pre>!is');
      $textareas = $this->matchAll($content, '!<textarea[^>]+>.*?</textarea>!is');
    }
    catch(sfRegexpException $e)
    {
      $this->log('Error while removing whitespace. {exception}', sfILogger::WARNING, array(
        'exception' => $e->getMessage()
      ));

      // we return the content unmodified
      return $content;
    }

    // Pull out the script blocks
    $this->replaceAll($content, '!<script[^>]*>.*?<\/script>!is', '@@@SIFT:TRIM:SCRIPT@@@');
    // Pull out the pre blocks
    $this->replaceAll($content, '!<pre[^>]*>.*?</pre>!is', '@@@SIFT:TRIM:PRE@@@');
    // Pull out the textarea blocks
    $this->replaceAll($content, '!<textarea[^>]+>.*?</textarea>!is', '@@@SIFT:TRIM:TEXTAREA@@@');
    // strip the whitespace
    $this->cleanup($content);

    // PUT ALL BACK
    // script blocks
    $this->replace('@@@SIFT:TRIM:SCRIPT@@@', $scripts, $content);
    // pre blocks
    $this->replace('@@@SIFT:TRIM:PRE@@@', $pres, $content);
    // textarea blocks
    $this->replace('@@@SIFT:TRIM:TEXTAREA@@@', $textareas, $content);

    return $content;
  }

  /**
   * Removes whitespace from the content
   *
   * @param string $content
   */
  protected function cleanup(&$content)
  {
    $content = preg_replace(
        array(
          '/[\r\n][\r\n\t]*[\r\n]/i',
          '/(\s+)?(\<.+\>)(\s+)?/', // strip spaces between tags
          '/(\s){2,}/i' // get rid of mutliple spaces and replace it with one
        ),
        array(
          chr(10), " $2 ", // add spaces !
          ' '
        ), $content);
  }

  /**
   * Matches all $pattern in the $subject
   *
   * @param string $subject
   * @param type $pattern
   * @param integer $flags
   * @param integer $offset
   * @return array Array of matches
   * @throws sfRegexpException
   */
  protected function matchAll($subject, $pattern, $flags = 0, $offset = 0)
  {
    preg_match_all($pattern, $subject, $matches, ($flags & PREG_PATTERN_ORDER) ? $flags : ($flags | PREG_SET_ORDER),  $offset);
    if($error = preg_last_error())
    {
      throw new sfRegexpException(null, $error, $pattern);
    }
    return $matches;
  }

  /**
   * Regular expression replacement
   *
   * @param string $subject The subject
   * @param string $pattern
   * @param string $replacement
   * @param integer $limit
   * @return string
   * @throws sfRegexpException
   */
  protected function replaceAll(&$subject, $pattern, $replacement, $limit = -1)
  {
    $subject = preg_replace($pattern, $replacement, $subject, $limit);
    if($error = preg_last_error())
    {
      throw new sfRegexpException(null, $error, $pattern);
    }
  }

  /**
   * Replaces string within another string
   *
   * @param string $string The string
   * @param array $replace The replacement string
   * @param string $subject The subject
   */
  protected function replace($string, $replace, &$subject)
  {
    $length = strlen($string);
    $pos = 0;
    for($i = 0, $count = count($replace); $i < $count; $i++)
    {
      if(($pos = strpos($subject, $string, $pos)) !== false)
      {
        $subject = substr_replace($subject, $replace[$i], $pos, $length);
      }
      else
      {
        break;
      }
    }
  }

}
